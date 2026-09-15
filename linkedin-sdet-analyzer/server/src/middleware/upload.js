const multer = require("multer");
const config = require("../config");

const ALLOWED_MIME_TYPES = new Set(["application/pdf"]);

// %PDF magic bytes — verify real file content rather than trusting the
// client-supplied mimetype, same defense-in-depth approach used previously
// for image signatures.
const PDF_SIGNATURE = Buffer.from([0x25, 0x50, 0x44, 0x46]); // "%PDF"

function hasValidPdfSignature(buffer) {
  return buffer.length >= PDF_SIGNATURE.length && buffer.subarray(0, PDF_SIGNATURE.length).equals(PDF_SIGNATURE);
}

const storage = multer.memoryStorage();

const upload = multer({
  storage,
  limits: { fileSize: config.maxUploadBytes, files: 1 },
  fileFilter: (req, file, cb) => {
    if (!ALLOWED_MIME_TYPES.has(file.mimetype)) {
      cb(new Error("UNSUPPORTED_FILE_TYPE"));
      return;
    }
    cb(null, true);
  },
});

/**
 * Verifies the uploaded buffer is actually a PDF before it's handed to
 * pdf-parse. Does not block the request for anything else — the actual
 * text-extraction failure (e.g. an encrypted or corrupt PDF) is handled
 * where the extraction happens, in the analyze route.
 */
function validatePdfSignature(req, res, next) {
  if (!req.file) {
    return next();
  }
  if (!hasValidPdfSignature(req.file.buffer)) {
    return next(new Error("INVALID_PDF_DATA"));
  }
  next();
}

function handleUploadErrors(err, req, res, next) {
  if (!err) return next();

  if (err instanceof multer.MulterError) {
    if (err.code === "LIMIT_FILE_SIZE") {
      return res.status(413).json({
        error: "FILE_TOO_LARGE",
        message: `Uploaded file exceeds the ${Math.round(config.maxUploadBytes / (1024 * 1024))}MB limit.`,
      });
    }
    return res.status(400).json({ error: "UPLOAD_ERROR", message: err.message });
  }

  if (err.message === "UNSUPPORTED_FILE_TYPE") {
    return res.status(400).json({
      error: "UNSUPPORTED_FILE_TYPE",
      message: "Only a .pdf LinkedIn profile export is supported.",
    });
  }

  if (err.message === "INVALID_PDF_DATA") {
    return res.status(400).json({
      error: "INVALID_PDF_DATA",
      message: "The uploaded file could not be read as a valid PDF.",
    });
  }

  return res.status(500).json({ error: "UPLOAD_FAILED", message: "Unexpected upload error." });
}

module.exports = { upload, validatePdfSignature, handleUploadErrors, ALLOWED_MIME_TYPES };
