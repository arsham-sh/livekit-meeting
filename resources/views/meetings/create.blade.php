<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8e1d3">
    <title>LiveKit Meeting · Start a room</title>
    <style>
        :root {
            color-scheme: light;
            --paper: #e8e1d3;
            --paper-2: #ded6c7;
            --ink: #171714;
            --muted: #6e695f;
            --line: #bdb5a6;
            --accent: #b64d35;
            --white: #f4efe5;
        }

        * { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100dvh;
            padding: 24px;
            display: grid;
            place-items: center;
            color: var(--ink);
            background:
                linear-gradient(rgba(23,23,20,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(23,23,20,.025) 1px, transparent 1px),
                var(--paper);
            background-size: 32px 32px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: "LIVEKIT / REALTIME MEETING";
            position: fixed;
            top: 18px;
            left: 24px;
            color: #81796d;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .16em;
        }

        .shell {
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(300px, .65fr);
            border-top: 1px solid var(--ink);
            border-bottom: 1px solid var(--ink);
        }

        .hero,
        .start-card {
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .hero {
            min-height: 610px;
            padding: clamp(34px, 7vw, 86px) clamp(26px, 6vw, 78px) 34px 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid var(--line);
        }

        .hero::after {
            display: none;
        }

        .eyebrow {
            display: inline-flex;
            width: fit-content;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: var(--accent);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "01";
            width: auto;
            height: auto;
            margin-right: 10px;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            color: #81796d;
        }

        h1 {
            max-width: 760px;
            margin: 24px 0 18px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(58px, 9vw, 112px);
            font-weight: 400;
            line-height: .88;
            letter-spacing: -.065em;
        }

        h1 span {
            color: var(--accent);
            background: none;
            -webkit-background-clip: initial;
            background-clip: initial;
        }

        .hero-copy {
            max-width: 600px;
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
        }

        .feature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            margin-top: 52px;
            border-top: 1px solid var(--line);
        }

        .feature {
            min-height: 104px;
            padding: 16px 14px 10px 0;
            border-radius: 0;
            border: 0;
            border-right: 1px solid var(--line);
            background: transparent;
        }

        .feature:not(:first-child) {
            padding-left: 14px;
        }

        .feature:last-child {
            border-right: 0;
        }

        .feature strong,
        .feature span {
            display: block;
        }

        .feature strong {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 17px;
            font-weight: 400;
        }

        .feature span {
            margin-top: 7px;
            color: #81796d;
            font-size: 10px;
            line-height: 1.45;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .start-card {
            min-height: 610px;
            padding: 34px 0 34px clamp(24px, 4vw, 46px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .start-card .icon {
            width: auto;
            height: auto;
            margin-bottom: 38px;
            display: block;
            border-radius: 0;
            background: none;
            color: var(--accent);
            font: 400 40px Georgia, "Times New Roman", serif;
            box-shadow: none;
        }

        .start-card h2 {
            margin: 0 0 9px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            font-weight: 400;
            letter-spacing: -.04em;
        }

        .start-card p {
            margin: 0 0 28px;
            color: var(--muted);
            line-height: 1.6;
            font-size: 13px;
        }

        form { margin: 0; }

        button {
            width: 100%;
            min-height: 52px;
            padding: 0 18px;
            border: 1px solid var(--ink);
            border-radius: 2px;
            cursor: pointer;
            color: var(--white);
            background: var(--ink);
            font: 700 12px "Helvetica Neue", Helvetica, Arial, sans-serif;
            letter-spacing: .08em;
            text-transform: uppercase;
            box-shadow: none;
            transition: background .15s ease, color .15s ease;
        }

        button:hover {
            transform: none;
            filter: none;
            color: var(--ink);
            background: transparent;
            box-shadow: none;
        }

        button:active {
            transform: translateY(1px);
        }

        .hint {
            margin-top: 13px;
            color: #8a8378;
            font-size: 9px;
            line-height: 1.5;
            text-align: left;
        }

        @media (max-width: 820px) {
            body {
                padding: 54px 18px 24px;
                display: block;
            }

            .shell {
                grid-template-columns: 1fr;
            }

            .hero {
                min-height: auto;
                padding: 42px 0 30px;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .start-card {
                min-height: auto;
                padding: 34px 0 38px;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 48px 14px 18px;
            }

            h1 {
                font-size: 58px;
            }

            .feature-row {
                grid-template-columns: 1fr;
            }

            .feature,
            .feature:not(:first-child) {
                min-height: auto;
                padding: 13px 0;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .feature:last-child {
                border-bottom: 0;
            }

            .feature span {
                margin-top: 3px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition-duration: .01ms !important;
            }
        }
    </style>
<style id="landing-motion-system">
        @keyframes landingRise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes accentDrift {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(2deg); }
        }
        @keyframes lineSweep {
            from { transform: scaleX(0); transform-origin: left; }
            to { transform: scaleX(1); transform-origin: left; }
        }
        .shell { animation: landingRise .65s cubic-bezier(.22,1,.36,1) both; }
        .eyebrow { animation: landingRise .5s .08s cubic-bezier(.22,1,.36,1) both; }
        h1 { animation: landingRise .7s .14s cubic-bezier(.22,1,.36,1) both; }
        .hero-copy { animation: landingRise .6s .22s cubic-bezier(.22,1,.36,1) both; }
        .feature-row { position: relative; animation: landingRise .6s .3s cubic-bezier(.22,1,.36,1) both; }
        .feature-row::before {
            content: "";
            position: absolute;
            inset: -1px 0 auto;
            height: 1px;
            background: var(--ink);
            animation: lineSweep .7s .35s cubic-bezier(.22,1,.36,1) both;
        }
        .feature {
            transition: transform .25s ease, background-color .25s ease, padding-left .25s ease;
        }
        .feature:hover { transform: translateY(-3px); padding-left: 6px; background: rgba(255,255,255,.12); }
        .feature strong { transition: color .2s ease; }
        .feature:hover strong { color: var(--accent); }
        .start-card { animation: landingRise .65s .18s cubic-bezier(.22,1,.36,1) both; }
        .start-card .icon { animation: accentDrift 3s ease-in-out 1s infinite; }
        button { transition: background .2s ease, color .2s ease, transform .2s ease, letter-spacing .2s ease; }
        button:hover { letter-spacing: .12em; transform: translateY(-2px); }
        button:active { transform: translateY(0); }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
</style>
</head>
<body>
    <main class="shell">
        <section class="hero">
            <div>
                <span class="eyebrow">Private realtime rooms</span>
                <h1>Meet without the <span>noise.</span></h1>
                <p class="hero-copy">
                    Video, screen sharing, collaborative notes, whiteboarding, and chat,
                    reduced to the essentials. No dashboard maze. No decorative glass.
                    Humanity has suffered enough translucent panels.
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
