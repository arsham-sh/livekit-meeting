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
            ->setRoomName($room)
            ->setRoomJoin()
            ->setCanPublish(true)
            ->setCanSubscribe(true)
            ->setCanPublishData(true);

        $token = new AccessToken(
            config('livekit.api_key'),
            config('livekit.api_secret'),
            $options,
        );

        $token->addGrant($grant);

        return $token->getToken();
    }
}
