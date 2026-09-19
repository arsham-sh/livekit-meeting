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
            grid-template-columns: auto minmax(150px, 240px) auto;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            padding-top: max(10px, env(safe-area-inset-top));
            border-bottom: 1px solid #1f1f23;
            background: #0d0d0fee;
            backdrop-filter: blur(12px);
        }
        .room {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 800;
        }
        .status {
            grid-column: 1 / -1;
            min-height: 18px;
            color: var(--muted);
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status.error { color: #f87171; }
        .status.good { color: #86efac; }

        #grid {
            position: fixed;
            inset: 91px 0 0;
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
        .tile:hover { border-color: #3a3a44; }
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
            width: 68px;
            height: 68px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #29292f;
            color: #fff;
            font-size: 26px;
            font-weight: 800;
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
            header {
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 7px;
                padding: 8px 10px;
                padding-top: max(8px, env(safe-area-inset-top));
            }
            .room { grid-column: 1 / -1; }
            header input { min-width: 0; width: 100%; }
            .status { font-size: 12px; }
            #grid {
                inset: 94px 0 0;
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
                border-radius: 10px;
            }
            .avatar { width: 48px; height: 48px; font-size: 19px; }
            .name { left: 6px; bottom: 6px; max-width: calc(100% - 12px); padding: 4px 6px; font-size: 11px; }
            .badge { right: 6px; top: 6px; padding: 4px 6px; font-size: 10px; }
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
            .quality { display: none; }
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

        @media (max-width: 420px) {
            #grid {
                grid-template-columns: 1fr;
            }
            #grid.compact,
            #grid.dense {
                grid-template-columns: 1fr;
            }
            .tile { aspect-ratio: 16 / 9; }
        }
    </style>
</head>
<body>
<header>
    <div class="room">Room: {{ $room }}</div>
    <input id="name" placeholder="Your name" maxlength="80" autocomplete="name">
    <button id="join">Join</button>
    <div id="status" class="status">Enter your name and join the room.</div>
</header>

<div id="grid"></div>
<div id="audio-root" aria-hidden="true"></div>

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
        <button id="whiteboard-close" type="button">Close</button>
    </div>
    <canvas id="whiteboard-canvas" tabindex="0"></canvas>
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
    <button id="copy-link" type="button">Copy link</button>
    <button id="fullscreen" type="button">Fullscreen</button>
    <button id="leave">Leave</button>
    <span id="participant-count" class="quality">1 participant</span>
    <span id="quality" class="quality"></span>
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
const fullscreenButton = document.getElementById('fullscreen');
const participantCount = document.getElementById('participant-count');
const quality = document.getElementById('quality');
const chatToggle = document.getElementById('chat-toggle');
const chatUnread = document.getElementById('chat-unread');
const chatPanel = document.getElementById('chat-panel');
const chatClose = document.getElementById('chat-close');
const chatMessages = document.getElementById('chat-messages');
const chatEmpty = document.getElementById('chat-empty');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
const chatSend = document.getElementById('chat-send');
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

const mediaElements = new Map();

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

function initials(name) {
    return (name || '?')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(part => part[0]?.toUpperCase() || '')
        .join('') || '?';
}

function participantTile(participant) {
    const selector = '[data-identity="' + CSS.escape(participant.identity) + '"]';
    let tile = grid.querySelector(selector);

    if (!tile) {
        tile = document.createElement('div');
        tile.className = 'tile';
        tile.dataset.identity = participant.identity;

        const avatar = document.createElement('div');
        avatar.className = 'avatar';

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
    tile.querySelector('.avatar').textContent = initials(displayName);
    attachTileZoomGesture(tile, participant);

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
    participantCount.textContent = count + (count === 1 ? ' participant' : ' participants');
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
}

function currentVideoPublication(participant) {
    const screen = participant.getTrackPublication(Track.Source.ScreenShare);
    if (screen?.track) return screen;

    const camera = participant.getTrackPublication(Track.Source.Camera);
    if (camera?.track) return camera;

    return null;
}

function removeVideoForParticipant(participant) {
    const tile = participantTile(participant);
    tile.querySelectorAll('video').forEach(element => element.remove());

    for (const [sid, element] of mediaElements.entries()) {
        if (element.tagName === 'VIDEO' && element.dataset.identity === participant.identity) {
            element.remove();
            mediaElements.delete(sid);
        }
    }

    tile.classList.remove('screen-share');
}

function renderParticipantVideo(participant) {
    const publication = currentVideoPublication(participant);
    const tile = participantTile(participant);

    if (!publication?.track) {
        removeVideoForParticipant(participant);
        updateGridDensity();
        return;
    }

    const track = publication.track;
    const sid = publication.trackSid || track.sid;
    if (!sid) return;

    const source = publication.source || track.source || Track.Source.Camera;
    const existing = tile.querySelector('video');

    if (existing?.dataset.trackSid === sid) {
        tile.classList.toggle('screen-share', source === Track.Source.ScreenShare);
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
    element.muted = participant === room?.localParticipant;
    element.dataset.trackSid = sid;
    element.dataset.identity = participant.identity;
    element.dataset.source = source;

    tile.insertBefore(element, tile.querySelector('.avatar'));
    tile.classList.toggle('screen-share', source === Track.Source.ScreenShare);
    mediaElements.set(sid, element);
    applyParticipantZoom(participant);
    updateGridDensity();
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

    room.remoteParticipants.forEach(renderParticipant);
    updateGridDensity();
    updateButtons();
}

function clearMedia() {
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
    screenButton.textContent = screenOn ? 'Stop sharing' : 'Share screen';

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

function redrawWhiteboard() {
    const ctx = whiteboardCanvas.getContext('2d');
    const rect = whiteboardCanvas.getBoundingClientRect();
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, whiteboardCanvas.width, whiteboardCanvas.height);
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    whiteboardStrokes.forEach(drawStroke);
}

async function publishWhiteboard(payload) {
    if (!room || room.state !== ConnectionState.Connected) return;
    try {
        await room.localParticipant.publishData(
            new TextEncoder().encode(JSON.stringify(payload)),
            { reliable: true, topic: 'whiteboard' },
        );
    } catch (error) {
        console.warn('Whiteboard sync failed:', error);
    }
}

function setWhiteboardZoom(next) {
    whiteboardZoom = Math.min(2.5, Math.max(.5, next));
    whiteboardZoomLabel.textContent = Math.round(whiteboardZoom * 100) + '%';
    redrawWhiteboard();
}

function openWhiteboard(open = true) {
    whiteboardOpen = open;
    whiteboard.hidden = !open;
    if (open) {
        requestAnimationFrame(() => {
            resizeWhiteboardCanvas();
            whiteboardCanvas.focus();
        });
    }
}

function handleWhiteboardData(payload, participant, topic) {
    if (topic !== 'whiteboard' || !participant) return;
    try {
        const message = JSON.parse(new TextDecoder().decode(payload));
        if (message.type === 'stroke' && Array.isArray(message.stroke?.points)) {
            whiteboardStrokes.push(message.stroke);
            if (whiteboardOpen) drawStroke(message.stroke);
        } else if (message.type === 'clear') {
            whiteboardStrokes = [];
            if (whiteboardOpen) redrawWhiteboard();
        }
    } catch (error) {
        console.warn('Ignoring invalid whiteboard message:', error);
    }
}

function beginWhiteboardStroke(event) {
    if (!whiteboardOpen || event.button !== 0) return;
    whiteboardDrawing = {
        points: [whiteboardPoint(event)],
        color: whiteboardColor.value,
        size: Number(whiteboardSize.value),
        mode: whiteboardMode,
    };
    whiteboardCanvas.setPointerCapture(event.pointerId);
    event.preventDefault();
}

function moveWhiteboardStroke(event) {
    if (!whiteboardDrawing) return;
    whiteboardDrawing.points.push(whiteboardPoint(event));
    redrawWhiteboard();
    drawStroke(whiteboardDrawing);
    event.preventDefault();
}

async function endWhiteboardStroke(event) {
    if (!whiteboardDrawing) return;
    const stroke = whiteboardDrawing;
    whiteboardDrawing = false;
    whiteboardCanvas.releasePointerCapture?.(event.pointerId);
    whiteboardStrokes.push(stroke);
    redrawWhiteboard();
    await publishWhiteboard({ type: 'stroke', stroke });
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
        adaptiveStream: true,
        dynacast: true,
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

function setupRoomEvents() {
    room
        .on(RoomEvent.DataReceived, (payload, participant, _kind, topic) => {
            handleChatData(payload, participant, topic);
            handleWhiteboardData(payload, participant, topic);
        })
        .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            attachTrack(track, participant, publication);
            renderParticipantVideo(participant);
        })
        .on(RoomEvent.TrackUnsubscribed, track => {
            detachTrack(track);
        })
        .on(RoomEvent.TrackPublished, (publication, participant) => {
            participantTile(participant);
            updateBadge(participant);
        })
        .on(RoomEvent.TrackUnpublished, (publication, participant) => {
            if (publication.track) detachTrack(publication.track);
            if (participant) renderParticipantVideo(participant);
            if (participant) updateBadge(participant);
        })
        .on(RoomEvent.ParticipantConnected, participant => {
            renderParticipant(participant);
            updateGridDensity();
        })
        .on(RoomEvent.ParticipantDisconnected, removeParticipant)
        .on(RoomEvent.LocalTrackPublished, publication => {
            if (publication.track) attachTrack(publication.track, room.localParticipant, publication);
            renderParticipantVideo(room.localParticipant);
            updateButtons();
        })
        .on(RoomEvent.LocalTrackUnpublished, publication => {
            if (publication.track) detachTrack(publication.track);
            renderParticipantVideo(room.localParticipant);
            updateButtons();
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
        .on(RoomEvent.ConnectionQualityChanged, (connectionQuality, participant) => {
            if (participant === room?.localParticipant) {
                quality.textContent = 'Connection: ' + connectionQuality;
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
        .on(RoomEvent.TrackSubscriptionFailed, (trackSid, participant, reason) => {
            console.warn('Track subscription failed:', trackSid, participant?.identity, reason);
            if (participant) {
                setStatus('A participant video could not be loaded. Reconnecting media...', 'error');
            }
        })
        .on(RoomEvent.ConnectionStateChanged, state => {
            if (state === ConnectionState.Connected) {
                reconnecting = false;
                setStatus('Connected as ' + nameInput.value.trim(), 'good');
                renderAllParticipants();
            } else if (state === ConnectionState.SignalReconnecting || state === ConnectionState.Reconnecting) {
                reconnecting = true;
                setStatus('Connection interrupted. Reconnecting...');
            } else if (state === ConnectionState.Disconnected && !leaving) {
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
            setStatus('Connection restored.', 'good');
            renderAllParticipants();
        })
        .on(RoomEvent.ParticipantMetadataChanged, (_metadata, participant) => {
            if (participant) renderParticipant(participant);
        })
        .on(RoomEvent.ParticipantNameChanged, (_name, participant) => {
            if (participant) renderParticipant(participant);
        })
        .on(RoomEvent.TrackStreamStateChanged, (publication, _streamState, participant) => {
            if (participant) renderParticipantVideo(participant);
        })
        .on(RoomEvent.Disconnected, reason => {
            if (!leaving) console.warn('LiveKit disconnected:', reason);
        });
}

async function connectRoom(serverUrl, token, relayOnly = false) {
    if (!room) throw new Error('LiveKit room is not initialized.');

    const connectionOptions = {
        autoSubscribe: true,
        maxRetries: 0,
        websocketTimeout: 10000,
        peerConnectionTimeout: relayOnly ? 12000 : 7000,
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

    try {
        if (!isBrowserSupported()) {
            throw new Error('This browser does not support the media features required for the meeting.');
        }

        room = roomOptions();
        setupRoomEvents();

        // Pre-warm DNS/TLS and request the token in parallel. Waiting for the
        // first pre-warm before starting the token request only adds latency.
        const prewarm = room.prepareConnection(livekitUrl).catch(error => {
            console.warn('LiveKit pre-warm failed:', error);
        });
        const tokenPromise = fetchToken(name);

        const data = await tokenPromise;

        setStatus('Preparing the realtime connection...');
        await Promise.allSettled([prewarm]);

        // With LiveKit Cloud, signal/WebSocket connectivity can succeed while
        // WebRTC ICE fails. Try the normal path first, then immediately retry
        // through LiveKit's TURN relay instead of waiting through slow region
        // retries. This is especially important on restrictive/mobile networks.
        await room.prepareConnection(data.server_url, data.participant_token);

        let connectionError = null;

        try {
            await connectRoom(data.server_url, data.participant_token, false);
        } catch (error) {
            connectionError = error;
            console.warn('Direct WebRTC connection failed; retrying through TURN:', error);

            const failedRoom = room;
            room = null;
            await failedRoom?.disconnect().catch(() => {});

            room = roomOptions();
            setupRoomEvents();

            setStatus('Network is restricting direct media. Switching to secure relay...');

            await room.prepareConnection(data.server_url, data.participant_token);
            await connectRoom(data.server_url, data.participant_token, true);
        }

        if (connectionError) {
            console.info('TURN fallback succeeded after direct connection failure.');
        }

        connectTimeout = setTimeout(() => {
            if (room && room.state !== ConnectionState.Connected) {
                room.disconnect().catch(() => {});
            }
        }, 20000);

        clearTimeout(connectTimeout);
        connectTimeout = null;

        nameInput.disabled = true;
        joinButton.hidden = true;
        controls.hidden = false;
        chatToggle.hidden = false;

        renderAllParticipants();
        setStatus('Connected. Starting camera and microphone...', 'good');

        await Promise.allSettled([
            room.localParticipant.setMicrophoneEnabled(true, {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
            }),
            room.localParticipant.setCameraEnabled(true),
        ]);

        updateButtons();
        renderParticipantVideo(room.localParticipant);

        const mic = room.localParticipant.getTrackPublication(Track.Source.Microphone);
        const camera = room.localParticipant.getTrackPublication(Track.Source.Camera);

        if (!mic && !camera) {
            setStatus('Connected, but camera and microphone could not be started. Check permissions.', 'error');
        } else if (!mic) {
            setStatus('Connected. Microphone is unavailable.', 'error');
        } else if (!camera) {
            setStatus('Connected. Camera is unavailable.', 'error');
        } else {
            setStatus('Connected as ' + name, 'good');
        }
    } catch (error) {
        console.error('Join failed:', error);

        if (room && typeof data !== 'undefined' && data?.server_url && data?.participant_token) {
            await runConnectionDiagnostics(data.server_url, data.participant_token);
        }

        if (connectTimeout) {
            clearTimeout(connectTimeout);
            connectTimeout = null;
        }

        const failedRoom = room;
        room = null;
        clearMedia();
        openWhiteboard(false);

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
            await room.localParticipant.setScreenShareEnabled(true, {
                audio: false,
                contentHint: 'detail',
                resolution: isMobile()
                    ? ScreenSharePresets.h720fps15.resolution
                    : ScreenSharePresets.h1080fps15.resolution,
                selfBrowserSurface: 'exclude',
                surfaceSwitching: 'include',
            });
        }

        renderParticipantVideo(room.localParticipant);
        updateButtons();
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

    if (room) {
        const oldRoom = room;
        room = null;
        await oldRoom.disconnect().catch(() => {});
    }

    clearMedia();
    openWhiteboard(false);
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
copyLinkButton.addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(location.href);
        copyLinkButton.textContent = 'Copied';
        setTimeout(() => copyLinkButton.textContent = 'Copy link', 1400);
    } catch {
        setStatus('Copy failed. Copy the address from your browser.', 'error');
    }
});
fullscreenButton.addEventListener('click', async () => {
    try {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        } else if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
        }
    } catch (error) {
        console.warn('Fullscreen failed:', error);
    }
});
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
    redrawWhiteboard();
    await publishWhiteboard({ type: 'clear' });
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
    fullscreenButton.textContent = document.fullscreenElement ? 'Exit fullscreen' : 'Fullscreen';
});
</script>
</body>
</html>
