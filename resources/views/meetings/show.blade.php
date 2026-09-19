<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meeting {{ $room }}</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07070a;
            --bg-glow: #111827;
            --surface: rgba(20, 20, 25, .82);
            --surface-strong: rgba(27, 27, 34, .94);
            --panel: #151518;
            --panel-2: #1d1d22;
            --text: #f5f5f5;
            --muted: #a1a1aa;
            --danger: #ef4444;
            --accent: #fafafa;
            --border: #27272d;
        }

        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100%; margin: 0; }
        body {
            min-height: 100dvh;
            overflow: hidden;
            background:
                radial-gradient(circle at 15% 0%, rgba(59, 130, 246, .10), transparent 28%),
                radial-gradient(circle at 85% 100%, rgba(139, 92, 246, .08), transparent 30%),
                var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button, input { font: inherit; }
        button {
            border: 0;
            border-radius: 10px;
            min-height: 42px;
            padding: 9px 13px;
            cursor: pointer;
            font-weight: 700;
            background: var(--panel-2);
            color: var(--text);
            transition: opacity .15s ease, transform .05s ease;
            touch-action: manipulation;
        }
        button:active { transform: translateY(1px); }
        button:hover { filter: brightness(1.12); }
        button:disabled { cursor: wait; opacity: .55; }
        input {
            width: min(240px, 60vw);
            min-height: 42px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 12px;
            background: var(--panel);
            color: var(--text);
            outline: none;
        }
        input:focus { border-color: #666; }

        header {
            position: fixed;
            inset: 0 0 auto;
            z-index: 40;
            display: grid;
            grid-template-columns: minmax(170px, 1fr) minmax(180px, .9fr) minmax(420px, 1.8fr);
            align-items: center;
            gap: 14px;
            min-height: 62px;
            padding: 9px 14px;
            padding-top: max(9px, env(safe-area-inset-top));
            border-bottom: 1px solid rgba(255,255,255,.07);
            background: rgba(8,8,11,.88);
            backdrop-filter: blur(20px) saturate(140%);
            box-shadow: 0 10px 30px rgba(0,0,0,.18);
        }
        .room {
            min-width: 0;
            display: flex;
            align-items: baseline;
            gap: 8px;
            overflow: hidden;
        }
        .room-kicker {
            flex: 0 0 auto;
            color: #71717a;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .12em;
        }
        .room-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 850;
            color: #f4f4f5;
        }
        .header-status {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 7px;
            color: #a1a1aa;
            font-size: 11px;
        }
        #status {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status-dot {
            flex: 0 0 auto;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #52525b;
            box-shadow: 0 0 0 3px rgba(82,82,91,.12);
        }
        .header-actions {
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
        }
        .header-actions input {
            width: min(170px, 18vw);
            min-height: 36px;
            border-radius: 10px;
            background: rgba(24,24,30,.78);
            border-color: #2d2d35;
        }
        .header-actions #join {
            min-height: 36px;
            padding: 7px 14px;
            border-radius: 10px;
        }
        .top-hud {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 0;
        }
        .hud-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            padding: 5px 9px;
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 10px;
            background: rgba(20,20,25,.72);
            color: #d4d4d8;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
            box-shadow: inset 0 1px rgba(255,255,255,.04);
        }
        .hud-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #71717a;
        }
        .hud-dot.good { background: #4ade80; box-shadow: 0 0 10px rgba(74,222,128,.4); }
        .hud-dot.warn { background: #facc15; box-shadow: 0 0 10px rgba(250,204,21,.35); }
        .hud-dot.bad { background: #ef4444; box-shadow: 0 0 10px rgba(239,68,68,.4); }
        .hud-icon { color: #a1a1aa; font-size: 8px; }
        .share-hud { color: #fecaca; border-color: rgba(239,68,68,.22); background: rgba(127,29,29,.22); }
        .share-hud-dot { width: 6px; height: 6px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 9px rgba(239,68,68,.75); }
        .status.error { color: #f87171; }
        .status.good { color: #86efac; }

        #grid {
            position: fixed;
            inset: 71px 0 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(300px, 32vw), 1fr));
            grid-auto-rows: minmax(170px, 1fr);
            align-content: start;
            gap: 10px;
            padding: 10px 10px 104px;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        #grid.compact {
            grid-template-columns: repeat(auto-fit, minmax(min(240px, 28vw), 1fr));
            grid-auto-rows: minmax(135px, 1fr);
        }
        #grid.dense {
            grid-template-columns: repeat(auto-fit, minmax(min(190px, 24vw), 1fr));
            grid-auto-rows: minmax(110px, 1fr);
            gap: 6px;
        }
        [hidden] { display: none !important; }

        .tile {
            position: relative;
            min-width: 0;
            min-height: 0;
            aspect-ratio: 16 / 10;
            background: var(--panel);
            border-radius: 14px;
            overflow: hidden;
            display: grid;
            place-items: center;
            border: 1px solid #222228;
            contain: layout paint;
            box-shadow: 0 8px 30px rgba(0,0,0,.22);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .tile:hover { border-color: #3a3a44; transform: translateY(-1px); }
        .tile.screen-share {
            border-color: rgba(248,113,113,.62);
            box-shadow: 0 0 0 1px rgba(239,68,68,.12), 0 18px 55px rgba(239,68,68,.10);
        }
        .tile.screen-share::before {
            content: "SCREEN SHARING";
            position: absolute;
            z-index: 5;
            top: 10px;
            left: 10px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(127,29,29,.82);
            color: #fecaca;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .08em;
            box-shadow: 0 4px 16px rgba(0,0,0,.24);
        }
        .screen-focus {
            position: absolute;
            z-index: 7;
            right: 10px;
            bottom: 10px;
            min-width: 34px;
            min-height: 34px;
            padding: 0 9px;
            border-radius: 10px;
            background: rgba(239,68,68,.92);
            color: #fff;
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 8px 24px rgba(0,0,0,.3);
        }
        .tile.zoomed { z-index: 8; box-shadow: 0 20px 60px rgba(0,0,0,.55); }
        .tile video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #050505;
            transform-origin: center center;
            will-change: transform;
            transition: transform .18s ease;
        }
        .tile.screen-share video { object-fit: contain; }
        .avatar {
            width: 96px;
            height: 96px;
            border-radius: 28px;
            display: block;
            object-fit: contain;
            background: transparent;
            box-shadow: none;
            user-select: none;
            cursor: pointer;
            transition: transform .2s ease, opacity .2s ease;
        }
        .tile.no-video {
            background:
                radial-gradient(circle at 50% 38%, rgba(255,255,255,.06), transparent 34%),
                linear-gradient(145deg, #19191f, #101014);
        }
        .tile.no-video .avatar {
            transform: scale(1.02);
        }
        .tile.no-video::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 42%, rgba(255,255,255,.05), transparent 38%);
        }
        .name {
            position: absolute;
            z-index: 3;
            left: 10px;
            bottom: 10px;
            max-width: calc(100% - 20px);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            background: #000b;
            padding: 6px 9px;
            border-radius: 8px;
            font-size: 13px;
        }
        .badge {
            position: absolute;
            z-index: 3;
            right: 10px;
            top: 10px;
            background: #000b;
            padding: 5px 8px;
            border-radius: 8px;
            font-size: 12px;
            color: #d4d4d8;
        }
        .speaking { outline: 2px solid #fff; outline-offset: -2px; }
        .avatar[hidden] { display: none !important; }
        .ping-pill { min-width: 88px; justify-content: center; }
        .ping-pill .hud-icon { font-size: 11px; }


        .tile-tools {
            position: absolute;
            z-index: 6;
            top: 8px;
            left: 8px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity .15s ease;
        }
        .tile:hover .tile-tools,
        .tile.zoomed .tile-tools { opacity: 1; }
        .tile-tools button {
            min-width: 30px;
            min-height: 30px;
            width: 30px;
            padding: 0;
            border-radius: 8px;
            background: rgba(0,0,0,.68);
            border: 1px solid rgba(255,255,255,.12);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
        }
        .tile-zoom-label {
            min-width: 42px;
            display: grid;
            place-items: center;
            padding: 0 6px;
            border-radius: 8px;
            background: rgba(0,0,0,.68);
            font-size: 11px;
            color: #ddd;
        }

        #whiteboard {
            position: fixed;
            z-index: 80;
            inset: 0;
            display: flex;
            flex-direction: column;
            background: #f8f8f7;
            color: #111;
        }
        #whiteboard[hidden] { display: none !important; }
        .whiteboard-toolbar {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: max(8px, env(safe-area-inset-top)) 10px 8px;
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid #ddd;
            box-shadow: 0 3px 18px rgba(0,0,0,.10);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .whiteboard-toolbar button {
            min-height: 38px;
            padding: 7px 11px;
            background: #eeeef0;
            color: #111;
            border: 1px solid #d6d6d8;
            white-space: nowrap;
        }
        .whiteboard-toolbar button.active { background: #111; color: #fff; }
        .whiteboard-toolbar input[type="color"] {
            width: 38px;
            min-height: 38px;
            padding: 3px;
            border-radius: 9px;
        }
        .whiteboard-toolbar input[type="range"] { width: 100px; }
        #whiteboard-canvas {
            flex: 1;
            min-height: 0;
            width: 100%;
            height: 100%;
            display: block;
            touch-action: none;
            cursor: crosshair;
            background-color: #fff;
            background-image:
                linear-gradient(#e8e8e8 1px, transparent 1px),
                linear-gradient(90deg, #e8e8e8 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .whiteboard-title {
            font-weight: 850;
            margin-right: 4px;
        }


        #shared-document {
            position: fixed;
            z-index: 85;
            inset: 0;
            display: flex;
            flex-direction: column;
            background: #f7f7f5;
            color: #171717;
        }
        #shared-document[hidden] { display: none !important; }
        .document-toolbar {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: max(8px, env(safe-area-inset-top)) 10px 8px;
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid #ddd;
            box-shadow: 0 3px 18px rgba(0,0,0,.10);
        }
        .document-toolbar .document-title {
            font-weight: 850;
            margin-right: 5px;
        }

        .document-toolbar {
            min-height: 72px;
            gap: 10px;
            padding: 9px 12px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.99), rgba(248,248,246,.96));
            border-bottom: 1px solid #d9d9dc;
            box-shadow: 0 10px 30px rgba(20,20,30,.12);
        }
        .document-brand {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 168px;
            padding-right: 10px;
            border-right: 1px solid #dedee1;
        }
        .document-brand-icon {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: linear-gradient(145deg, #18181b, #3f3f46);
            color: #fff;
            font-size: 16px;
            box-shadow: 0 6px 16px rgba(0,0,0,.18);
        }
        .document-brand strong,
        .document-brand small {
            display: block;
        }
        .document-brand strong {
            font-size: 12px;
            letter-spacing: -.01em;
        }
        .document-brand small {
            margin-top: 2px;
            color: #85858c;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .document-nav-scroll {
            min-width: 0;
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .document-nav-scroll::-webkit-scrollbar { display: none; }
        .document-tool-group {
            flex: 0 0 auto;
            align-items: center;
            gap: 4px;
            padding: 5px 8px 5px 6px;
            margin: 0;
            border: 1px solid #e2e2e5;
            border-radius: 12px;
            background: rgba(255,255,255,.8);
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .document-tool-group:last-of-type { border-right: 1px solid #e2e2e5; }
        .document-group-label {
            align-self: stretch;
            display: inline-flex;
            align-items: center;
            padding: 0 4px 0 2px;
            color: #9a9aa0;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .document-toolbar select {
            min-height: 32px;
            max-width: 132px;
            padding: 5px 28px 5px 9px;
            border: 1px solid #d8d8dc;
            border-radius: 8px;
            background: #fff;
            color: #242428;
            font: 700 11px system-ui, sans-serif;
            outline: none;
            cursor: pointer;
        }
        .document-toolbar select:focus {
            border-color: #8b8b94;
            box-shadow: 0 0 0 3px rgba(24,24,27,.08);
        }
        .document-toolbar button {
            min-height: 32px;
            padding: 5px 9px;
            border-radius: 8px;
            background: #f0f0f2;
            border-color: #dadade;
            color: #28282d;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .document-toolbar button:hover {
            filter: none;
            background: #e7e7ea;
            border-color: #c9c9ce;
        }
        .document-toolbar button:focus-visible {
            outline: 2px solid #71717a;
            outline-offset: 2px;
        }
        .document-color-control {
            position: relative;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid #d8d8dc;
            border-radius: 8px;
            background: #fff;
            color: #27272a;
            font: 800 13px Georgia, serif;
            cursor: pointer;
            overflow: hidden;
        }
        .document-color-control.highlight { color: #b08a00; }
        .document-color-control input {
            position: absolute;
            inset: 0;
            width: 100%;
            min-height: 100%;
            padding: 0;
            border: 0;
            opacity: 0;
            cursor: pointer;
        }
        .document-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
        }
        .document-action {
            white-space: nowrap;
        }
        .document-action.primary {
            background: #18181b;
            border-color: #18181b;
            color: #fff;
            box-shadow: 0 5px 14px rgba(24,24,27,.18);
        }
        .document-action.secondary {
            background: #fff;
        }
        .document-status {
            margin-left: 2px;
            padding: 7px 9px;
            border: 1px solid #e2e2e5;
            border-radius: 8px;
            background: #fafafa;
            color: #71717a;
            font-size: 10px;
            font-weight: 800;
        }
        .document-toolbar .toolbar-close {
            min-width: 34px;
            width: 34px;
            height: 34px;
            min-height: 34px;
            padding: 0;
            border-radius: 9px;
            background: #ececef;
            font-size: 19px;
            line-height: 1;
        }
        .document-tool-group {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding-right: 8px;
            margin-right: 2px;
            border-right: 1px solid #e1e1e3;
        }
        .document-tool-group:last-of-type {
            border-right: 0;
        }
        .document-toolbar button {
            min-height: 36px;
            padding: 6px 10px;
            background: #eeeef0;
            color: #171717;
            border: 1px solid #d5d5d7;
        }
        .document-toolbar button.active { background: #171717; color: #fff; }
        .document-status {
            margin-left: auto;
            color: #666;
            font-size: 12px;
            white-space: nowrap;
        }
        #document-editor {
            flex: 1;
            width: min(900px, calc(100% - 28px));
            margin: 14px auto;
            padding: 42px 52px;
            overflow-y: auto;
            outline: none;
            background: #fff;
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            box-shadow: 0 12px 35px rgba(0,0,0,.08);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18px;
            line-height: 1.65;
            -webkit-overflow-scrolling: touch;
        }
        #document-editor[contenteditable="false"] {
            background: #fdfdfc;
            cursor: default;
        }
        #document-editor h1, #document-editor h2, #document-editor h3 {
            line-height: 1.2;
        }
        #document-editor blockquote {
            margin: 1em 0;
            padding-left: 1em;
            border-left: 4px solid #bbb;
            color: #555;
        }
        .document-access {
            position: fixed;
            z-index: 90;
            right: 16px;
            top: 70px;
            width: min(360px, calc(100vw - 24px));
            max-height: calc(100dvh - 100px);
            overflow-y: auto;
            padding: 12px;
            background: #151518f5;
            color: #fff;
            border: 1px solid #33333b;
            border-radius: 14px;
            box-shadow: 0 18px 60px #0009;
            backdrop-filter: blur(16px);
        }
        .document-access h3 { margin: 0 0 5px; font-size: 15px; }
        .document-access p { margin: 0 0 10px; color: #aaa; font-size: 12px; }
        .document-access-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 0;
            border-top: 1px solid #2b2b31;
        }
        .document-access-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .document-access-row button { min-height: 32px; padding: 5px 8px; }
        .document-readonly {
            padding: 8px 12px;
            background: #fff4d6;
            border-bottom: 1px solid #ead7a2;
            color: #704d00;
            font: 600 12px system-ui, sans-serif;
        }

        #audio-root {
            position: fixed;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .controls {
            position: fixed;
            z-index: 50;
            left: 50%;
            bottom: max(12px, env(safe-area-inset-bottom));
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 7px;
            max-width: calc(100vw - 20px);
            padding: 8px;
            background: #17171be8;
            border: 1px solid #303038;
            border-radius: 16px;
            backdrop-filter: blur(14px);
            box-shadow: 0 10px 35px #0008;
        }
        .controls button { white-space: nowrap; }
        #screen.sharing {
            background: #dc2626;
            color: #fff;
            box-shadow: 0 0 0 1px rgba(255,255,255,.12), 0 8px 24px rgba(220,38,38,.30);
            animation: sharePulse 1.8s ease-in-out infinite;
        }
        @keyframes sharePulse {
            0%, 100% { box-shadow: 0 0 0 1px rgba(255,255,255,.12), 0 8px 24px rgba(220,38,38,.25); }
            50% { box-shadow: 0 0 0 4px rgba(220,38,38,.12), 0 10px 30px rgba(220,38,38,.38); }
        }
        #leave { background: var(--danger); color: #fff; }
        #join { background: var(--accent); color: #111; }
        .quality {
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            color: var(--muted);
            padding: 0 3px;
        }

        #presentation {
            position: fixed;
            z-index: 120;
            inset: 0;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at 50% 20%, rgba(239,68,68,.08), transparent 35%),
                #050507;
        }
        #presentation[hidden] { display: none !important; }
        .presentation-bar {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: max(10px, env(safe-area-inset-top)) 12px 10px;
            background: rgba(10,10,13,.82);
            border-bottom: 1px solid #29292f;
            backdrop-filter: blur(18px);
        }
        .presentation-title {
            min-width: 0;
            margin-right: auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 850;
        }
        .presentation-live {
            color: #fecaca;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .08em;
        }
        #presentation-video {
            flex: 1;
            min-height: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #020204;
        }
        .presentation-bar button {
            min-height: 36px;
            padding: 6px 10px;
            background: #1f1f25;
            border: 1px solid #34343d;
        }
        .presentation-bar button.danger {
            background: #dc2626;
            color: #fff;
        }

        .toolbar-spacer {
            margin-left: auto;
        }

        .toolbar-close {
            width: 36px;
            min-width: 36px;
            min-height: 36px;
            padding: 0 !important;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-size: 20px !important;
            line-height: 1;
        }

        .document-toolbar select {
            min-height: 36px;
            padding: 6px 9px;
            border: 1px solid #d5d5d7;
            border-radius: 9px;
            background: #eeeef0;
            color: #171717;
            font: inherit;
            font-weight: 700;
        }

        .document-toolbar input[type="color"] {
            width: 36px;
            min-height: 36px;
            padding: 3px;
            border: 1px solid #d5d5d7;
            border-radius: 9px;
            background: #eeeef0;
        }

        .chat-toggle {
            position: fixed;
            z-index: 55;
            right: 16px;
            bottom: max(16px, env(safe-area-inset-bottom));
            width: 50px;
            height: 50px;
            min-height: 50px;
            padding: 0;
            border-radius: 50%;
            background: var(--accent);
            color: #111;
            box-shadow: 0 8px 30px #0008;
        }
        .chat-unread {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 11px;
            display: grid;
            place-items: center;
        }
        .chat-panel {
            position: fixed;
            z-index: 54;
            right: 16px;
            bottom: 78px;
            width: min(360px, calc(100vw - 24px));
            height: min(520px, calc(100dvh - 110px));
            display: flex;
            flex-direction: column;
            background: #111114f7;
            border: 1px solid #303038;
            border-radius: 16px;
            backdrop-filter: blur(16px);
            box-shadow: 0 18px 60px #0009;
            overflow: hidden;
        }
        .chat-header {
            position: static;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #2a2a30;
            background: transparent;
            backdrop-filter: none;
            font-weight: 800;
        }
        .chat-header button { min-height: 34px; padding: 5px 9px; }
        .chat-messages {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            -webkit-overflow-scrolling: touch;
        }
        .chat-empty {
            margin: auto;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
        }
        .chat-message {
            max-width: 85%;
            align-self: flex-start;
            padding: 8px 10px;
            border-radius: 12px 12px 12px 4px;
            background: var(--panel-2);
            overflow-wrap: anywhere;
        }
        .chat-message.mine {
            align-self: flex-end;
            background: #f4f4f5;
            color: #111;
            border-radius: 12px 12px 4px 12px;
        }
        .chat-author { font-size: 11px; font-weight: 800; opacity: .7; margin-bottom: 3px; }
        .chat-body { white-space: pre-wrap; word-break: break-word; font-size: 14px; line-height: 1.4; }
        .chat-time { margin-top: 4px; font-size: 10px; opacity: .55; text-align: right; }
        .chat-form {
            display: flex;
            gap: 7px;
            padding: 9px;
            border-top: 1px solid #2a2a30;
        }
        .chat-form input { flex: 1; width: auto; min-width: 0; }
        .chat-form button { flex: 0 0 auto; }

        @media (max-width: 760px) {
            body { overflow: hidden; }
            .top-hud { overflow-x: auto; scrollbar-width: none; }
            .top-hud::-webkit-scrollbar { display: none; }
            .hud-pill { flex: 0 0 auto; }
            .presentation-bar { padding-bottom: 8px; }
            .presentation-bar button { min-height: 40px; }

            header {
                grid-template-columns: 1fr auto;
                gap: 6px;
                min-height: 72px;
                padding: 8px 10px;
                padding-top: max(8px, env(safe-area-inset-top));
            }
            .room { min-width: 0; }
            .room-kicker { display: none; }
            .room-name { font-size: 13px; }
            .header-status { grid-column: 1 / -1; grid-row: 2; }
            .header-actions { grid-column: 2; grid-row: 1; }
            .header-actions input { width: 110px; }
            #status { font-size: 11px; }
            #join { min-height: 36px; }
            .status { font-size: 12px; }
            #grid {
                inset: 76px 0 0;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-auto-rows: auto;
                gap: 7px;
                padding: 7px 7px 100px;
            }
            #grid.compact,
            #grid.dense {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-auto-rows: auto;
                gap: 7px;
            }
            .tile {
                width: 100%;
                min-height: 0;
                aspect-ratio: 16 / 10;
                border-radius: 12px;
            }
            .avatar { width: 64px; height: 64px; border-radius: 20px; }
            .name { left: 7px; bottom: 7px; max-width: calc(100% - 14px); padding: 5px 7px; font-size: 11px; }
            .badge { right: 7px; top: 7px; padding: 5px 7px; font-size: 10px; }
            .controls { gap: 6px; padding: 7px; border-radius: 14px; }
            .controls button { min-height: 44px; }
            .tile-tools { opacity: 1; top: 6px; left: 6px; }
            .tile-tools button { min-width: 28px; width: 28px; min-height: 28px; font-size: 12px; }
            .tile-zoom-label { display: none; }
            .controls {
                left: 10px;
                right: 10px;
                bottom: max(8px, env(safe-area-inset-bottom));
                width: auto;
                max-width: none;
                transform: none;
                overflow-x: auto;
                justify-content: flex-start;
                scrollbar-width: none;
            }
            .controls::-webkit-scrollbar { display: none; }
            .controls button { flex: 1 0 auto; min-width: 78px; }

            .document-toolbar {
                min-height: 58px;
                flex-wrap: nowrap;
                padding: 7px 8px;
            }
            .document-brand {
                min-width: 138px;
                padding-right: 7px;
            }
            .document-brand-icon {
                width: 30px;
                height: 30px;
            }
            .document-brand small { display: none; }
            .document-nav-scroll { order: 2; }
            .document-actions {
                order: 3;
                margin-left: 0;
            }
            .document-action.secondary,
            .document-status {
                display: none;
            }
            .document-toolbar select { max-width: 118px; }
            .document-group-label { display: none; }

            #document-editor {
                width: calc(100% - 12px);
                margin: 7px auto;
                padding: 28px 20px;
                font-size: 17px;
                border-radius: 4px;
            }
            .document-status { margin-left: 0; width: 100%; }
            .document-access { right: 8px; top: 78px; width: calc(100vw - 16px); }

            .chat-toggle {
                right: 10px;
                bottom: calc(76px + env(safe-area-inset-bottom));
                width: 46px;
                height: 46px;
                min-height: 46px;
            }
            .chat-panel {
                right: 8px;
                bottom: calc(132px + env(safe-area-inset-bottom));
                width: calc(100vw - 16px);
                height: min(62dvh, 500px);
                border-radius: 14px;
            }
        }

        @media (max-width: 600px) {
            #grid,
            #grid.compact,
            #grid.dense {
                grid-template-columns: 1fr;
                gap: 9px;
                padding: 8px 8px 112px;
            }
            .tile { aspect-ratio: 16 / 9; min-height: 210px; }
            .avatar { width: 82px; height: 82px; border-radius: 24px; }
            .name { font-size: 12px; }
            .tile-tools { top: 8px; left: 8px; }
            .tile-tools button { min-width: 32px; width: 32px; min-height: 32px; }
            .controls {
                left: 8px;
                right: 8px;
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                overflow: visible;
            }
            .controls button {
                min-width: 0;
                width: 100%;
                padding: 8px 6px;
                font-size: 12px;
            }
            .quality { display: none; }
        }

        @media (max-width: 420px) {
            .tile { min-height: 190px; }
            .presentation-bar .presentation-live { display: none; }
            .avatar { width: 72px; height: 72px; }
            .controls {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    
        /* Visual polish: subtle technical hatch background without interfering with the meeting UI. */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -2;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255,255,255,.035) 25%, transparent 25%) 0 0 / 22px 22px,
                linear-gradient(315deg, rgba(255,255,255,.022) 25%, transparent 25%) 0 0 / 22px 22px,
                radial-gradient(circle at 18% 12%, rgba(99,102,241,.11), transparent 30%),
                radial-gradient(circle at 82% 88%, rgba(168,85,247,.09), transparent 32%);
            opacity: .7;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                repeating-linear-gradient(
                    -45deg,
                    transparent 0,
                    transparent 9px,
                    rgba(255,255,255,.018) 9px,
                    rgba(255,255,255,.018) 10px
                );
            mask-image: linear-gradient(to bottom, rgba(0,0,0,.9), rgba(0,0,0,.35));
        }
        header {
            background: rgba(9,9,13,.72);
            border-bottom-color: rgba(255,255,255,.10);
            box-shadow: 0 12px 38px rgba(0,0,0,.24);
        }
        .tile {
            background:
                linear-gradient(145deg, rgba(255,255,255,.025), transparent 48%),
                rgba(17,17,22,.88);
            border-color: rgba(255,255,255,.075);
            box-shadow: 0 12px 36px rgba(0,0,0,.25);
            backdrop-filter: blur(5px);
        }
        .tile:hover {
            border-color: rgba(255,255,255,.16);
            box-shadow: 0 16px 42px rgba(0,0,0,.30);
        }
        .controls {
            background: rgba(18,18,23,.78);
            border-color: rgba(255,255,255,.11);
            box-shadow: 0 14px 42px rgba(0,0,0,.42);
        }
        .hud-pill {
            background: rgba(20,20,26,.62);
            border-color: rgba(255,255,255,.10);
            backdrop-filter: blur(12px);
        }
        @media (prefers-reduced-transparency: reduce) {
            header, .controls, .hud-pill { backdrop-filter: none; }
        }

    </style>
