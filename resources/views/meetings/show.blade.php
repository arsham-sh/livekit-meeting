<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meeting {{ $room }}</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #09090b;
            --panel: #151518;
            --panel-2: #1d1d22;
            --text: #f5f5f5;
            --muted: #a1a1aa;
            --danger: #ef4444;
            --accent: #fafafa;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        button, input { font: inherit; }

        button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 700;
            background: var(--panel-2);
            color: var(--text);
        }

        button:hover { filter: brightness(1.12); }
        button:disabled { cursor: wait; opacity: .55; }

        input {
            width: min(240px, 60vw);
            border: 1px solid #2b2b31;
            border-radius: 10px;
            padding: 10px 12px;
            background: var(--panel);
            color: var(--text);
            outline: none;
        }

        input:focus { border-color: #666; }

        header {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 14px 16px;
            border-bottom: 1px solid #1f1f23;
            background: #0d0d0f;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .room {
            font-weight: 800;
            margin-right: auto;
        }

        .status {
            width: 100%;
            color: var(--muted);
            font-size: 14px;
        }

        .status.error { color: #f87171; }
        .status.good { color: #86efac; }

        #grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 10px;
            padding: 12px 12px 110px;
            min-height: calc(100vh - 75px);
        }

        .tile {
            position: relative;
            min-height: 220px;
            aspect-ratio: 16 / 10;
            background: var(--panel);
            border-radius: 14px;
            overflow: hidden;
            display: grid;
            place-items: center;
            border: 1px solid #222228;
        }

        .tile video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #050505;
        }

        .avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #29292f;
            color: #fff;
            font-size: 28px;
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
            z-index: 30;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #17171bcc;
            border: 1px solid #303038;
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        #leave { background: var(--danger); color: #fff; }
        #join { background: var(--accent); color: #111; }

        .quality {
            font-size: 12px;
            color: var(--muted);
            margin-left: 4px;
            align-self: center;
        }

        @media (max-width: 640px) {
            header { padding: 10px; }
            .room { width: 100%; }
            input { width: 100%; }
            #grid {
                grid-template-columns: 1fr;
                padding: 8px 8px 100px;
            }
            .tile { min-height: 210px; }
            .controls {
                bottom: 10px;
                width: calc(100% - 20px);
                justify-content: center;
            }
            .controls button { flex: 1; }
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

<div class="controls" hidden>
    <button id="mic">Mute</button>
    <button id="camera">Camera off</button>
    <button id="leave">Leave</button>
    <span id="quality" class="quality"></span>
</div>

<script type="module">
import {
    Room,
    RoomEvent,
    ConnectionState,
} from 'https://cdn.jsdelivr.net/npm/livekit-client@2.17.0/+esm';

const roomName = @json($room);
const grid = document.getElementById('grid');
const audioRoot = document.getElementById('audio-root');
const nameInput = document.getElementById('name');
const joinButton = document.getElementById('join');
const status = document.getElementById('status');
const controls = document.querySelector('.controls');
const micButton = document.getElementById('mic');
const cameraButton = document.getElementById('camera');
const leaveButton = document.getElementById('leave');
const quality = document.getElementById('quality');
const csrf = document.querySelector('meta[name="csrf-token"]').content;

let room = null;
let joining = false;
let leaving = false;
let reconnectTimer = null;

function setStatus(message, type = '') {
    status.textContent = message;
    status.classList.toggle('error', type === 'error');
    status.classList.toggle('good', type === 'good');
}

function initials(name) {
    return (name || '?')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(part => part[0]?.toUpperCase() || '')
        .join('') || '?';
}

function tileFor(participant) {
    const selector = `[data-identity="${CSS.escape(participant.identity)}"]`;
    let tile = grid.querySelector(selector);

    if (!tile) {
        tile = document.createElement('div');
        tile.className = 'tile';
        tile.dataset.identity = participant.identity;

        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        avatar.textContent = initials(participant.name || participant.identity);

        const name = document.createElement('span');
        name.className = 'name';

        const badge = document.createElement('span');
        badge.className = 'badge';

        tile.append(avatar, name, badge);
        grid.appendChild(tile);
    }

    tile.querySelector('.name').textContent = participant.name || participant.identity;
    tile.querySelector('.avatar').textContent = initials(participant.name || participant.identity);

    return tile;
}

function updateBadge(participant) {
    const tile = tileFor(participant);
    const badge = tile.querySelector('.badge');
    const mic = participant.isMicrophoneEnabled;
    const camera = participant.isCameraEnabled;

    const parts = [];
    if (!mic) parts.push('Muted');
    if (!camera) parts.push('Camera off');

    badge.textContent = parts.join(' · ');
    badge.hidden = parts.length === 0;
}

function removeParticipant(participant) {
    participant.trackPublications.forEach(publication => {
        if (publication.track) detachTrack(publication.track);
    });

    grid.querySelector(`[data-identity="${CSS.escape(participant.identity)}"]`)?.remove();
}

function detachTrack(track) {
    if (!track) return;

    track.detach().forEach(element => {
        element.remove();
    });
}

function attachVideoTrack(track, participant) {
    const tile = tileFor(participant);

    tile.querySelectorAll('video').forEach(video => video.remove());

    const avatar = tile.querySelector('.avatar');
    const element = track.attach();

    element.autoplay = true;
    element.playsInline = true;
    element.muted = participant === room?.localParticipant;
    element.dataset.trackSid = track.sid;

    tile.insertBefore(element, avatar);
}

function attachRemoteAudio(track, participant) {
    // Never attach the local microphone. Playing our own mic back is a
    // direct route to echo and is not useful in a meeting.
    if (participant === room?.localParticipant) return;

    const element = track.attach();
    element.autoplay = true;
    element.playsInline = true;
    element.dataset.trackSid = track.sid;

    audioRoot.appendChild(element);

    element.play().catch(() => {
        setStatus('Connected. Click the page once to enable remote audio.');
    });
}

function attachTrack(track, participant) {
    if (!track) return;

    if (track.kind === 'video') {
        attachVideoTrack(track, participant);
    } else if (track.kind === 'audio') {
        attachRemoteAudio(track, participant);
    }

    updateBadge(participant);
}

function renderParticipant(participant) {
    tileFor(participant);
    participant.trackPublications.forEach(publication => {
        if (publication.track) attachTrack(publication.track, participant);
    });
    updateBadge(participant);
}

function clearMedia() {
    grid.replaceChildren();
    audioRoot.replaceChildren();
}

function cleanupRoom() {
    if (!room) return;

    room.localParticipant.trackPublications.forEach(publication => {
        if (publication.track) detachTrack(publication.track);
    });

    room.remoteParticipants.forEach(participant => {
        participant.trackPublications.forEach(publication => {
            if (publication.track) detachTrack(publication.track);
        });
    });

    clearMedia();
}

function setupRoomEvents() {
    room
        .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            attachTrack(track, participant);
        })
        .on(RoomEvent.TrackUnsubscribed, track => {
            detachTrack(track);
        })
        .on(RoomEvent.ParticipantConnected, participant => {
            renderParticipant(participant);
        })
        .on(RoomEvent.ParticipantDisconnected, participant => {
            removeParticipant(participant);
        })
        .on(RoomEvent.LocalTrackPublished, publication => {
            if (publication.track) {
                attachTrack(publication.track, room.localParticipant);
            }
            updateBadge(room.localParticipant);
        })
        .on(RoomEvent.LocalTrackUnpublished, publication => {
            if (publication.track) detachTrack(publication.track);
            updateBadge(room.localParticipant);
        })
        .on(RoomEvent.TrackMuted, (publication, participant) => {
            updateBadge(participant);
        })
        .on(RoomEvent.TrackUnmuted, (publication, participant) => {
            updateBadge(participant);
        })
        .on(RoomEvent.ActiveSpeakersChanged, speakers => {
            document.querySelectorAll('.tile').forEach(tile => {
                tile.style.outline = '';
            });

            speakers.forEach(participant => {
                const tile = grid.querySelector(
                    `[data-identity="${CSS.escape(participant.identity)}"]`
                );
                if (tile) tile.style.outline = '2px solid #fff';
            });
        })
        .on(RoomEvent.ConnectionStateChanged, state => {
            if (state === ConnectionState.Connected) {
                setStatus(`Connected as ${nameInput.value.trim()}`, 'good');
            } else if (state === ConnectionState.Reconnecting) {
                setStatus('Connection interrupted. Reconnecting...');
            } else if (state === ConnectionState.Disconnected && !leaving) {
                setStatus('Disconnected from the meeting.', 'error');
                controls.hidden = true;
                joinButton.hidden = false;
                joinButton.disabled = false;
            }
        })
        .on(RoomEvent.Reconnecting, () => {
            setStatus('Connection interrupted. Reconnecting...');
        })
        .on(RoomEvent.Reconnected, () => {
            setStatus('Connection restored.', 'good');
            renderAllParticipants();
        })
        .on(RoomEvent.Disconnected, reason => {
            if (!leaving) {
                console.warn('LiveKit disconnected:', reason);
                setStatus('Disconnected from the meeting.', 'error');
            }
        });
}

