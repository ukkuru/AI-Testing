# syntax=docker/dockerfile:1

# ---- Stage 1: build the React frontend -------------------------------
FROM node:20-bookworm-slim AS client-build
WORKDIR /app/client
COPY client/package.json client/package-lock.json ./
RUN npm ci
COPY client/ ./
RUN npm run build

# ---- Stage 2: install backend deps ------------------------------------
# Debian-based (not alpine) so better-sqlite3's prebuilt glibc binary is
# used directly — musl/alpine usually forces a from-source node-gyp build.
FROM node:20-bookworm-slim AS server-deps
WORKDIR /app/server
COPY server/package.json server/package-lock.json ./
RUN npm ci --omit=dev

# ---- Stage 3: runtime ---------------------------------------------------
FROM node:20-bookworm-slim
ENV NODE_ENV=production
WORKDIR /app/server

COPY --from=server-deps /app/server/node_modules ./node_modules
COPY server/ ./
COPY --from=client-build /app/client/dist ./public

# SQLite lives here; mount a volume at this path to persist across deploys.
RUN mkdir -p /app/server/data

EXPOSE 8787
CMD ["node", "src/index.js"]
