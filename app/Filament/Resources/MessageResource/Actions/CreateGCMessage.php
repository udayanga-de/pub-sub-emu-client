<?php

namespace App\Filament\Resources\MessageResource\Actions;

use App\Helpers\PubSubHelper;
use Google\Cloud\PubSub\MessageBuilder;

class CreateGCMessage
{
    public function create($topic, $message)
    {
        if (is_null($topic)) {
            return false;
        }

        $client = PubSubHelper::fromProject($topic->project);

        try {

            $gcTopic = $client->topic($topic->topic);
            $gcTopic->publish((new MessageBuilder)->setData($message)->build());

            return true;
        } catch (\Exception $e) {

        }

        return false;

    }
}