</head>
<body>
<header>
    <div class="room"><span class="room-kicker">LIVE ROOM</span><span class="room-name">{{ $room }}</span></div>
    <div class="header-status">
        <span id="status-dot" class="status-dot"></span>
        <span id="status" class="status">Enter your name and join the room.</span>
    </div>
    <div class="header-actions">
        <input id="name" placeholder="Your name" maxlength="80" autocomplete="name">
        <button id="join">Join</button>
        <div class="top-hud" aria-label="Meeting status">
            <span class="hud-pill network-pill"><span id="network-dot" class="hud-dot"></span><span id="network-label">Offline</span></span>
            <span class="hud-pill ping-pill"><span class="hud-icon">↕</span><span id="ping-label">Ping -- ms</span></span>
            <span id="share-indicator" class="hud-pill share-hud" hidden><span class="share-hud-dot"></span>Sharing</span>
        </div>
    </div>
</header>

<div id="grid"></div>
<div id="audio-root" aria-hidden="true"></div>

<section id="presentation" hidden aria-label="Screen share presentation">
    <div class="presentation-bar">
        <span class="presentation-live">LIVE</span>
        <span id="presentation-title" class="presentation-title">Screen share</span>
        <button id="presentation-fullscreen" type="button">Fullscreen</button>
        <button id="presentation-close" class="danger" type="button">Close</button>
    </div>
    <video id="presentation-video" autoplay playsinline></video>
