<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meeting {{ $room }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #0b0b0b; color: #fff; font-family: system-ui, sans-serif; }
        header { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; padding: 14px 18px; }
        #grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; padding: 10px 10px 100px; }
        .tile { position: relative; min-height: 220px; background: #181818; border-radius: 12px; overflow: hidden; display: grid; place-items: center; }
        .tile video { width: 100%; height: 100%; min-height: 220px; object-fit: cover; }
        .tile audio { display: none; }
        .name { position: absolute; z-index: 2; left: 10px; bottom: 10px; background: #000a; padding: 5px 8px; border-radius: 6px; }
        .status { width: 100%; color: #aaa; font-size: 14px; }
        .error { color: #ff7777; }
        .controls { position: fixed; bottom: 18px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; padding: 10px; background: #1b1b1bcc; border-radius: 14px; }
        button, input { border: 0; border-radius: 8px; padding: 10px 12px; }
        button { cursor: pointer; font-weight: 700; }
        button:disabled { cursor: wait; opacity: .6; }
        #join { background: #fff; color: #111; }
        #leave { background: #d33; color: #fff; }
    </style>
</head>
<body>
<header>
    <strong>Room: {{ $room }}</strong>
    <input id="name" placeholder="Your name" maxlength="80" autocomplete="name">
    <button id="join">Join</button>
    <div id="status" class="status">Enter your name and join the room.</div>
</header>
<div id="grid"></div>
<div class="controls" hidden>
    <button id="mic">Mute</button>
    <button id="camera">Camera off</button>
    <button id="leave">Leave</button>
</div>

<script type="module">
import { Room, RoomEvent } from 'https://cdn.jsdelivr.net/npm/livekit-client@2.17.0/+esm';

const roomName = @json($room);
const grid = document.getElementById('grid');
const nameInput = document.getElementById('name');
const joinButton = document.getElementById('join');
const status = document.getElementById('status');
const controls = document.querySelector('.controls');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let room = null;

function setStatus(message, error = false) {
    status.textContent = message;
    status.classList.toggle('error', error);
}

function tileFor(participant) {
    let tile = document.querySelector(`[data-identity="${CSS.escape(participant.identity)}"]`);
    if (!tile) {
        tile = document.createElement('div');
        tile.className = 'tile';
        tile.dataset.identity = participant.identity;
        tile.innerHTML = '<span class="name"></span>';
        grid.appendChild(tile);
    }
    tile.querySelector('.name').textContent = participant.name || participant.identity;
    return tile;
}

function attachTrack(track, participant) {
    if (!track) return;
    const tile = tileFor(participant);
    const element = track.attach();
    element.dataset.trackSid = track.sid || '';
    element.dataset.kind = track.kind;

    if (track.kind === 'video') {
        tile.querySelectorAll('video').forEach(el => el.remove());
        tile.insertBefore(element, tile.querySelector('.name'));
    } else {
        tile.querySelectorAll('audio').forEach(el => el.remove());
        tile.appendChild(element);
    }
}

function detachTrack(track) {
    if (!track) return;
    track.detach().forEach(element => element.remove());
}

function removeParticipant(participant) {
    participant.tracks?.forEach(publication => publication.track && detachTrack(publication.track));
    document.querySelector(`[data-identity="${CSS.escape(participant.identity)}"]`)?.remove();
}

async function join() {
    const name = nameInput.value.trim();
    if (!name) {
        nameInput.focus();
        setStatus('Enter your name first.', true);
        return;
    }

    joinButton.disabled = true;
    setStatus('Getting meeting token...');

    try {
        const response = await fetch(`/meetings/${encodeURIComponent(roomName)}/token`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name })
        });

        if (!response.ok) {
            const body = await response.text();
            throw new Error(`Token request failed (${response.status}): ${body}`);
        }

        const data = await response.json();
        if (!data.server_url || !data.participant_token) {
            throw new Error('Laravel returned an invalid LiveKit token response.');
        }

        setStatus('Connecting to local LiveKit server...');
        room = new Room();

        room.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            attachTrack(track, participant);
        });
        room.on(RoomEvent.TrackUnsubscribed, (track) => detachTrack(track));
        room.on(RoomEvent.ParticipantDisconnected, removeParticipant);
        room.on(RoomEvent.Disconnected, () => setStatus('Disconnected from the meeting.'));

        await room.connect(data.server_url, data.participant_token);
        setStatus('Connected. Requesting camera and microphone...');

        await room.localParticipant.enableCameraAndMicrophone();

        tileFor(room.localParticipant);
        room.localParticipant.trackPublications.forEach(publication => {
            if (publication.track) attachTrack(publication.track, room.localParticipant);
        });

        room.remoteParticipants.forEach(participant => {
            tileFor(participant);
            participant.trackPublications.forEach(publication => {
                if (publication.track) attachTrack(publication.track, participant);
            });
        });

        controls.hidden = false;
        nameInput.disabled = true;
        joinButton.hidden = true;
        setStatus(`Connected as ${name}`);
    } catch (error) {
        console.error(error);
        setStatus(error?.message || 'Could not join the meeting. Check the browser console.', true);
        joinButton.disabled = false;
        if (room) {
            await room.disconnect().catch(() => {});
            room = null;
        }
    }
}

joinButton.addEventListener('click', join);
nameInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') join();
});

document.getElementById('mic').onclick = async (event) => {
    if (!room) return;
    const enabled = room.localParticipant.isMicrophoneEnabled;
    await room.localParticipant.setMicrophoneEnabled(!enabled);
    event.currentTarget.textContent = enabled ? 'Unmute' : 'Mute';
};

document.getElementById('camera').onclick = async (event) => {
    if (!room) return;
    const enabled = room.localParticipant.isCameraEnabled;
    await room.localParticipant.setCameraEnabled(!enabled);
    event.currentTarget.textContent = enabled ? 'Camera on' : 'Camera off';
};

document.getElementById('leave').onclick = async () => {
    if (room) await room.disconnect();
    location.href = '/';
};
</script>
</body>
</html>