function renderAllParticipants() {
    if (!room) return;

    renderParticipant(room.localParticipant);

    room.remoteParticipants.forEach(participant => {
        renderParticipant(participant);
    });

    updateButtons();
}

function updateButtons() {
    if (!room) return;

    const micEnabled = room.localParticipant.isMicrophoneEnabled;
    const cameraEnabled = room.localParticipant.isCameraEnabled;

    micButton.textContent = micEnabled ? 'Mute' : 'Unmute';
    cameraButton.textContent = cameraEnabled ? 'Camera off' : 'Camera on';
    updateBadge(room.localParticipant);
}

async function requestMedia() {
    let microphoneError = null;
    let cameraError = null;

    try {
        await room.localParticipant.setMicrophoneEnabled(true);
    } catch (error) {
        microphoneError = error;
        console.warn('Microphone unavailable:', error);
    }

    try {
        await room.localParticipant.setCameraEnabled(true);
    } catch (error) {
        cameraError = error;
        console.warn('Camera unavailable:', error);
    }

    updateButtons();

    if (microphoneError && cameraError) {
        setStatus('Connected, but camera and microphone are unavailable. Check browser permissions.', 'error');
    } else if (microphoneError) {
        setStatus('Connected. Microphone is unavailable. Camera is active.', 'error');
    } else if (cameraError) {
        setStatus('Connected. Camera is unavailable. Microphone is active.', 'error');
    } else {
        setStatus(`Connected as ${nameInput.value.trim()}`, 'good');
    }
}