</section>

<section id="whiteboard" hidden aria-label="Collaborative whiteboard">
    <div class="whiteboard-toolbar">
        <span class="whiteboard-title">Whiteboard</span>
        <button id="whiteboard-pen" class="active" type="button">Pen</button>
        <button id="whiteboard-eraser" type="button">Eraser</button>
        <input id="whiteboard-color" type="color" value="#111111" aria-label="Pen color">
        <input id="whiteboard-size" type="range" min="1" max="32" value="4" aria-label="Brush size">
        <button id="whiteboard-zoom-out" type="button">−</button>
        <span id="whiteboard-zoom-label">100%</span>
        <button id="whiteboard-zoom-in" type="button">+</button>
        <button id="whiteboard-reset" type="button">Reset view</button>
        <button id="whiteboard-clear" type="button">Clear</button>
        <span class="toolbar-spacer"></span>
        <button id="whiteboard-close" class="toolbar-close" type="button" aria-label="Close whiteboard" title="Close">×</button>
    </div>
    <canvas id="whiteboard-canvas" tabindex="0"></canvas>
</section>


<section id="shared-document" hidden aria-label="Shared document">
    <div class="document-toolbar">
        <div class="document-brand">
            <span class="document-brand-icon">✦</span>
            <span>
                <strong>Shared document</strong>
                <small>Collaborative notes</small>
            </span>
        </div>

        <div class="document-nav-scroll" role="toolbar" aria-label="Document editing tools">
            <div class="document-tool-group" aria-label="Text style">
                <span class="document-group-label">Style</span>
                <select data-doc-command="formatBlock" aria-label="Text style" title="Text style">
                    <option value="p">Paragraph</option>
                    <option value="h1">Title</option>
                    <option value="h2">Heading 1</option>
                    <option value="h3">Heading 2</option>
                    <option value="blockquote">Quote</option>
                </select>
                <select data-doc-command="fontName" aria-label="Font" title="Font">
                    <option value="Georgia">Georgia</option>
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Courier New">Monospace</option>
                </select>
                <select data-doc-command="fontSize" aria-label="Text size" title="Text size">
                    <option value="3">Normal</option>
                    <option value="1">Small</option>
                    <option value="4">Large</option>
                    <option value="5">Huge</option>
                </select>
            </div>

            <div class="document-tool-group" aria-label="Formatting">
                <span class="document-group-label">Format</span>
                <button type="button" data-doc-command="bold" title="Bold"><b>B</b></button>
                <button type="button" data-doc-command="italic" title="Italic"><i>I</i></button>
                <button type="button" data-doc-command="underline" title="Underline"><u>U</u></button>
                <button type="button" data-doc-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                <button type="button" data-doc-command="removeFormat" title="Clear formatting">Clear</button>
            </div>

            <div class="document-tool-group" aria-label="Alignment and lists">
                <span class="document-group-label">Layout</span>
                <button type="button" data-doc-command="justifyLeft" title="Align left">Left</button>
                <button type="button" data-doc-command="justifyCenter" title="Align center">Center</button>
                <button type="button" data-doc-command="justifyRight" title="Align right">Right</button>
                <button type="button" data-doc-command="insertUnorderedList" title="Bulleted list">• List</button>
                <button type="button" data-doc-command="insertOrderedList" title="Numbered list">1. List</button>
                <button type="button" data-doc-command="outdent" title="Decrease indent">Outdent</button>
                <button type="button" data-doc-command="indent" title="Increase indent">Indent</button>
            </div>

            <div class="document-tool-group" aria-label="History and colors">
                <span class="document-group-label">Edit</span>
                <button type="button" data-doc-command="undo" title="Undo">↶</button>
                <button type="button" data-doc-command="redo" title="Redo">↷</button>
                <label class="document-color-control" title="Text color">
                    <span>A</span>
                    <input type="color" data-doc-command="foreColor" value="#171717" aria-label="Text color">
                </label>
                <label class="document-color-control highlight" title="Highlight color">
                    <span>▰</span>
                    <input type="color" data-doc-command="hiliteColor" value="#fff2a8" aria-label="Highlight color">
                </label>
            </div>
        </div>

        <div class="document-actions">
            <button id="document-access-toggle" class="document-action secondary" type="button" hidden>Manage access</button>
            <button id="document-download" class="document-action primary" type="button">Download Word</button>
            <span id="document-status" class="document-status">View only</span>
            <button id="document-close" class="toolbar-close" type="button" aria-label="Close shared document" title="Close">×</button>
        </div>
    </div>
    <div id="document-readonly" class="document-readonly" hidden>
        You can read this document, but the host has not given you edit access.
    </div>
    <div id="document-editor" contenteditable="false" spellcheck="true">
        <h1>Meeting notes</h1>
        <p>Start writing together...</p>
    </div>
    <aside id="document-access" class="document-access" hidden>
        <h3>Document access</h3>
        <p>Only the host can change who may edit. Changes apply immediately.</p>
        <div id="document-access-list"></div>
    </aside>
</section>

<button id="chat-toggle" class="chat-toggle" hidden type="button" aria-label="Open chat" title="Chat">
    Chat
    <span id="chat-unread" class="chat-unread" hidden>0</span>
</button>

<section id="chat-panel" class="chat-panel" hidden aria-label="Meeting chat">
    <div class="chat-header">
        <span>Chat</span>
        <button id="chat-close" type="button" aria-label="Close chat">Close</button>
    </div>
    <div id="chat-messages" class="chat-messages" aria-live="polite">
        <div id="chat-empty" class="chat-empty">No messages yet.<br>Messages are available while you are in this room.</div>
    </div>
    <form id="chat-form" class="chat-form">
        <input id="chat-input" maxlength="1000" autocomplete="off" placeholder="Write a message..." aria-label="Chat message">
        <button id="chat-send" type="submit">Send</button>
    </form>
</section>

<div class="controls" hidden>
    <button id="mic">Mute</button>
    <button id="camera">Camera off</button>
    <button id="screen">Share screen</button>
    <button id="whiteboard-toggle" type="button">Whiteboard</button>
    <button id="document-toggle" type="button">Shared doc</button>
    <button id="copy-link" type="button">Copy link</button>
    <button id="leave">Leave</button>

</div>

<script type="module">
import {
    Room,
    RoomEvent,
    ConnectionState,
    Track,
    VideoPresets,
    ScreenSharePresets,
    isBrowserSupported,
    ConnectionCheck,
} from 'https://cdn.jsdelivr.net/npm/livekit-client@2.22.3/+esm';

const roomName = @json($room);
const livekitUrl = @json($livekitUrl);
const grid = document.getElementById('grid');
const audioRoot = document.getElementById('audio-root');
const nameInput = document.getElementById('name');
const joinButton = document.getElementById('join');
const status = document.getElementById('status');
const controls = document.querySelector('.controls');
const micButton = document.getElementById('mic');
const cameraButton = document.getElementById('camera');
const screenButton = document.getElementById('screen');
const leaveButton = document.getElementById('leave');
const whiteboardToggle = document.getElementById('whiteboard-toggle');
const whiteboard = document.getElementById('whiteboard');
const whiteboardCanvas = document.getElementById('whiteboard-canvas');
const whiteboardPen = document.getElementById('whiteboard-pen');
const whiteboardEraser = document.getElementById('whiteboard-eraser');
const whiteboardColor = document.getElementById('whiteboard-color');
const whiteboardSize = document.getElementById('whiteboard-size');
const whiteboardZoomOut = document.getElementById('whiteboard-zoom-out');
const whiteboardZoomIn = document.getElementById('whiteboard-zoom-in');
const whiteboardZoomLabel = document.getElementById('whiteboard-zoom-label');
const whiteboardReset = document.getElementById('whiteboard-reset');
const whiteboardClear = document.getElementById('whiteboard-clear');
const whiteboardClose = document.getElementById('whiteboard-close');
let whiteboardOpen = false;
let whiteboardDrawing = null;
let whiteboardStrokes = [];
let whiteboardMode = 'pen';
let whiteboardZoom = 1;
let whiteboardOffsetX = 0;
let whiteboardOffsetY = 0;
const zoomLevels = new Map();
const copyLinkButton = document.getElementById('copy-link');
const presentation = document.getElementById('presentation');
const presentationVideo = document.getElementById('presentation-video');
const presentationTitle = document.getElementById('presentation-title');
const presentationClose = document.getElementById('presentation-close');
const presentationFullscreen = document.getElementById('presentation-fullscreen');
const networkDot = document.getElementById('network-dot');
const networkLabel = document.getElementById('network-label');
const pingLabel = document.getElementById('ping-label');
const shareIndicator = document.getElementById('share-indicator');
const chatToggle = document.getElementById('chat-toggle');
const chatUnread = document.getElementById('chat-unread');
const chatPanel = document.getElementById('chat-panel');
const chatClose = document.getElementById('chat-close');
const chatMessages = document.getElementById('chat-messages');
const chatEmpty = document.getElementById('chat-empty');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
const chatSend = document.getElementById('chat-send');

