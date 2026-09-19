<?php

$url = env('LIVEKIT_URL');
$apiKey = env('LIVEKIT_API_KEY');
$apiSecret = env('LIVEKIT_API_SECRET');

if (env('APP_ENV', 'production') === 'production' && (!$url || !$apiKey || !$apiSecret)) {
    throw new RuntimeException(
        'LiveKit production configuration is incomplete. Set LIVEKIT_URL, LIVEKIT_API_KEY and LIVEKIT_API_SECRET.'
    );
}

return [
    'url' => $url,
    'api_key' => $apiKey,
    'api_secret' => $apiSecret,
    'token_ttl' => (int) env('LIVEKIT_TOKEN_TTL', 3600),
];
