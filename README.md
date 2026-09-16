# Laravel + Local LiveKit Meeting MVP

A minimal local video meeting app using Laravel 13 and a self-hosted LiveKit server.

## Requirements

- PHP 8.3+
- Composer
- Docker + Docker Compose
- PHP extensions `pdo_sqlite` and `sqlite3`

## Setup on Windows PowerShell

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
```

If `.env` already exists, make sure it contains:

```env
DB_CONNECTION=sqlite
SESSION_DRIVER=file
CACHE_STORE=file

LIVEKIT_URL=ws://localhost:7880
LIVEKIT_API_KEY=devkey
LIVEKIT_API_SECRET=secret
```

After changing `.env`:

```powershell
php artisan config:clear
php artisan optimize:clear
```

## Start LiveKit

```powershell
docker compose up -d
```

Check it:

```powershell
docker compose ps
docker compose logs livekit
```

The local LiveKit server uses:

- WebSocket: `ws://localhost:7880`
- WebRTC UDP: `localhost:7881`
- API key: `devkey`
- API secret: `secret`

These credentials are for local development only. Do not expose them publicly.

## Start Laravel

```powershell
php artisan serve
```

Open:

```text
http://localhost:8000
```

Click **Create meeting**, enter a name, and click **Join**. Open the generated room URL in a second browser tab or on another local device to test multiple participants.

## Browser permissions

Allow camera and microphone access when the browser asks. Use `localhost` for local development.

## Architecture

- Laravel creates meeting URLs and signs short-lived LiveKit participant tokens.
- LiveKit runs locally in Docker and handles WebRTC media routing.
- The browser uses `livekit-client` from jsDelivr and never receives the LiveKit API secret.
- Rooms are created automatically when the first participant joins.
- SQLite is available for application data, while sessions and cache use the filesystem for the local MVP.