const documentToggle = document.getElementById('document-toggle');
const sharedDocument = document.getElementById('shared-document');
const documentEditor = document.getElementById('document-editor');
const documentClose = document.getElementById('document-close');
const documentDownload = document.getElementById('document-download');
const documentAccessToggle = document.getElementById('document-access-toggle');
const documentAccess = document.getElementById('document-access');
const documentAccessList = document.getElementById('document-access-list');
const documentStatus = document.getElementById('document-status');
const documentReadonly = document.getElementById('document-readonly');

const csrf = document.querySelector('meta[name="csrf-token"]').content;

let room = null;
let joining = false;
let leaving = false;
let audioUnlockNeeded = false;
let connectTimeout = null;
let chatOpen = false;
let unreadMessages = 0;
let lastJoinAttempt = 0;
let reconnecting = false;
let presentationTrack = null;
let pingTimer = null;
const screenShareRetryTimers = new Set();

const mediaElements = new Map();

let documentOpen = false;
let documentCanEdit = false;
let documentHostIdentity = null;
let documentHtml = documentEditor.innerHTML;
let documentRevision = 0;
let documentSaveTimer = null;
let documentApplyingRemote = false;
const documentPermissions = new Map();
let whiteboardLiveStrokes = new Map();
let whiteboardStrokeSequence = 0;
let whiteboardLastPublishAt = 0;


function setStatus(message, type = '') {
    status.textContent = message;
    status.classList.toggle('error', type === 'error');
    status.classList.toggle('good', type === 'good');
}

function updateChatUnread() {
    chatUnread.textContent = unreadMessages > 99 ? '99+' : String(unreadMessages);
    chatUnread.hidden = unreadMessages === 0;
}

function setChatOpen(open) {
    chatOpen = open;
    chatPanel.hidden = !open;

    if (open) {
        unreadMessages = 0;
        updateChatUnread();
        requestAnimationFrame(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
            chatInput.focus();
        });
    }
}

function appendChatMessage({ name, text, mine = false, timestamp = Date.now() }) {
    if (!text) return;

    chatEmpty.hidden = true;

    const message = document.createElement('article');
    message.className = 'chat-message' + (mine ? ' mine' : '');

    const author = document.createElement('div');
    author.className = 'chat-author';
    author.textContent = mine ? 'You' : (name || 'Participant');

    const body = document.createElement('div');
    body.className = 'chat-body';
    body.textContent = text;

    const time = document.createElement('div');
    time.className = 'chat-time';
    time.textContent = new Date(timestamp).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });

    message.append(author, body, time);
    chatMessages.appendChild(message);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    if (!chatOpen && !mine) {
        unreadMessages += 1;
        updateChatUnread();
    }
}

async function sendChatMessage(event) {
    event.preventDefault();
    if (!room || leaving || room.state !== ConnectionState.Connected) return;

    const text = chatInput.value.trim();
    if (!text) return;

    chatSend.disabled = true;

    try {
        const payload = {
            type: 'chat',
            text: text.slice(0, 1000),
            timestamp: Date.now(),
        };

        await room.localParticipant.publishData(
            new TextEncoder().encode(JSON.stringify(payload)),
            { reliable: true, topic: 'chat' },
        );

        appendChatMessage({
            name: room.localParticipant.name || nameInput.value.trim(),
            text: payload.text,
            mine: true,
            timestamp: payload.timestamp,
        });

        chatInput.value = '';
        chatInput.focus();
    } catch (error) {
        console.error('Chat send failed:', error);
        setStatus('Could not send the message. Check your connection.', 'error');
    } finally {
        chatSend.disabled = false;
    }
}

function handleChatData(payload, participant, topic) {
    if (topic !== 'chat' || !participant) return;

    try {
        const message = JSON.parse(new TextDecoder().decode(payload));

        if (
            message?.type !== 'chat' ||
            typeof message.text !== 'string' ||
            !message.text.trim()
        ) return;

        appendChatMessage({
            name: participant.name || participant.identity,
            text: message.text.slice(0, 1000),
            mine: participant === room?.localParticipant,
            timestamp: Number.isFinite(message.timestamp) ? message.timestamp : Date.now(),
        });
    } catch (error) {
        console.warn('Ignoring invalid chat message:', error);
    }
}


function documentParticipants() {
    if (!room) return [];
    return [room.localParticipant, ...room.remoteParticipants.values()]
        .filter(Boolean)
        .sort((a, b) => {
            const at = a.joinedAt?.getTime?.() ?? Number.MAX_SAFE_INTEGER;
            const bt = b.joinedAt?.getTime?.() ?? Number.MAX_SAFE_INTEGER;
            return at - bt || String(a.identity).localeCompare(String(b.identity));
        });
}

function electDocumentHost() {
    const participants = documentParticipants();
    if (!participants.length) return null;

    const nextHost = participants[0].identity;
    const changed = documentHostIdentity !== nextHost;
    documentHostIdentity = nextHost;

    if (changed && isDocumentHost()) {
        documentPermissions.set(room.localParticipant.identity, true);
        publishDocumentPacket({
            type: 'host',
            hostIdentity: documentHostIdentity,
            permissions: Object.fromEntries(documentPermissions),
        });
        sendDocumentState();
    }

    updateDocumentPermissionUi();
    updateDocumentUi();
    return nextHost;
}

function isDocumentHost() {
    return !!room?.localParticipant && documentHostIdentity === room.localParticipant.identity;
}

function canEditDocument(identity = room?.localParticipant?.identity) {
    return !!identity && (identity === documentHostIdentity || documentPermissions.get(identity) === true);
}

function updateDocumentUi() {
    documentCanEdit = canEditDocument();
    documentEditor.contentEditable = documentCanEdit ? 'true' : 'false';
    documentReadonly.hidden = documentCanEdit || !documentOpen;
    documentStatus.textContent = isDocumentHost()
        ? 'Host · You can edit'
        : documentCanEdit
            ? 'Can edit'
            : 'View only';
    documentAccessToggle.hidden = !isDocumentHost();
}

function updateDocumentPermissionUi() {
    documentAccessList.replaceChildren();
    if (!room) return;

    documentParticipants().forEach(participant => {
        const row = document.createElement('div');
        row.className = 'document-access-row';

        const label = document.createElement('span');
        label.className = 'document-access-name';
        label.textContent = participant.name || participant.identity;

        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = canEditDocument(participant.identity) ? 'Can edit' : 'View only';
        button.disabled = !isDocumentHost() || participant.identity === room.localParticipant.identity;
        button.addEventListener('click', () => {
            if (!isDocumentHost()) return;
            const next = !canEditDocument(participant.identity);
            documentPermissions.set(participant.identity, next);
            publishDocumentPacket({
                type: 'permission',
                identity: participant.identity,
                canEdit: next,
            });
            updateDocumentPermissionUi();
            if (participant.identity === room.localParticipant.identity) updateDocumentUi();
        });

        row.append(label, button);
        documentAccessList.appendChild(row);
    });
}

function openSharedDocument(open = true) {
    documentOpen = open;
    sharedDocument.hidden = !open;
    if (!open) {
        documentAccess.hidden = true;
        return;
    }

    electDocumentHost();
    updateDocumentUi();
    updateDocumentPermissionUi();
    if (isDocumentHost()) sendDocumentState();

    requestAnimationFrame(() => {
        if (documentCanEdit) documentEditor.focus();
    });
}

async function publishDocumentPacket(payload, destinationIdentities = undefined) {
    if (!room || room.state !== ConnectionState.Connected) return;
    try {
        await room.localParticipant.publishData(
            new TextEncoder().encode(JSON.stringify(payload)),
            {
                reliable: true,
                topic: 'document-control',
                ...(destinationIdentities?.length ? { destinationIdentities } : {}),
            },
        );
    } catch (error) {
        console.warn('Document control sync failed:', error);
    }
}

async function sendDocumentState(destinationIdentities = undefined) {
    if (!room || room.state !== ConnectionState.Connected) return;
    const html = documentEditor.innerHTML.slice(0, 60000);
    documentHtml = html;
    documentRevision = Math.max(documentRevision, Date.now());
    try {
        await room.localParticipant.sendText(
            JSON.stringify({
                type: 'state',
                html,
                revision: documentRevision,
            }),
            {
                topic: 'shared-document',
                ...(destinationIdentities?.length ? { destinationIdentities } : {}),
            },
        );
    } catch (error) {
        console.warn('Document state sync failed:', error);
    }
}

function scheduleDocumentSync() {
    if (!documentCanEdit || documentApplyingRemote) return;
    documentHtml = documentEditor.innerHTML.slice(0, 60000);
    clearTimeout(documentSaveTimer);
    documentSaveTimer = setTimeout(() => {
        sendDocumentState();
    }, 35);
}

function applyDocumentState(html, revision = Date.now()) {
    if (typeof html !== 'string' || html.length > 60000) return;
    if (revision < documentRevision) return;

    documentRevision = revision;
    documentHtml = html;
    documentApplyingRemote = true;
    documentEditor.innerHTML = html || '<p><br></p>';
    documentApplyingRemote = false;
    updateDocumentUi();
}

function handleDocumentControl(payload, participant) {
    if (!participant || !payload) return;

    if (payload.type === 'host') {
        if (!documentHostIdentity || participant.identity === documentHostIdentity) {
            documentHostIdentity = payload.hostIdentity || documentHostIdentity;
            if (payload.permissions && typeof payload.permissions === 'object') {
                Object.entries(payload.permissions).forEach(([identity, canEdit]) => {
                    documentPermissions.set(identity, canEdit === true);
                });
            }
            documentPermissions.set(documentHostIdentity, true);
            updateDocumentPermissionUi();
            updateDocumentUi();
        }
        return;
    }

    if (payload.type === 'permission' && participant.identity === documentHostIdentity) {
        if (typeof payload.identity === 'string') {
            documentPermissions.set(payload.identity, payload.canEdit === true);
            updateDocumentPermissionUi();
            updateDocumentUi();
        }
    }
}

async function handleDocumentStream(reader, participantInfo) {
    try {
        const message = JSON.parse(await reader.readAll());
        if (message?.type !== 'state' || !participantInfo?.identity) return;

        const senderIsHost = participantInfo.identity === documentHostIdentity;
        const senderCanEdit = canEditDocument(participantInfo.identity);
        if (!senderIsHost && !senderCanEdit) return;

        applyDocumentState(message.html, Number(message.revision) || Date.now());
    } catch (error) {
        console.warn('Ignoring invalid shared document state:', error);
    }
}

