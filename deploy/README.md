# Production deployment

This project has two separate production pieces:

1. Laravel, served by Nginx/PHP-FPM.
2. LiveKit, running on the server with host networking.

LiveKit's production deployment requires a real domain with TLS for the WebSocket endpoint. WebRTC also needs the media ports open on the public server. The current configuration uses TCP 7881 plus UDP 50000-60000. LiveKit recommends host networking for Docker deployments. See the official deployment and firewall documentation before exposing the server.

## 1. DNS

Create records pointing to the server:

- `meet.example.com` -> Laravel application
- `livekit.example.com` -> LiveKit server

Use real domains in production. The browser should receive `wss://livekit.example.com`, not `ws://localhost:7880`.

## 2. Laravel environment

Copy `.env.production.example` to `.env` and replace every placeholder.

Generate the application key:

```bash
php artisan key:generate --force
```

Generate strong, unique LiveKit credentials. The API secret must never be committed to Git or exposed to the browser.

## 3. Laravel production install

On the application server:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
```

Laravel recommends caching configuration, routes, views, and other optimized metadata during production deployment. `php artisan optimize` performs the standard optimization pass.

Serve only the `public/` directory through Nginx. Never point Nginx at the repository root.

The application must run with:

```env
APP_ENV=production
APP_DEBUG=false
```

## 4. Nginx

Use Nginx with PHP-FPM and HTTPS. A minimal application server should:

- serve `/path/to/livekit-meeting/public`
- send PHP requests to PHP 8.3-FPM
- redirect HTTP to HTTPS
- reject access to dotfiles
- set `X-Content-Type-Options: nosniff`
- set `X-Frame-Options: SAMEORIGIN`
- enable HTTP/2 or HTTP/3 at the TLS terminator

Example PHP location:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ ^/index\\.php(/|$) {
    fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_hide_header X-Powered-By;
}

location ~ /\\. {
    deny all;
}
```

## 5. LiveKit

Copy the production environment file:

```bash
cd deploy/livekit
cp .env.example .env
```

Put the same API key and secret in this file that Laravel uses.

Start LiveKit:

```bash
docker compose up -d
docker compose ps
docker compose logs --tail=100 livekit
```

The supplied compose file uses LiveKit v1.13.7 rather than `latest` so a routine image pull cannot silently change the media server underneath the application.

## 6. LiveKit TLS

The LiveKit HTTP/WebSocket endpoint on port 7880 must sit behind a TLS-terminating reverse proxy or load balancer. The browser-facing URL is:

```text
wss://livekit.example.com
```

The reverse proxy must support WebSocket upgrades.

For clients behind restrictive firewalls, add the embedded TURN/TLS setup described in LiveKit's production deployment documentation. TURN/TLS requires its own TLS configuration and domain.

## 7. Firewall

For the supplied LiveKit configuration, allow:

- TCP 443 for the HTTPS/WebSocket reverse proxy
- TCP 7881 for LiveKit WebRTC fallback
- UDP 50000-60000 for LiveKit WebRTC media

Do not expose TCP 7880 directly to the public internet when a TLS reverse proxy is available.

If TURN is enabled, open the TURN ports required by that configuration as well.

## 8. Health checks

Laravel 13 already exposes `/up`. Use it for the application process monitor or load balancer.

Check:

```bash
curl -fsS https://meet.example.com/up
```

Check LiveKit logs after startup:

```bash
docker compose logs --tail=100 livekit
```

## 9. Deployment checklist

Before accepting real users:

- [ ] HTTPS works for the Laravel domain.
- [ ] `wss://livekit.example.com` works from a real browser.
- [ ] `APP_DEBUG=false`.
- [ ] Production LiveKit credentials are random and are not in Git.
- [ ] Laravel points to `wss://livekit.example.com`.
- [ ] LiveKit advertises the server's public IP.
- [ ] TCP 7881 and UDP 50000-60000 are allowed by both the cloud firewall and host firewall.
- [ ] Two different networks can join the same room.
- [ ] Camera is off by default after joining.
- [ ] Screen sharing works.
- [ ] `/up` returns HTTP 200.
- [ ] LiveKit is configured with a pinned version and an upgrade/rollback procedure.

This repository does not contain a server-specific IP, domain, TLS certificate, or production secret. Those values belong on the deployment host, not in Git.
