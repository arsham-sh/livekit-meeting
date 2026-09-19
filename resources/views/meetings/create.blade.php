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
    
        body {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 20% 15%, rgba(99,102,241,.16), transparent 34%),
                radial-gradient(circle at 82% 84%, rgba(168,85,247,.12), transparent 32%),
                #09090b;
        }
        body::before {
            content: "";
            position: fixed;
            inset: -50%;
            pointer-events: none;
            background:
                repeating-linear-gradient(
                    -45deg,
                    transparent 0,
                    transparent 10px,
                    rgba(255,255,255,.028) 10px,
                    rgba(255,255,255,.028) 11px
                );
            transform: rotate(.5deg);
        }
        main {
            position: relative;
            overflow: hidden;
            background: rgba(21,21,24,.78);
            border-color: rgba(255,255,255,.10);
            box-shadow: 0 28px 90px rgba(0,0,0,.42);
            backdrop-filter: blur(18px) saturate(125%);
        }
        main::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255,255,255,.045), transparent 42%),
                radial-gradient(circle at 50% 0%, rgba(255,255,255,.045), transparent 48%);
        }
        main > * { position: relative; }
        button {
            box-shadow: 0 10px 26px rgba(0,0,0,.22);
            transition: transform .16s ease, filter .16s ease, box-shadow .16s ease;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 32px rgba(0,0,0,.28);
        }
        @media (prefers-reduced-transparency: reduce) {
            main { backdrop-filter: none; }
        }

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
