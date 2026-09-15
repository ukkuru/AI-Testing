# Deploying to the Hostinger VPS (alongside the existing app)

This VPS already runs another app (`webapp-app-1`, a Docker container bound to
`127.0.0.1:3000`, served publicly at `qpulse.sbs` via Nginx + Certbot). This
guide adds LinkedIn Profile Scorecard for Testers as a **second, independent** container
on a different local port and a different (sub)domain — it never touches the
existing container, its Nginx config, or its certificate.

Target setup:

| | Existing app | This app |
|---|---|---|
| Container | `webapp-app-1` | `linkedin-sdet-analyzer` |
| Local port | `127.0.0.1:3000` | `127.0.0.1:3101` |
| Public domain | `qpulse.sbs` | `sdet.qpulse.sbs` |

## 0. Point DNS at the VPS

In Hostinger's DNS panel for `qpulse.sbs`, add:

```
Type: A      Name: sdet      Value: 76.13.247.142
Type: AAAA   Name: sdet      Value: 2a02:4780:12:45fd::1   (optional, IPv6)
```

DNS can take a few minutes to propagate — check with `dig sdet.qpulse.sbs`
before moving to the Certbot step.

## 1. Get the code onto the VPS

```bash
mkdir -p /opt/apps && cd /opt/apps
git clone https://github.com/ukkuru/AI-Testing.git
cd AI-Testing
# Until PR #1 is merged, deploy from its branch:
git checkout claude/linkedin-sdet-analyzer-si58nm
cd linkedin-sdet-analyzer
```

(Private repo → you'll need a GitHub personal access token as the password
when `git clone` prompts, or clone over SSH with a deploy key.)

## 2. Configure environment

```bash
cp .env.example .env
nano .env
```

Fill in:
- `ANTHROPIC_API_KEY` — your real key
- `JWT_SECRET` — generate one: `node -e "console.log(require('crypto').randomBytes(48).toString('hex'))"`
- `CLIENT_ORIGIN=https://sdet.qpulse.sbs`
- `COOKIE_SECURE=true` (already the default in the example file)

## 3. Build and start the container

```bash
docker compose version || docker-compose --version   # confirm which you have
docker compose build
docker compose up -d
docker compose ps
curl -s http://127.0.0.1:3101/api/health              # expect {"ok":true}
```

If `docker compose` (v2, no hyphen) isn't available, substitute
`docker-compose` in the commands above.

User accounts (SQLite) persist in `./data` on the host via the bind mount in
`docker-compose.yml` — safe across rebuilds/redeploys.

## 4. New Nginx server block

Create `/etc/nginx/sites-available/sdet.qpulse.sbs`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name sdet.qpulse.sbs;

    location / {
        proxy_pass http://127.0.0.1:3101;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 10M;   # PDF exports up to 8MB + form overhead
    }
}
```

```bash
ln -s /etc/nginx/sites-available/sdet.qpulse.sbs /etc/nginx/sites-enabled/
nginx -t   # must say "syntax is ok" / "test is successful"
systemctl reload nginx
```

`nginx -t` failing at this point means a typo in the new file — it won't
affect the existing `qpulse.sbs` block either way since Nginx validates the
whole config before reloading anything.

## 5. SSL via Certbot

```bash
certbot --nginx -d sdet.qpulse.sbs
```

(If `certbot` isn't found: `apt install certbot python3-certbot-nginx`.)

Certbot edits only the `sdet.qpulse.sbs` block it just created — it doesn't
touch the existing `qpulse.sbs` block or its certificate.

## 6. Verify

Visit `https://sdet.qpulse.sbs`, register an account, and run the analyzer
end to end. Confirm the existing app still works unchanged at
`https://qpulse.sbs`.

## Redeploying after a code change

```bash
cd /opt/apps/AI-Testing/linkedin-sdet-analyzer
git pull
docker compose up -d --build
```

`./data` (the SQLite file) is untouched by this — registered users survive
the redeploy. There's a few seconds of downtime for this app only while the
container restarts; the other app is unaffected.

## Rollback / stop

```bash
docker compose down          # stops and removes the container, keeps ./data
docker compose up -d         # brings it back
```
