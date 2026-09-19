<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#070711">
    <title>LiveKit Meeting · Start a room</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #070711;
            --surface: rgba(18,18,30,.72);
            --surface-strong: rgba(25,25,42,.88);
            --text: #f8fafc;
            --muted: #a1a1b2;
            --line: rgba(255,255,255,.11);
            --violet: #8b5cf6;
            --cyan: #22d3ee;
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body {
            min-height: 100dvh;
            display: grid;
            place-items: center;
            overflow: hidden;
            padding: 24px;
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 15% 15%, rgba(139,92,246,.23), transparent 28%),
                radial-gradient(circle at 86% 82%, rgba(34,211,238,.16), transparent 26%),
                radial-gradient(circle at 50% 0%, rgba(99,102,241,.10), transparent 34%),
                var(--bg);
            -webkit-font-smoothing: antialiased;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .45;
            background:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at center, #000, transparent 82%);
        }
        .shell {
            position: relative;
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 18px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 30px;
            background: rgba(8,8,15,.58);
            box-shadow: 0 40px 120px rgba(0,0,0,.48);
            backdrop-filter: blur(24px) saturate(130%);
        }
        .hero, .start-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 22px;
        }
        .hero {
            min-height: 540px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(28px, 5vw, 58px);
            background:
                linear-gradient(145deg, rgba(255,255,255,.055), transparent 42%),
                radial-gradient(circle at 90% 10%, rgba(34,211,238,.10), transparent 25%),
                var(--surface);
        }
        .hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -100px;
            bottom: -110px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(139,92,246,.45), rgba(34,211,238,.06));
            filter: blur(10px);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 7px 10px;
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 999px;
            background: rgba(255,255,255,.045);
            color: #c4b5fd;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }
        .eyebrow::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 14px rgba(74,222,128,.8);
        }
        h1 {
            max-width: 620px;
            margin: 22px 0 14px;
            font-size: clamp(42px, 7vw, 76px);
            line-height: .94;
            letter-spacing: -.055em;
        }
        h1 span {
            background: linear-gradient(110deg, #fff 15%, #c4b5fd 55%, #67e8f9 90%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero-copy {
            max-width: 580px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(15px, 1.8vw, 18px);
            line-height: 1.7;
        }
        .feature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
            margin-top: 34px;
        }
        .feature {
            padding: 12px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            background: rgba(255,255,255,.035);
        }
        .feature strong, .feature span { display: block; }
        .feature strong { font-size: 12px; }
        .feature span { margin-top: 4px; color: #85859a; font-size: 10px; line-height: 1.4; }
        .start-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(24px, 4vw, 42px);
            background:
                linear-gradient(145deg, rgba(139,92,246,.11), transparent 45%),
                var(--surface-strong);
            box-shadow: inset 0 1px rgba(255,255,255,.05);
        }
        .start-card .icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--violet), var(--cyan));
            color: #fff;
            font-size: 22px;
            font-weight: 900;
            box-shadow: 0 14px 34px rgba(99,102,241,.28);
        }
        .start-card h2 {
            margin: 0 0 9px;
            font-size: 25px;
            letter-spacing: -.035em;
        }
        .start-card p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.6;
            font-size: 13px;
        }
        form { margin: 0; }
        button {
            width: 100%;
            min-height: 52px;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 14px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #0891b2);
            font: 800 14px inherit;
            letter-spacing: -.01em;
            box-shadow: 0 16px 34px rgba(79,70,229,.25), inset 0 1px rgba(255,255,255,.22);
            transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
        }
        button:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 20px 40px rgba(79,70,229,.32), inset 0 1px rgba(255,255,255,.25);
        }
        button:active { transform: translateY(0); }
        .hint {
            margin-top: 14px;
            color: #6f7081;
            font-size: 10px;
            line-height: 1.5;
            text-align: center;
        }
        @media (max-width: 820px) {
            body { overflow: auto; place-items: start center; }
            .shell { grid-template-columns: 1fr; margin: auto 0; }
            .hero { min-height: 0; }
        }
        @media (max-width: 520px) {
            body { padding: 10px; }
            .shell { padding: 8px; border-radius: 22px; }
            .hero, .start-card { border-radius: 17px; }
            .hero { padding: 28px 22px; }
            h1 { font-size: 50px; }
            .feature-row { grid-template-columns: 1fr; }
            .feature { display: flex; align-items: center; gap: 8px; }
            .feature span { margin: 0; }
        }
        @media (prefers-reduced-transparency: reduce) {
            .shell, .hero, .start-card { backdrop-filter: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="hero">
            <div>
                <span class="eyebrow">Private realtime rooms</span>
                <h1>Meet without the <span>mess.</span></h1>
                <p class="hero-copy">
                    Start a focused video room with screen sharing, collaborative notes,
                    whiteboarding, and chat. One click, one link, fewer tiny buttons
                    pretending to be a product.
                </p>
            </div>

            <div class="feature-row" aria-label="Meeting features">
                <div class="feature"><strong>Video</strong><span>Camera &amp; microphone</span></div>
                <div class="feature"><strong>Share</strong><span>Present your screen</span></div>
                <div class="feature"><strong>Collaborate</strong><span>Docs &amp; whiteboard</span></div>
            </div>
        </section>

        <section class="start-card">
            <div class="icon">✦</div>
            <h2>Create a room</h2>
            <p>A unique meeting room is generated instantly. Share the resulting link with your participants.</p>
            <form method="POST" action="{{ route('meetings.store') }}">
                @csrf
                <button type="submit">Launch meeting</button>
            </form>
            <div class="hint">No account screen. No dashboard labyrinth. Just the meeting.</div>
        </section>
    </main>
</body>
</html>
