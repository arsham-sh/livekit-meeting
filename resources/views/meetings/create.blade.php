<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LiveKit Meeting</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #111; color: #fff; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        main { width: min(420px, calc(100% - 32px)); text-align: center; }
        button { border: 0; border-radius: 10px; padding: 12px 18px; cursor: pointer; font-weight: 700; }
    </style>
</head>
<body>
<main>
    <h1>Local LiveKit Meeting</h1>
    <p>Create a room and share its URL.</p>
    <form method="POST" action="{{ route('meetings.store') }}">
        @csrf
        <button type="submit">Create meeting</button>
    </form>
</main>
</body>
</html>