async function fetchToken(name) {
    const response = await fetch(`/meetings/${encodeURIComponent(roomName)}/token`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name }),
    });

    if (!response.ok) {
        const body = await response.text();
        throw new Error(`Token request failed (${response.status}): ${body}`);
    }

    const data = await response.json();

    if (!data.server_url || !data.participant_token) {
        throw new Error('Laravel returned an invalid LiveKit token response.');
    }

    return data;
}

async function join() {
    if (joining || room) return;

    const name = nameInput.value.trim();

    if (!name) {
        nameInput.focus();
        setStatus('Enter your name first.', 'error');
        return;
    }

    joining = true;
    joinButton.disabled = true;
    setStatus('Preparing your meeting...');

    try {
        const data = await fetchToken(name);

        setStatus('Connecting to meeting...');

        room = new Room({
            adaptiveStream: true,
            dynacast: true,
        });

        setupRoomEvents();

        await room.connect(data.server_url, data.participant_token, {
            autoSubscribe: true,
        });

        nameInput.disabled = true;
        joinButton.hidden = true;
        controls.hidden = false;

        renderAllParticipants();
        await requestMedia();
    } catch (error) {
        console.error('Join failed:', error);

        cleanupRoom();

        if (room) {
            await room.disconnect().catch(() => {});
        }

        room = null;
        controls.hidden = true;
        joinButton.hidden = false;
        joinButton.disabled = false;

        const message = error?.message || 'Could not join the meeting.';
        setStatus(message, 'error');
    } finally {
        joining = false;
    }
}

async function toggleMicrophone() {
    if (!room) return;

    micButton.disabled = true;

    try {
        await room.localParticipant.setMicrophoneEnabled(
            !room.localParticipant.isMicrophoneEnabled
        );
        updateButtons();
    } catch (error) {
        console.error('Microphone toggle failed:', error);
        setStatus('Could not change microphone state.', 'error');
    } finally {
        micButton.disabled = false;
    }
}

async function toggleCamera() {
    if (!room) return;

    cameraButton.disabled = true;

    try {
        await room.localParticipant.setCameraEnabled(
            !room.localParticipant.isCameraEnabled
        );
        updateButtons();
    } catch (error) {
        console.error('Camera toggle failed:', error);
        setStatus('Could not change camera state.', 'error');
    } finally {
        cameraButton.disabled = false;
    }
}

async function leave() {
    if (leaving) return;

    leaving = true;
    leaveButton.disabled = true;

    if (room) {
        cleanupRoom();
        await room.disconnect().catch(() => {});
        room = null;
    }

    location.href = '/';
}

joinButton.addEventListener('click', join);

nameInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        join();
    }
});

micButton.addEventListener('click', toggleMicrophone);
cameraButton.addEventListener('click', toggleCamera);
leaveButton.addEventListener('click', leave);

window.addEventListener('pagehide', () => {
    if (room) {
        room.disconnect();
    }
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden && room?.state === ConnectionState.Connected) {
        renderAllParticipants();
    }
});
</script>
</body>
</html>
