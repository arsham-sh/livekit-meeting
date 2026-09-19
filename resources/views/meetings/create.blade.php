<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LiveKit Meeting</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #09090b;
            --panel: #151518;
            --text: #f5f5f5;
            --muted: #a1a1aa;
        }

        * { box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
        }

        main {
            width: min(460px, calc(100% - 32px));
            text-align: center;
            padding: 32px;
            background: var(--panel);
            border: 1px solid #25252b;
            border-radius: 18px;
        }

        h1 { margin-top: 0; }

        p {
            color: var(--muted);
            line-height: 1.5;
        }

        button {
            border: 0;
            border-radius: 10px;
            padding: 12px 18px;
            cursor: pointer;
            font-weight: 700;
            background: #fafafa;
            color: #111;
        }

        button:hover { filter: brightness(1.08); }
    </style>
</head>
<body>
<main>
    <h1>LiveKit Meeting</h1>
    <p>Create a room and share the meeting URL with the people you want to invite.</p>

    <form method="POST" action="{{ route('meetings.store') }}">
        @csrf
        <button type="submit">Create meeting</button>
    </form>
</main>
</body>
</html>