function downloadSharedDocument() {
    const html = '<!doctype html><html><head><meta charset="utf-8"><title>Meeting document</title></head><body>' +
        documentEditor.innerHTML +
        '</body></html>';
    const blob = new Blob([html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = 'meeting-document.doc';
    anchor.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function initials(name) {
    return (name || '?')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(part => part[0]?.toUpperCase() || '')
        .join('') || '?';
}

const blobatarExpressions = [
    'idle',
    'happy',
    'sad',
    'mad',
    'surprised',
    'wink',
    'sleepy',
    'smug',
    'unsure',
    'scared',
    'love',
    'shy',
    'sick',
    'thinking',
];

function blobatarUrl(name, size = 96, expression = 'idle') {
    const value = String(name || 'participant').trim() || 'participant';
    const pose = blobatarExpressions.includes(expression) ? expression : 'idle';
    return 'https://blobatar.dev/avatar/' + encodeURIComponent(value) +
        '?size=' + encodeURIComponent(size) +
        '&background=none' +
        '&expression=' + encodeURIComponent(pose);
}

function cycleBlobatarExpression(participant) {
    const tile = participantTile(participant);
    const avatar = tile.querySelector('.avatar');
    if (!avatar) return;

    const current = avatar.dataset.expression || 'idle';
    const index = blobatarExpressions.indexOf(current);
    const next = blobatarExpressions[(index + 1) % blobatarExpressions.length];
    const displayName = participant.name || participant.identity;

    avatar.dataset.expression = next;
    avatar.src = blobatarUrl(displayName, 96, next);
    avatar.setAttribute('aria-label', displayName + ' expression: ' + next);
}

function participantTile(participant) {
    const selector = '[data-identity="' + CSS.escape(participant.identity) + '"]';
    let tile = grid.querySelector(selector);

    if (!tile) {
        tile = document.createElement('div');
        tile.className = 'tile';
        tile.dataset.identity = participant.identity;

        const avatar = document.createElement('img');
        avatar.className = 'avatar';
        avatar.alt = '';
        avatar.width = 82;
        avatar.height = 82;
        avatar.loading = 'lazy';
        avatar.decoding = 'async';
        avatar.referrerPolicy = 'no-referrer';

        const name = document.createElement('span');
        name.className = 'name';

        const badge = document.createElement('span');
        badge.className = 'badge';

        const tools = document.createElement('div');
        tools.className = 'tile-tools';
        tools.innerHTML = '<button type="button" data-zoom-action="out" aria-label="Zoom out">−</button>' +
            '<span class="tile-zoom-label">100%</span>' +
            '<button type="button" data-zoom-action="in" aria-label="Zoom in">+</button>' +
            '<button type="button" data-zoom-action="reset" aria-label="Reset zoom">Reset</button>';
        tools.addEventListener('click', event => {
            const button = event.target.closest('[data-zoom-action]');
            if (!button) return;
            event.stopPropagation();
            changeParticipantZoom(participant.identity, button.dataset.zoomAction);
        });

        tile.append(avatar, name, badge, tools);
        grid.appendChild(tile);
    }

    const displayName = participant.name || participant.identity;
    tile.querySelector('.name').textContent = displayName;

    const avatar = tile.querySelector('.avatar');
    if (avatar) {
        avatar.alt = displayName;
        avatar.dataset.expression = avatar.dataset.expression || 'idle';
        avatar.src = blobatarUrl(displayName, 96, avatar.dataset.expression);
        avatar.onclick = event => {
            event.stopPropagation();
            cycleBlobatarExpression(participant);
        };
        avatar.onerror = () => {
            avatar.onerror = null;
            avatar.removeAttribute('src');
            avatar.alt = displayName;
            avatar.style.background = 'transparent';
        };
    }
    attachTileZoomGesture(tile, participant);

    if (!tile.dataset.screenClickReady) {
        tile.dataset.screenClickReady = '1';
        tile.addEventListener('click', event => {
            if (event.target.closest('.tile-tools, .screen-focus, .name, .badge')) return;
            const video = event.target.closest('video');
            if (!video || video.dataset.source !== Track.Source.ScreenShare) return;
            openPresentation(participant);
        });
    }

    return tile;
}


function participantZoom(identity) {
    return zoomLevels.get(identity) || 1;
}

function applyParticipantZoom(participant) {
    const tile = participantTile(participant);
    const video = tile.querySelector('video');
    if (!video) return;

    const zoom = participantZoom(participant.identity);
    video.style.transform = 'scale(' + zoom + ')';
    tile.classList.toggle('zoomed', zoom > 1);
    const label = tile.querySelector('.tile-zoom-label');
    if (label) label.textContent = Math.round(zoom * 100) + '%';
}

function changeParticipantZoom(identity, action) {
    const current = participantZoom(identity);
    const next = action === 'in'
        ? Math.min(3, current + 0.25)
        : action === 'out'
            ? Math.max(1, current - 0.25)
            : 1;

    zoomLevels.set(identity, next);
    const tile = grid.querySelector('[data-identity="' + CSS.escape(identity) + '"]');
    const video = tile?.querySelector('video');
    if (video) video.style.transform = 'scale(' + next + ')';
    tile?.classList.toggle('zoomed', next > 1);
    const label = tile?.querySelector('.tile-zoom-label');
    if (label) label.textContent = Math.round(next * 100) + '%';
}

function attachTileZoomGesture(tile, participant) {
    if (tile.dataset.zoomGestureReady) return;
    tile.dataset.zoomGestureReady = '1';

    let lastTap = 0;
    tile.addEventListener('dblclick', event => {
        if (event.target.closest('.tile-tools')) return;
        const next = participantZoom(participant.identity) > 1 ? 1 : 1.5;
        zoomLevels.set(participant.identity, next);
        applyParticipantZoom(participant);
    });
    tile.addEventListener('touchend', event => {
        if (event.target.closest('.tile-tools')) return;
        const now = Date.now();
        if (now - lastTap < 280) {
            event.preventDefault();
            const next = participantZoom(participant.identity) > 1 ? 1 : 1.5;
            zoomLevels.set(participant.identity, next);
            applyParticipantZoom(participant);
        }
        lastTap = now;
    }, { passive: false });
}

function updateGridDensity() {
    const count = grid.querySelectorAll('.tile').length;
    grid.classList.toggle('dense', count >= 13);
    grid.classList.toggle('compact', count >= 7 && count < 13);
}

function updateBadge(participant) {
    const tile = participantTile(participant);
    const micPublication = participant.getTrackPublication(Track.Source.Microphone);
    const cameraPublication = participant.getTrackPublication(Track.Source.Camera);
    const parts = [];

    if (micPublication?.isMuted || micPublication?.isEnabled === false) parts.push('Muted');
    if (cameraPublication?.isMuted || cameraPublication?.isEnabled === false) parts.push('Camera off');

    tile.querySelector('.badge').textContent = parts.join(' · ');
    tile.querySelector('.badge').hidden = parts.length === 0;
    tile.classList.toggle('camera-off', !!cameraPublication && (cameraPublication.isMuted || cameraPublication.isEnabled === false));
}

function currentVideoPublication(participant) {
    const publications = participant?.videoTrackPublications
        ? [...participant.videoTrackPublications.values()]
        : [];

    // Screen share is a presentation surface, so it always wins over camera
    // when both are subscribed. This prevents a later camera event from
    // replacing the shared screen in the participant tile.
    const active = publication => (
        publication?.track &&
        publication.isMuted !== true &&
        publication.isEnabled !== false
    );

    const screenShare = publications.find(publication =>
        publication.source === Track.Source.ScreenShare && active(publication)
    );

    // Never render the outgoing screen-share track back into the sender's tile.
    // The sender already has the native browser share preview, and decoding the
    // same stream again adds avoidable CPU/GPU work and can make the presenter lag.
    if (participant === room?.localParticipant && screenShare) {
        return publications.find(publication =>
            publication.source === Track.Source.Camera && active(publication)
        ) || null;
    }

    return screenShare || publications.find(publication =>
        publication.source === Track.Source.Camera && active(publication)
    ) || null;
}

function removeVideoForParticipant(participant) {
    const tile = participantTile(participant);
    tile.querySelector('.avatar')?.removeAttribute('hidden');
    tile.querySelectorAll('video').forEach(element => element.remove());

    for (const [sid, element] of mediaElements.entries()) {
        if (element.tagName === 'VIDEO' && element.dataset.identity === participant.identity) {
            element.remove();
            mediaElements.delete(sid);
        }
    }

    tile.classList.remove('screen-share');
    tile.classList.add('no-video');
    tile.querySelector('.screen-focus')?.remove();
    if (presentationTrack?.participant === participant) closePresentation();
}

function renderParticipantVideo(participant) {
    const publication = currentVideoPublication(participant);
    const tile = participantTile(participant);

    if (!publication?.track) {
        removeVideoForParticipant(participant);
        tile.classList.add('no-video');
        updateGridDensity();
        return;
    }

    const track = publication.track;
    const sid = publication.trackSid || track.sid;
    if (!sid) return;

    const source = publication.source || track.source || Track.Source.Camera;
    const isScreenShare = source === Track.Source.ScreenShare;
    const existing = tile.querySelector('video');

    if (existing?.dataset.trackSid === sid) {
        tile.querySelector('.avatar')?.setAttribute('hidden', 'hidden');
        tile.classList.remove('no-video');
        tile.classList.toggle('screen-share', isScreenShare);
        syncScreenFocusButton(tile, participant, isScreenShare);
        applyParticipantZoom(participant);
        return;
    }

    if (existing) {
        const oldSid = existing.dataset.trackSid;
        existing.remove();
        if (oldSid) mediaElements.delete(oldSid);
    }

    const element = track.attach();
    element.autoplay = true;
    element.playsInline = true;
    element.muted = participant === room?.localParticipant || isScreenShare;
    element.volume = 0;
    element.dataset.trackSid = sid;
    element.dataset.identity = participant.identity;
    element.dataset.source = source;

    tile.querySelector('.avatar')?.setAttribute('hidden', 'hidden');
    tile.insertBefore(element, tile.querySelector('.avatar'));
    tile.classList.remove('no-video');
    tile.classList.toggle('screen-share', isScreenShare);
    syncScreenFocusButton(tile, participant, isScreenShare);
    mediaElements.set(sid, element);
    applyParticipantZoom(participant);
    updateGridDensity();
}

function syncScreenFocusButton(tile, participant, isScreenShare) {
    let button = tile.querySelector('.screen-focus');
    if (!isScreenShare) {
        button?.remove();
        if (presentationTrack?.participant === participant) closePresentation();
        return;
    }
    if (!button) {
        button = document.createElement('button');
        button.type = 'button';
        button.className = 'screen-focus';
        button.textContent = '⛶';
        button.title = 'Open screen share fullscreen';
        button.setAttribute('aria-label', 'Open screen share fullscreen');
        button.addEventListener('click', event => {
            event.stopPropagation();
            openPresentation(participant);
        });
        tile.appendChild(button);
    }
}

function openPresentation(participant) {
    const publication = participant && currentVideoPublication(participant);
    if (!publication?.track || (publication.source || publication.track.source) !== Track.Source.ScreenShare) return;

    closePresentation();
    presentationTrack = { track: publication.track, participant };
    presentationTitle.textContent = (participant.name || participant.identity) + ' · Screen share';
    publication.track.attach(presentationVideo);
    presentationVideo.autoplay = true;
    presentationVideo.playsInline = true;
    presentationVideo.muted = true;
    presentationVideo.volume = 0;
    presentation.hidden = false;
    presentationVideo.play().catch(() => {});
}

function closePresentation() {
    if (presentationTrack?.track) {
        try { presentationTrack.track.detach(presentationVideo); } catch {}
    }
    presentationTrack = null;
    presentation.hidden = true;
}

async function fullscreenPresentation() {
    try {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        } else if (presentation.requestFullscreen) {
            await presentation.requestFullscreen();
        } else if (presentationVideo.requestFullscreen) {
            await presentationVideo.requestFullscreen();
        }
    } catch (error) {
        console.warn('Presentation fullscreen failed:', error);
    }
}

function updateNetworkHud() {
    if (!room || room.state !== ConnectionState.Connected) {
        networkDot.className = 'hud-dot';
        networkLabel.textContent = reconnecting ? 'Reconnecting' : 'Offline';
        return;
    }
    const value = String(room.localParticipant.connectionQuality || 'unknown').toLowerCase();
    networkDot.className = 'hud-dot ' + (value === 'excellent' ? 'good' : value === 'good' ? 'good' : value === 'poor' ? 'warn' : value === 'lost' ? 'bad' : '');
    networkLabel.textContent = value === 'excellent' ? 'Excellent' : value === 'good' ? 'Good' : value === 'poor' ? 'Poor' : value === 'lost' ? 'Lost' : 'Connecting';
    const statusDot = document.getElementById('status-dot');
    if (statusDot) {
        statusDot.style.background = value === 'excellent' || value === 'good'
            ? '#4ade80'
            : value === 'poor'
                ? '#facc15'
                : value === 'lost'
                    ? '#ef4444'
                    : '#52525b';
    }
}

async function updatePingHud() {
    if (!room || room.state !== ConnectionState.Connected) {
        pingLabel.textContent = 'Ping -- ms';
        return;
    }

    let bestRtt = null;
    const publications = room.localParticipant?.trackPublications
        ? [...room.localParticipant.trackPublications.values()]
        : [];

    for (const publication of publications) {
        const track = publication?.track;
        if (!track || typeof track.getRTCStatsReport !== 'function') continue;

        try {
            const report = await track.getRTCStatsReport();
            if (!report) continue;

            for (const stat of report.values()) {
                if (
                    stat?.type === 'remote-inbound-rtp' &&
                    Number.isFinite(stat.roundTripTime) &&
                    stat.roundTripTime >= 0
                ) {
                    bestRtt = bestRtt === null
                        ? stat.roundTripTime
                        : Math.min(bestRtt, stat.roundTripTime);
                }
                if (
                    stat?.type === 'candidate-pair' &&
                    Number.isFinite(stat.currentRoundTripTime) &&
                    stat.currentRoundTripTime >= 0
                ) {
                    bestRtt = bestRtt === null
                        ? stat.currentRoundTripTime
                        : Math.min(bestRtt, stat.currentRoundTripTime);
                }
            }
        } catch (error) {
            console.debug('Ping stats unavailable:', error);
        }
    }

    pingLabel.textContent = bestRtt === null
        ? 'Ping -- ms'
        : 'Ping ' + Math.max(0, Math.round(bestRtt * 1000)) + ' ms';
}

function startPingMonitor() {
    if (pingTimer) clearInterval(pingTimer);
    pingTimer = window.setInterval(updatePingHud, 1500);
    updatePingHud();
}

function stopPingMonitor() {
    if (pingTimer) {
        clearInterval(pingTimer);
        pingTimer = null;
    }
    pingLabel.textContent = 'Ping -- ms';
}

async function ensureRemoteScreenShareSubscribed(publication, participant) {
    if (
        !publication ||
        publication.source !== Track.Source.ScreenShare ||
        participant === room?.localParticipant
    ) {
        return;
    }

    try {
        if (publication.isEnabled === false) {
            publication.setEnabled(true);
        }
        if (typeof publication.setVideoDimensions === 'function') {
            publication.setVideoDimensions({ width: 1920, height: 1080 });
        }
        if (!publication.isSubscribed) {
            publication.setSubscribed(true);
        }
        console.debug('LiveKit screen share subscription requested:', {
            identity: participant?.identity,
            trackSid: publication.trackSid,
            subscribed: publication.isSubscribed,
            enabled: publication.isEnabled,
        });
    } catch (error) {
        console.warn('Remote screen share subscribe failed:', {
            identity: participant?.identity,
            trackSid: publication.trackSid,
            error,
        });
    }
}

async function subscribeToRemoteScreenShares() {
    if (!room || room.state !== ConnectionState.Connected) return;

    for (const participant of room.remoteParticipants.values()) {
        for (const publication of participant.videoTrackPublications?.values() || []) {
            if (publication.source === Track.Source.ScreenShare) {
                await ensureRemoteScreenShareSubscribed(publication, participant);
            }
        }
    }
}

function scheduleScreenShareSubscriptionRetries() {
    screenShareRetryTimers.forEach(timer => clearTimeout(timer));
    screenShareRetryTimers.clear();

    const delays = [0, 250, 750, 1500, 3000];

    delays.forEach(delay => {
        const timer = window.setTimeout(() => {
            screenShareRetryTimers.delete(timer);
            subscribeToRemoteScreenShares().catch(error => {
                console.warn('Screen share subscription pass failed:', error);
            });
        }, delay);
        screenShareRetryTimers.add(timer);
    });
}

function updateShareButton() {
    const publication = room?.localParticipant?.getTrackPublication(Track.Source.ScreenShare);
    const sharing = !!publication && !publication.isMuted && publication.isEnabled !== false && !!publication.track;

    screenButton.classList.toggle('sharing', sharing);
    screenButton.textContent = sharing ? 'Stop sharing' : 'Share screen';
    shareIndicator.hidden = !sharing;

}

function detachTrack(track) {
    if (!track) return;

    const sid = track.sid;
    track.detach().forEach(element => element.remove());

    if (sid) mediaElements.delete(sid);

    const participant = track.participant;
    if (participant && track.kind === 'video') {
        renderParticipantVideo(participant);
    }
}

function attachRemoteAudio(track, participant, publication) {
    if (participant === room?.localParticipant) return;

    const sid = publication?.trackSid || track.sid;
    if (!sid || mediaElements.has(sid)) return;

    const element = track.attach();
    element.autoplay = true;
    element.playsInline = true;
    element.dataset.trackSid = sid;

    audioRoot.appendChild(element);
    mediaElements.set(sid, element);

    element.play().catch(() => {
        audioUnlockNeeded = true;
        setStatus('Connected. Tap the page once to enable remote audio.');
    });
}

function attachTrack(track, participant, publication) {
    if (!track) return;

    if (track.kind === 'video') {
        renderParticipantVideo(participant);
    } else if (track.kind === 'audio') {
        attachRemoteAudio(track, participant, publication);
    }

    updateBadge(participant);
    applyParticipantZoom(participant);
}

function removeParticipant(participant) {
    participant.trackPublications.forEach(publication => {
        if (publication.track) detachTrack(publication.track);
    });

    grid.querySelectorAll('[data-identity="' + CSS.escape(participant.identity) + '"]').forEach(tile => tile.remove());

    for (const [sid, element] of mediaElements.entries()) {
        if (element.dataset.identity === participant.identity) {
            element.remove();
            mediaElements.delete(sid);
        }
    }

    updateGridDensity();
}

function renderParticipant(participant) {
    participantTile(participant);

    participant.trackPublications.forEach(publication => {
        if (publication.track) attachTrack(publication.track, participant, publication);
    });

    renderParticipantVideo(participant);
    updateBadge(participant);
}

function renderAllParticipants() {
    if (!room) return;

    participantTile(room.localParticipant);
    renderParticipant(room.localParticipant);

    room.remoteParticipants.forEach(participant => {
        renderParticipant(participant);

    });

    updateGridDensity();
    updateButtons();
    scheduleScreenShareSubscriptionRetries();
}

function clearMedia() {
    closePresentation();
    mediaElements.clear();
    grid.replaceChildren();
    audioRoot.replaceChildren();
}

function cleanupRoom() {
    if (!room) return;

    room.localParticipant.trackPublications.forEach(publication => {
        if (publication.track) publication.track.detach();
    });

    room.remoteParticipants.forEach(participant => {
        participant.trackPublications.forEach(publication => {
            if (publication.track) publication.track.detach();
        });
    });

    clearMedia();
}

function updateButtons() {
    if (!room) return;

    const mic = room.localParticipant.getTrackPublication(Track.Source.Microphone);
    const camera = room.localParticipant.getTrackPublication(Track.Source.Camera);
    const screen = room.localParticipant.getTrackPublication(Track.Source.ScreenShare);

    const micOn = !!mic && !mic.isMuted && mic.isEnabled !== false;
    const cameraOn = !!camera && !camera.isMuted && camera.isEnabled !== false;
    const screenOn = !!screen && !screen.isMuted && screen.isEnabled !== false;

    micButton.textContent = micOn ? 'Mute' : 'Unmute';
    cameraButton.textContent = cameraOn ? 'Camera off' : 'Camera on';
    screenButton.classList.toggle('sharing', screenOn);
    screenButton.textContent = screenOn ? 'Stop sharing' : 'Share screen';
    shareIndicator.hidden = !screenOn;

    updateBadge(room.localParticipant);
}

async function unlockAudio() {
    if (!room || !audioUnlockNeeded) return;

    try {
        if (typeof room.startAudio === 'function') {
            await room.startAudio();
        } else {
            await Promise.all(
                [...audioRoot.querySelectorAll('audio')].map(element => element.play().catch(() => {})),
            );
        }

        audioUnlockNeeded = false;
        setStatus('Connected as ' + nameInput.value.trim(), 'good');
    } catch (error) {
        console.warn('Audio unlock failed:', error);
    }
}


function setWhiteboardZoom(nextZoom) {
    whiteboardZoom = Math.min(3, Math.max(0.5, Number(nextZoom) || 1));
    whiteboardZoomLabel.textContent = Math.round(whiteboardZoom * 100) + '%';
    redrawWhiteboard();
}

function openWhiteboard(open = true) {
    if (open && (!room || room.state !== ConnectionState.Connected)) {
        setStatus('Join the meeting before opening the whiteboard.', 'error');
        return;
    }

    whiteboardOpen = open;
    whiteboard.hidden = !open;

    if (!open) {
        whiteboardDrawing = null;
        return;
    }

    requestAnimationFrame(() => {
        resizeWhiteboardCanvas();
        redrawWhiteboard();
    });
}

function resizeWhiteboardCanvas() {
    const rect = whiteboardCanvas.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const old = whiteboardCanvas.width ? whiteboardCanvas.toDataURL() : null;
    whiteboardCanvas.width = Math.max(1, Math.floor(rect.width * dpr));
    whiteboardCanvas.height = Math.max(1, Math.floor(rect.height * dpr));
    const ctx = whiteboardCanvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    if (old) {
        const image = new Image();
        image.onload = () => {
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.drawImage(image, 0, 0, rect.width, rect.height);
        };
        image.src = old;
    }
    redrawWhiteboard();
}

function whiteboardPoint(event) {
    const rect = whiteboardCanvas.getBoundingClientRect();
    return {
        x: (event.clientX - rect.left - whiteboardOffsetX) / whiteboardZoom,
        y: (event.clientY - rect.top - whiteboardOffsetY) / whiteboardZoom,
    };
}

function drawStroke(stroke) {
    const ctx = whiteboardCanvas.getContext('2d');
    if (!stroke?.points?.length) return;
    ctx.save();
    ctx.translate(whiteboardOffsetX, whiteboardOffsetY);
    ctx.scale(whiteboardZoom, whiteboardZoom);
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = Number(stroke.size) || 4;
    ctx.strokeStyle = stroke.mode === 'eraser' ? '#ffffff' : (stroke.color || '#111111');
    ctx.beginPath();
    stroke.points.forEach((point, index) => {
        if (index === 0) ctx.moveTo(point.x, point.y);
        else ctx.lineTo(point.x, point.y);
    });
    if (stroke.points.length === 1) ctx.lineTo(stroke.points[0].x + .01, stroke.points[0].y);
    ctx.stroke();
    ctx.restore();
}


function drawStrokeSegment(stroke, from, to) {
    const ctx = whiteboardCanvas.getContext('2d');
    ctx.save();
    ctx.translate(whiteboardOffsetX, whiteboardOffsetY);
    ctx.scale(whiteboardZoom, whiteboardZoom);
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = Number(stroke.size) || 4;
    ctx.strokeStyle = stroke.mode === 'eraser' ? '#ffffff' : (stroke.color || '#111111');
    ctx.beginPath();
    ctx.moveTo(from.x, from.y);
    ctx.lineTo(to.x, to.y);
    ctx.stroke();
    ctx.restore();
}

function redrawWhiteboard() {
    const ctx = whiteboardCanvas.getContext('2d');
    const rect = whiteboardCanvas.getBoundingClientRect();
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, whiteboardCanvas.width, whiteboardCanvas.height);
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    whiteboardStrokes.forEach(drawStroke);
    whiteboardLiveStrokes.forEach(stroke => drawStroke(stroke));
}

async function publishWhiteboard(payload, reliable = false, destinationIdentities = undefined) {
    if (!room || room.state !== ConnectionState.Connected) return;
    try {
        await room.localParticipant.publishData(
            new TextEncoder().encode(JSON.stringify(payload)),
            {
                reliable,
                topic: 'whiteboard',
                ...(destinationIdentities?.length ? { destinationIdentities } : {}),
            },
        );
    } catch (error) {
        console.warn('Whiteboard sync failed:', error);
    }
}

function whiteboardStrokeId() {
    whiteboardStrokeSequence += 1;
    return room?.localParticipant?.identity + ':' + Date.now() + ':' + whiteboardStrokeSequence;
}

function handleWhiteboardData(payload, participant, topic) {
    if (topic !== 'whiteboard' || !participant) return;
    try {
        const message = JSON.parse(new TextDecoder().decode(payload));

        if (message.type === 'snapshot') {
            if (Array.isArray(message.strokes)) {
                whiteboardStrokes = message.strokes.slice(-500);
                whiteboardLiveStrokes.clear();
                if (whiteboardOpen) redrawWhiteboard();
            }
            return;
        }

        if (message.type === 'stroke-start' && message.stroke) {
            whiteboardLiveStrokes.set(message.stroke.id, message.stroke);
            if (whiteboardOpen) drawStroke(message.stroke);
            return;
        }

        if (message.type === 'stroke-point' && message.id && message.point) {
            const stroke = whiteboardLiveStrokes.get(message.id);
            if (!stroke) return;
            const previous = stroke.points[stroke.points.length - 1];
            stroke.points.push(message.point);
            if (whiteboardOpen && previous) drawStrokeSegment(stroke, previous, message.point);
            return;
        }

        if (message.type === 'stroke-end' && message.stroke) {
            whiteboardLiveStrokes.delete(message.stroke.id);
            whiteboardStrokes.push(message.stroke);
            if (whiteboardOpen) redrawWhiteboard();
            return;
        }

        if (message.type === 'clear') {
            whiteboardStrokes = [];
            whiteboardLiveStrokes.clear();
            if (whiteboardOpen) redrawWhiteboard();
        }
    } catch (error) {
        console.warn('Ignoring invalid whiteboard message:', error);
    }
}

function beginWhiteboardStroke(event) {
    if (!whiteboardOpen || event.button !== 0) return;
    const stroke = {
        id: whiteboardStrokeId(),
        points: [whiteboardPoint(event)],
        color: whiteboardColor.value,
        size: Number(whiteboardSize.value),
        mode: whiteboardMode,
    };
    whiteboardDrawing = stroke;
    whiteboardLastPublishAt = performance.now();
    whiteboardLiveStrokes.set(stroke.id, stroke);
    redrawWhiteboard();
    whiteboardCanvas.setPointerCapture(event.pointerId);
    publishWhiteboard({ type: 'stroke-start', stroke }, true);
    event.preventDefault();
}

function moveWhiteboardStroke(event) {
    if (!whiteboardDrawing) return;
    const point = whiteboardPoint(event);
    const previous = whiteboardDrawing.points[whiteboardDrawing.points.length - 1];
    whiteboardDrawing.points.push(point);
    redrawWhiteboard();

    const now = performance.now();
    if (now - whiteboardLastPublishAt >= 16) {
        whiteboardLastPublishAt = now;
        publishWhiteboard({
            type: 'stroke-point',
            id: whiteboardDrawing.id,
            point,
        }, false);
    }

    event.preventDefault();
}

async function endWhiteboardStroke(event) {
    if (!whiteboardDrawing) return;
    const stroke = whiteboardDrawing;
    whiteboardDrawing = null;
    whiteboardCanvas.releasePointerCapture?.(event.pointerId);
    whiteboardStrokes.push(stroke);
    whiteboardLiveStrokes.delete(stroke.id);
    redrawWhiteboard();
    await publishWhiteboard({ type: 'stroke-end', stroke }, true);
    event.preventDefault();
}

async function fetchToken(name) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 12000);

    try {
        const response = await fetch('/meetings/' + encodeURIComponent(roomName) + '/token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name }),
            signal: controller.signal,
            cache: 'no-store',
        });

        if (!response.ok) {
            const body = await response.text();
            throw new Error('Token request failed (' + response.status + '): ' + body);
        }

        const data = await response.json();

        if (!data.server_url || !data.participant_token) {
            throw new Error('Laravel returned an invalid LiveKit token response.');
        }

        return data;
    } catch (error) {
        if (error.name === 'AbortError') {
            throw new Error('The server took too long to create the meeting token.');
        }
        throw error;
    } finally {
        clearTimeout(timeout);
    }
}

