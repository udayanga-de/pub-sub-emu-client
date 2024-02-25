<?php

namespace App\Filament\Resources\MessageResource\Actions;

use App\Helpers\PubSubHelper;
use App\Models\Message;
use App\Models\Subscription;

class PullMessages
{
    public function pull($subscription)
    {
        if (is_null($subscription)) {
            return false;
        }

        try {
            $messages = PubSubHelper::fromProjectId($subscription->project_id)->subscription($subscription->subscription)->pull();
        } catch (\Exception $e) {
            $messages = [];
        }

        $savedMessages = Message::where('subscription_id', $subscription->id)
            ->whereNotNull('raw_id')
            ->pluck('raw_id')
            ->toArray();

        $pulledMessages = [];

        foreach ($messages as $message) {
            if (in_array($message->id(), $savedMessages)) {
                continue;
            }

            $pulledMessages[$message->id()] = $message->data();
        }

        return $pulledMessages;
    }

    public function pullById($subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);

        return $this->pull($subscription);
    }
}
