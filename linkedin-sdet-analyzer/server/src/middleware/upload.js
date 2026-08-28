const multer = require("multer");
const imageSize = require("image-size");
const config = require("../config");

const ALLOWED_MIME_TYPES = new Set(["image/png", "image/jpeg"]);

const PNG_SIGNATURE = Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]);
const JPEG_SIGNATURE = Buffer.from([0xff, 0xd8, 0xff]);

/**
 * image-size sniffs real file bytes (not the declared mimetype) and has an
 * unpatched DoS in its ICNS/JXL/HEIF parsers (GHSA-w3rx-r6r6-pgpr,
 * GHSA-5p2g-fcmc-qvqq). We only ever want to hand it PNG/JPEG bytes, so we
 * verify the magic bytes ourselves first and reject anything else outright
 * rather than trusting the client-supplied mimetype.
 */
function hasValidImageSignature(buffer) {
  if (buffer.length >= PNG_SIGNATURE.length && buffer.subarray(0, PNG_SIGNATURE.length).equals(PNG_SIGNATURE)) {
    return true;
  }
  if (buffer.length >= JPEG_SIGNATURE.length && buffer.subarray(0, JPEG_SIGNATURE.length).equals(JPEG_SIGNATURE)) {
    return true;
  }
  return false;
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
 * Reads image dimensions from the in-memory buffer and attaches a
 * low-resolution warning to the request if the screenshot is too small
 * to reliably assess photo/banner quality. Does not block the request.
 */
function checkImageResolution(req, res, next) {
  if (!req.file) {
    return next();
  }
  if (!hasValidImageSignature(req.file.buffer)) {
    return next(new Error("INVALID_IMAGE_DATA"));
  }
  try {
    const dimensions = imageSize(req.file.buffer);
    req.imageDimensions = dimensions;
    if (!dimensions.width || dimensions.width < config.minRecommendedWidth) {
      req.lowResolutionWarning = `Screenshot width (${dimensions.width || "unknown"}px) is below the recommended ${config.minRecommendedWidth}px. Photo/banner quality assessment may be unreliable — consider a higher-resolution full-page capture.`;
    }
    next();
  } catch (err) {
    next(new Error("INVALID_IMAGE_DATA"));
  }
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
      message: "Only .png and .jpg/.jpeg screenshots are supported. PDFs and other formats are rejected.",
    });
  }

  if (err.message === "INVALID_IMAGE_DATA") {
    return res.status(400).json({
      error: "INVALID_IMAGE_DATA",
      message: "The uploaded file could not be read as a valid image.",
    });
  }

  return res.status(500).json({ error: "UPLOAD_FAILED", message: "Unexpected upload error." });
}

module.exports = { upload, checkImageResolution, handleUploadErrors, ALLOWED_MIME_TYPES };
