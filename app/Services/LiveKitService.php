<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitService
{
    public function token(string $room, string $identity, string $name): string
    {
        $apiKey = config('livekit.api_key');
        $apiSecret = config('livekit.api_secret');

        if (!is_string($apiKey) || $apiKey === '' || !is_string($apiSecret) || $apiSecret === '') {
            throw new \RuntimeException('LiveKit credentials are not configured.');
        }

        $ttl = max(60, min(86400, (int) config('livekit.token_ttl', 3600)));

        $options = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setName($name)
            ->setTtl($ttl);

        $grant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($room)
            ->setCanPublish(true)
            ->setCanSubscribe(true);

        return (new AccessToken(config('livekit.api_key'), config('livekit.api_secret')))
            ->init($options)
            ->setGrant($grant)
            ->toJwt();
    }
}