function isMobile() {
    return window.matchMedia('(max-width: 760px)').matches ||
        /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

function roomOptions() {
    const mobile = isMobile();

    return new Room({
        // Keep screen-share delivery deterministic. Adaptive stream and dynacast
        // can pause/reduce video based on element visibility; a meeting UI that
        // swaps tracks dynamically should not let those optimizations hide a
        // presentation track.
        adaptiveStream: false,
        dynacast: false,
        disconnectOnPageLeave: true,
        singlePeerConnection: true,
        audioCaptureDefaults: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
        },
        videoCaptureDefaults: {
            resolution: (mobile ? VideoPresets.h360 : VideoPresets.h720).resolution,
            facingMode: 'user',
        },
        publishDefaults: {
            simulcast: true,
        },
    });
}


function syncNewParticipant(participant) {
    if (!room || !participant) return;
    participant.waitUntilActive?.().then(() => {
        if (!room || room.state !== ConnectionState.Connected) return;
        if (isDocumentHost()) {
            sendDocumentState([participant.identity]);
            publishDocumentPacket({
                type: 'host',
                hostIdentity: documentHostIdentity,
                permissions: Object.fromEntries(documentPermissions),
            }, [participant.identity]);
        }
        if (documentStrokesForSync()) {
            publishWhiteboard({
                type: 'snapshot',
                strokes: whiteboardStrokes.slice(-500),
            }, true, [participant.identity]);
        }
    }).catch(() => {});
}

