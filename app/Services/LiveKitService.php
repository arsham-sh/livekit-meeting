<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitService
{
    public function token(string $room, string $identity, string $name): string
    {
        $options = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setName($name)
            ->setTtl(3600);

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
