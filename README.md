# Laravel + Local LiveKit Meeting MVP

A minimal local video meeting app using Laravel 13 and a self-hosted LiveKit server.

## Requirements

- PHP 8.3+
- Composer
- Docker + Docker Compose

Laravel 13 requires PHP 8.3 or newer. The LiveKit PHP server SDK is `agence104/livekit-server-sdk`.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Start LiveKit:

```bash
docker compose up -d
```

Start Laravel:

```bash
php artisan serve
```

Open:

```text
http://localhost:8000
```

Create a meeting, then open the generated room URL in two browser tabs or on two local devices.

## Architecture

- Laravel creates meeting URLs and signs short-lived LiveKit participant tokens.
- LiveKit runs locally in Docker and handles WebRTC media routing.
- The browser uses `livekit-client` and never receives the LiveKit API secret.
- Rooms are created automatically when the first participant joins.

## Local LiveKit

The development server uses:

- HTTP/WebSocket: `localhost:7880`
- WebRTC UDP: `localhost:7881`
- API key: `devkey`
- API secret: `secret`

These credentials are for local development only. Do not expose them publicly.