function documentStrokesForSync() {
    return whiteboardStrokes.length > 0;
}

function setupRoomEvents() {
    room.registerTextStreamHandler('shared-document', handleDocumentStream);

    room
        .on(RoomEvent.DataReceived, (payload, participant, _kind, topic) => {
            handleChatData(payload, participant, topic);
            handleWhiteboardData(payload, participant, topic);
            if (topic === 'document-control') {
                try {
                    handleDocumentControl(JSON.parse(new TextDecoder().decode(payload)), participant);
                } catch (error) {
                    console.warn('Ignoring invalid document control message:', error);
                }
            }
        })
        .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            if (publication?.source === Track.Source.ScreenShare) {
                if (typeof publication.setEnabled === 'function') publication.setEnabled(true);
                if (typeof publication.setVideoDimensions === 'function') {
                    publication.setVideoDimensions({ width: 1920, height: 1080 });
                }
                console.debug('LiveKit screen share subscribed:', {
                    identity: participant?.identity,
                    trackSid: publication.trackSid,
                });
            }
            attachTrack(track, participant, publication);
            renderParticipantVideo(participant);
            if (publication?.source === Track.Source.ScreenShare) {
                setStatus((participant?.name || participant?.identity || 'Participant') + ' is sharing their screen.', 'good');
            }
        })
        .on(RoomEvent.TrackUnsubscribed, track => {
            detachTrack(track);
        })
        .on(RoomEvent.TrackPublished, async (publication, participant) => {
            participantTile(participant);
            updateBadge(participant);

            if (
                participant !== room?.localParticipant &&
                publication.source === Track.Source.ScreenShare
            ) {
                await ensureRemoteScreenShareSubscribed(publication, participant);
                scheduleScreenShareSubscriptionRetries();
            }

            if (publication.track) {
                attachTrack(publication.track, participant, publication);
            } else if (participant) {
                renderParticipantVideo(participant);
            }
        })
        .on(RoomEvent.TrackUnpublished, (publication, participant) => {
            if (publication.track) detachTrack(publication.track);
            if (participant) renderParticipantVideo(participant);
            if (participant) updateBadge(participant);
        })
        .on(RoomEvent.ParticipantConnected, participant => {
            renderParticipant(participant);
            scheduleScreenShareSubscriptionRetries();
            updateGridDensity();
            updateDocumentPermissionUi();
            syncNewParticipant(participant);
        })
        .on(RoomEvent.ParticipantDisconnected, participant => {
            if (participant?.identity === documentHostIdentity) {
                documentHostIdentity = null;
                electDocumentHost();
            }
            documentPermissions.delete(participant?.identity);
            removeParticipant(participant);
            updateDocumentPermissionUi();
            updateDocumentUi();
        })
        .on(RoomEvent.LocalTrackPublished, publication => {
            if (publication.track) attachTrack(publication.track, room.localParticipant, publication);
            renderParticipantVideo(room.localParticipant);
            updateButtons();
            updateShareButton();
        })
        .on(RoomEvent.LocalTrackUnpublished, publication => {
            if (publication.track) detachTrack(publication.track);
            renderParticipantVideo(room.localParticipant);
            updateButtons();
            updateShareButton();
        })
        .on(RoomEvent.TrackMuted, (publication, participant) => {
            if (participant) updateBadge(participant);
            if (participant === room?.localParticipant) updateButtons();
        })
        .on(RoomEvent.TrackUnmuted, (publication, participant) => {
            if (participant) updateBadge(participant);
            if (participant === room?.localParticipant) updateButtons();
        })
        .on(RoomEvent.ActiveSpeakersChanged, speakers => {
            grid.querySelectorAll('.tile').forEach(tile => tile.classList.remove('speaking'));
            speakers.forEach(participant => {
                grid.querySelector('[data-identity="' + CSS.escape(participant.identity) + '"]')
                    ?.classList.add('speaking');
            });
        })
        .on(RoomEvent.ConnectionQualityChanged, (_connectionQuality, participant) => {
            if (participant === room?.localParticipant) {
                updateNetworkHud();
                updatePingHud();
            }
        })
        .on(RoomEvent.ParticipantActive, participant => {
            if (participant) {
                renderParticipant(participant);
                scheduleScreenShareSubscriptionRetries();
            }
        })
        .on(RoomEvent.AudioPlaybackStatusChanged, () => {
            if (room && !room.canPlaybackAudio) {
                audioUnlockNeeded = true;
                setStatus('Connected. Tap the page once to enable remote audio.');
            }
        })
        .on(RoomEvent.MediaDevicesError, error => {
            console.warn('Media device error:', error);
            setStatus('Camera or microphone is unavailable. Check browser permissions.', 'error');
        })
        .on(RoomEvent.TrackSubscriptionFailed, async (trackSid, participant, reason) => {
            console.warn('Track subscription failed:', trackSid, participant?.identity, reason);
            const publication = participant?.videoTrackPublications
                ? [...participant.videoTrackPublications.values()].find(item => item.trackSid === trackSid)
                : null;
            if (publication?.source === Track.Source.ScreenShare) {
                await ensureRemoteScreenShareSubscribed(publication, participant);
                scheduleScreenShareSubscriptionRetries();
                return;
            }
            if (participant) {
                setStatus('A participant video could not be loaded. Reconnecting media...', 'error');
            }
        })
        .on(RoomEvent.TrackSubscriptionStatusChanged, (publication, _status, participant) => {
            if (publication?.source === Track.Source.ScreenShare) {
                ensureRemoteScreenShareSubscribed(publication, participant);
            }
        })
        .on(RoomEvent.TrackSubscriptionPermissionChanged, (publication, _status, participant) => {
            if (publication?.source === Track.Source.ScreenShare) {
                ensureRemoteScreenShareSubscribed(publication, participant);
            }
        })
        .on(RoomEvent.ConnectionStateChanged, state => {
            updateNetworkHud();
            if (state === ConnectionState.Connected) {
                startPingMonitor();
                scheduleScreenShareSubscriptionRetries();
                electDocumentHost();
                updateDocumentPermissionUi();
                reconnecting = false;
                setStatus('Connected as ' + nameInput.value.trim(), 'good');
                renderAllParticipants();
            } else if (state === ConnectionState.SignalReconnecting || state === ConnectionState.Reconnecting) {
                reconnecting = true;
                setStatus('Connection interrupted. Reconnecting...');
            } else if (state === ConnectionState.Disconnected && !leaving) {
                stopPingMonitor();
                screenShareRetryTimers.forEach(timer => clearTimeout(timer));
                screenShareRetryTimers.clear();
                reconnecting = false;
                setStatus('Disconnected from the meeting. Press Join to reconnect.', 'error');
                controls.hidden = true;
                chatToggle.hidden = true;
                setChatOpen(false);
                joinButton.hidden = false;
                joinButton.disabled = false;
                nameInput.disabled = false;
                room = null;
                clearMedia();
            }
        })
        .on(RoomEvent.Reconnecting, () => {
            reconnecting = true;
            setStatus('Connection interrupted. Reconnecting...');
        })
        .on(RoomEvent.Reconnected, () => {
            reconnecting = false;
            startPingMonitor();
            scheduleScreenShareSubscriptionRetries();
            setStatus('Connection restored.', 'good');
            renderAllParticipants();
            updateNetworkHud();
            updateShareButton();
        })
        .on(RoomEvent.ParticipantMetadataChanged, (_metadata, participant) => {
            if (participant) renderParticipant(participant);
        })
        .on(RoomEvent.ParticipantNameChanged, (_name, participant) => {
            if (participant) renderParticipant(participant);
        })
        .on(RoomEvent.TrackStreamStateChanged, (publication, _streamState, participant) => {
            if (publication?.source === Track.Source.ScreenShare && typeof publication.setEnabled === 'function') {
                publication.setEnabled(true);
            }
            if (participant) renderParticipantVideo(participant);
        })
        .on(RoomEvent.Disconnected, reason => {
            if (!leaving) console.warn('LiveKit disconnected:', reason);
        });
}

async function connectRoom(serverUrl, token, relayOnly = true) {
    if (!room) throw new Error('LiveKit room is not initialized.');

    const connectionOptions = {
        autoSubscribe: true,
        maxRetries: 0,
        websocketTimeout: 7000,
        peerConnectionTimeout: relayOnly ? 8000 : 5000,
    };

    if (relayOnly) {
        connectionOptions.rtcConfig = {
            iceTransportPolicy: 'relay',
        };
    }

    await room.connect(serverUrl, token, connectionOptions);
}

async function runConnectionDiagnostics(serverUrl, token) {
    try {
        const checker = new ConnectionCheck(serverUrl, token, {
            errorsAsWarnings: true,
        });

        await Promise.allSettled([
            checker.checkWebsocket(),
            checker.checkWebRTC(),
            checker.checkTURN(),
        ]);

        const results = checker.getResults();
        console.warn('LiveKit connection diagnostics:', results);
        await checker.dispose();
    } catch (error) {
        console.warn('LiveKit connection diagnostics failed:', error);
    }
}

