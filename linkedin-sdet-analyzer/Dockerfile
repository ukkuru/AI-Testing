# syntax=docker/dockerfile:1

# Node 22 is required — better-sqlite3's resolved version only ships
# prebuilt binaries for Node >=22. On Node 20 there's no matching prebuilt,
# so npm falls back to compiling from source via node-gyp, which then fails
# because the slim image has no Python/build toolchain.

# ---- Stage 1: build the React frontend -------------------------------
FROM node:22-bookworm-slim AS client-build
WORKDIR /app/client
COPY client/package.json client/package-lock.json ./
RUN npm ci
COPY client/ ./
RUN npm run build

# ---- Stage 2: install backend deps ------------------------------------
# Debian-based (not alpine) so better-sqlite3's prebuilt glibc binary is
# used directly — musl/alpine usually forces a from-source node-gyp build.
FROM node:22-bookworm-slim AS server-deps
WORKDIR /app/server
COPY server/package.json server/package-lock.json ./
# Fallback toolchain in case a prebuilt binary is ever unavailable for the
# exact platform (e.g. a future dependency bump) — only affects this
# intermediate stage, never bloats the final runtime image below.
RUN apt-get update && apt-get install -y --no-install-recommends python3 make g++ \
    && rm -rf /var/lib/apt/lists/*
RUN npm ci --omit=dev

# ---- Stage 3: runtime ---------------------------------------------------
FROM node:22-bookworm-slim
ENV NODE_ENV=production
WORKDIR /app/server

COPY --from=server-deps /app/server/node_modules ./node_modules
COPY server/ ./
COPY --from=client-build /app/client/dist ./public

# SQLite lives here; mount a volume at this path to persist across deploys.
RUN mkdir -p /app/server/data

EXPOSE 8787
CMD ["node", "src/index.js"]
