<?php

namespace App\Http\Controllers;

use App\Services\LiveKitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    
    public function create(): View
    {
        return view('meetings.create');
    }

    public function store(): mixed
    {
        $room = Str::lower(Str::random(12));

        return redirect()->route('meetings.show', ['room' => $room]);
    }

    public function show(string $room): View
    {
        return view('meetings.show', [
            'room' => $room,
            'livekitUrl' => config('livekit.url'),
        ]);
    }

    public function token(Request $request, string $room, LiveKitService $liveKit): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:80'],
        ]);

        $livekitUrl = config('livekit.url');

        if (!is_string($livekitUrl) || $livekitUrl === '') {
            abort(503, 'Realtime service is not configured.');
        }

        $identity = (string) Str::uuid();

        return response()->json([
            'server_url' => config('livekit.url'),
            'participant_token' => $liveKit->token($room, $identity, $data['name']),
        ]);
    }
}