async function join() {
    if (joining || room) return;

    const now = Date.now();
    if (now - lastJoinAttempt < 1200) return;
    lastJoinAttempt = now;

    const name = nameInput.value.trim();
    if (!name) {
        nameInput.focus();
        setStatus('Enter your name first.', 'error');
        return;
    }

    joining = true;
    leaving = false;
    joinButton.disabled = true;
    setStatus('Preparing connection...');

    let connectionData = null;

    try {
        if (!isBrowserSupported()) {
            throw new Error('This browser does not support the media features required for the meeting.');
        }

        room = roomOptions();
        setupRoomEvents();

        // Fetch the token first, then pre-warm the exact LiveKit Cloud edge.
        // The old flow pre-warmed without a token and then repeated the work.
        const data = await fetchToken(name);
        connectionData = data;

        setStatus('Connecting to realtime media...');
        await room.prepareConnection(data.server_url, data.participant_token);

        let connectionError = null;

        // Direct ICE has timed out repeatedly in this deployment. Prefer TURN
        // so restrictive/mobile networks do not wait through failed ICE checks.
        try {
            await connectRoom(data.server_url, data.participant_token, true);
        } catch (error) {
            connectionError = error;
            console.warn('TURN connection failed; retrying direct WebRTC:', error);

            const failedRoom = room;
            room = null;
            await failedRoom?.disconnect().catch(() => {});

            room = roomOptions();
            setupRoomEvents();

            setStatus('Relay connection failed. Trying direct media...');
            await room.prepareConnection(data.server_url, data.participant_token);
            await connectRoom(data.server_url, data.participant_token, false);
        }

        if (connectionError) {
            console.info('Direct WebRTC fallback succeeded after TURN failed.');
        }

        nameInput.disabled = true;
        joinButton.hidden = true;
        controls.hidden = false;
        chatToggle.hidden = false;

        renderAllParticipants();
        electDocumentHost();
        updateDocumentPermissionUi();
        setStatus('Connected. Starting microphone. Camera is off by default.', 'good');

        await room.localParticipant.setMicrophoneEnabled(true, {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
        });

        // Never enable the camera as part of joining. Users explicitly turn it
        // on with the Camera button after entering the meeting.
        await room.localParticipant.setCameraEnabled(false);

        updateButtons();
        updateShareButton();
        updateNetworkHud();
        startPingMonitor();
        scheduleScreenShareSubscriptionRetries();
        renderParticipantVideo(room.localParticipant);

        const mic = room.localParticipant.getTrackPublication(Track.Source.Microphone);
        const camera = room.localParticipant.getTrackPublication(Track.Source.Camera);

        if (!mic) {
            setStatus('Connected. Microphone is unavailable. Check browser permissions.', 'error');
        } else {
            setStatus('Connected as ' + name, 'good');
        }
    } catch (error) {
        console.error('Join failed:', error);

        if (connectionData?.server_url && connectionData?.participant_token) {
            runConnectionDiagnostics(connectionData.server_url, connectionData.participant_token);
        }

        if (connectTimeout) {
            clearTimeout(connectTimeout);
            connectTimeout = null;
        }

        const failedRoom = room;
        room = null;
        clearMedia();
        openWhiteboard(false);
        openSharedDocument(false);

        if (failedRoom) {
            await failedRoom.disconnect().catch(() => {});
        }

        controls.hidden = true;
        chatToggle.hidden = true;
        setChatOpen(false);
        joinButton.hidden = false;
        joinButton.disabled = false;
        nameInput.disabled = false;

        setStatus(error?.message || 'Could not join the meeting.', 'error');
    } finally {
        joining = false;
    }
}

async function toggleMicrophone() {
    if (!room || leaving || room.state !== ConnectionState.Connected) return;

    micButton.disabled = true;

    try {
        const publication = room.localParticipant.getTrackPublication(Track.Source.Microphone);
        const enabled = !!publication && !publication.isMuted && publication.isEnabled !== false;

        await room.localParticipant.setMicrophoneEnabled(!enabled, {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
        });

        updateButtons();
    } catch (error) {
        console.error('Microphone toggle failed:', error);
        setStatus('Could not change microphone state.', 'error');
    } finally {
        micButton.disabled = false;
    }
}

async function toggleCamera() {
    if (!room || leaving || room.state !== ConnectionState.Connected) return;

    cameraButton.disabled = true;

    try {
        const publication = room.localParticipant.getTrackPublication(Track.Source.Camera);
        const enabled = !!publication && !publication.isMuted && publication.isEnabled !== false;

        await room.localParticipant.setCameraEnabled(!enabled);
        renderParticipantVideo(room.localParticipant);
        updateButtons();
        updateShareButton();
    } catch (error) {
        console.error('Camera toggle failed:', error);
        setStatus('Could not change camera state.', 'error');
    } finally {
        cameraButton.disabled = false;
    }
}

async function toggleScreenShare() {
    if (!room || leaving || room.state !== ConnectionState.Connected) return;

    screenButton.disabled = true;

    try {
        const publication = room.localParticipant.getTrackPublication(Track.Source.ScreenShare);
        const sharing = !!publication && !publication.isMuted && publication.isEnabled !== false;

        if (sharing) {
            await room.localParticipant.setScreenShareEnabled(false);
        } else {
            const published = await room.localParticipant.setScreenShareEnabled(true, {
                audio: false,
                contentHint: 'detail',
                resolution: isMobile()
                    ? ScreenSharePresets.h720fps15.resolution
                    : ScreenSharePresets.h1080fps15.resolution,
                selfBrowserSurface: 'include',
                surfaceSwitching: 'include',
            }, {
                source: Track.Source.ScreenShare,
                simulcast: false,
                videoCodec: 'vp8',
                backupCodec: { codec: 'h264' },
                degradationPreference: 'maintain-resolution',
                screenShareEncoding: {
                    maxFramerate: 15,
                    maxBitrate: isMobile() ? 1800000 : 3500000,
                },
            });

            if (!published?.track) {
                throw new Error('LiveKit did not publish a screen-share track.');
            }
        }

        renderParticipantVideo(room.localParticipant);
        updateButtons();
        updateShareButton();
    } catch (error) {
        console.error('Screen share toggle failed:', error);

        if (error?.name === 'NotAllowedError' || /cancel|denied/i.test(error?.message || '')) {
            setStatus('Screen sharing was cancelled.', 'error');
        } else {
            setStatus('Could not start screen sharing. Check browser support and permissions.', 'error');
        }
    } finally {
        screenButton.disabled = false;
    }
}

async function leave() {
    if (leaving) return;

    leaving = true;
    leaveButton.disabled = true;

    if (connectTimeout) {
        clearTimeout(connectTimeout);
        connectTimeout = null;
    }

    stopPingMonitor();
    screenShareRetryTimers.forEach(timer => clearTimeout(timer));
    screenShareRetryTimers.clear();

    if (room) {
        const oldRoom = room;
        room = null;
        await oldRoom.disconnect().catch(() => {});
    }

    clearMedia();
    openWhiteboard(false);
    openSharedDocument(false);
    chatToggle.hidden = true;
    setChatOpen(false);
    location.href = '/';
}

joinButton.addEventListener('click', join);
chatToggle.addEventListener('click', () => setChatOpen(!chatOpen));
chatClose.addEventListener('click', () => setChatOpen(false));
chatForm.addEventListener('submit', sendChatMessage);
micButton.addEventListener('click', toggleMicrophone);
cameraButton.addEventListener('click', toggleCamera);
screenButton.addEventListener('click', toggleScreenShare);
leaveButton.addEventListener('click', leave);
whiteboardToggle.addEventListener('click', () => openWhiteboard(true));
whiteboardClose.addEventListener('click', () => openWhiteboard(false));

documentToggle.addEventListener('click', () => openSharedDocument(true));
documentClose.addEventListener('click', () => openSharedDocument(false));
documentAccessToggle.addEventListener('click', () => {
    if (!isDocumentHost()) return;
    documentAccess.hidden = !documentAccess.hidden;
    updateDocumentPermissionUi();
});
let savedDocumentSelection = null;

function saveDocumentSelection() {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) return;

    const range = selection.getRangeAt(0);
    if (documentEditor.contains(range.commonAncestorContainer)) {
        savedDocumentSelection = range.cloneRange();
    }
}

function restoreDocumentSelection() {
    if (!savedDocumentSelection) return;

    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(savedDocumentSelection);
}

function applyDocumentCommand(command, value = null) {
    if (!documentCanEdit) return;

    restoreDocumentSelection();
    documentEditor.focus();
    restoreDocumentSelection();

    if (['fontName', 'fontSize', 'foreColor', 'hiliteColor'].includes(command)) {
        document.execCommand('styleWithCSS', false, true);
    }

    if (command === 'formatBlock' && typeof value === 'string') {
        value = value.toLowerCase();
        if (!['p', 'h1', 'h2', 'h3', 'blockquote'].includes(value)) return;
        value = '<' + value + '>';
    }

    try {
        if (typeof document.queryCommandSupported === 'function' &&
            !document.queryCommandSupported(command)) {
            return;
        }

        document.execCommand(command, false, value);
        saveDocumentSelection();
        scheduleDocumentSync();
    } catch (error) {
        console.warn('Document command failed:', command, error);
    }
}

documentEditor.addEventListener('input', scheduleDocumentSync);
documentEditor.addEventListener('keyup', saveDocumentSelection);
documentEditor.addEventListener('mouseup', saveDocumentSelection);
documentEditor.addEventListener('blur', saveDocumentSelection);
documentEditor.addEventListener('paste', () => {
    setTimeout(() => {
        saveDocumentSelection();
        scheduleDocumentSync();
    }, 0);
});
document.addEventListener('selectionchange', saveDocumentSelection);

document.querySelectorAll('[data-doc-command]').forEach(control => {
    control.addEventListener('mousedown', () => {
        if (documentCanEdit) saveDocumentSelection();
    });

    const eventName = control instanceof HTMLSelectElement ? 'change' : 'click';
    control.addEventListener(eventName, () => {
        let value = control.dataset.docValue || null;

        if (control instanceof HTMLInputElement && control.type === 'color') {
            value = control.value;
        } else if (control instanceof HTMLSelectElement) {
            value = control.value;
        }

        applyDocumentCommand(control.dataset.docCommand, value);
    });
});
documentDownload.addEventListener('click', downloadSharedDocument);

copyLinkButton.addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(location.href);
        copyLinkButton.textContent = 'Copied';
        setTimeout(() => copyLinkButton.textContent = 'Copy link', 1400);
    } catch {
        setStatus('Copy failed. Copy the address from your browser.', 'error');
    }
});
presentationClose.addEventListener('click', closePresentation);
presentationFullscreen.addEventListener('click', fullscreenPresentation);
whiteboardPen.addEventListener('click', () => {
    whiteboardMode = 'pen';
    whiteboardPen.classList.add('active');
    whiteboardEraser.classList.remove('active');
});
whiteboardEraser.addEventListener('click', () => {
    whiteboardMode = 'eraser';
    whiteboardEraser.classList.add('active');
    whiteboardPen.classList.remove('active');
});
whiteboardZoomIn.addEventListener('click', () => setWhiteboardZoom(whiteboardZoom + .25));
whiteboardZoomOut.addEventListener('click', () => setWhiteboardZoom(whiteboardZoom - .25));
whiteboardReset.addEventListener('click', () => {
    whiteboardZoom = 1;
    whiteboardOffsetX = 0;
    whiteboardOffsetY = 0;
    setWhiteboardZoom(1);
});
whiteboardClear.addEventListener('click', async () => {
    whiteboardStrokes = [];
    whiteboardLiveStrokes.clear();
    redrawWhiteboard();
    await publishWhiteboard({ type: 'clear' }, true);
});
whiteboardCanvas.addEventListener('pointerdown', beginWhiteboardStroke);
whiteboardCanvas.addEventListener('pointermove', moveWhiteboardStroke);
whiteboardCanvas.addEventListener('pointerup', endWhiteboardStroke);
whiteboardCanvas.addEventListener('pointercancel', endWhiteboardStroke);
window.addEventListener('resize', () => {
    if (whiteboardOpen) resizeWhiteboardCanvas();
});

nameInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        join();
    }
});

document.addEventListener('pointerdown', unlockAudio, { passive: true });

window.addEventListener('pagehide', () => {
    room?.disconnect();
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden && room?.state === ConnectionState.Connected) {
        renderAllParticipants();
        startPingMonitor();
        scheduleScreenShareSubscriptionRetries();
    }
});

window.addEventListener('online', () => {
    if (room?.state === ConnectionState.Connected) {
        setStatus('Network restored. Checking media...', 'good');
        renderAllParticipants();
    } else if (!room && !joining) {
        setStatus('Internet connection restored. Ready to join.', 'good');
    }
});

window.addEventListener('offline', () => {
    if (room) setStatus('Internet connection lost. Waiting for network...', 'error');
});

document.addEventListener('fullscreenchange', () => {
    presentationFullscreen.textContent = document.fullscreenElement ? 'Exit fullscreen' : 'Fullscreen';
});
</script>
</body>
</html>
