<?php

namespace App\Filament\Resources\MessageResource\Actions;

use App\Helpers\PubSubHelper;
use App\Models\Message;
use Illuminate\Support\Carbon;

class SyncMessages
{
    public function syncOnly($subscription)
    {
        return $this->sync($subscription);
    }

    public function syncAndAcknowledge($subscription)
    {
        return $this->sync($subscription, true);
    }

    public function sync($subscription, $witAcknowledge = false)
    {
        if (is_null($subscription)) {
            return false;
        }

        $messages = [];
        $googleSubscription = null;
        try {
            $googleSubscription = PubSubHelper::fromProjectId($subscription->project_id)->subscription($subscription->subscription);
            $messages = $googleSubscription->pull();
        } catch (\Exception $e) {

        }

        $savedMessages = Message::where('subscription_id', $subscription->id)
            ->whereNotNull('raw_id')
            ->get()
            ->keyBy('raw_id');

        foreach ($messages as $message) {
            $acknowledged = false;
            if ($witAcknowledge) {
                try {
                    $googleSubscription->acknowledge($message);
                    $acknowledged = true;
                } catch (\Exception $e) {
                    // Handle exception if needed
                }
            }

            if ($savedMessage = $savedMessages->get($message->id())) {
                if ($acknowledged) {
                    $savedMessage->update([
                        'ack' => 1,
                        'ack_at' => now(),
                    ]);
                }
            } else {
                $newMessage = new Message([
                    'project_id' => $subscription->project_id,
                    'topic_id' => $subscription->topic_id,
                    'subscription_id' => $subscription->id,
                    'sync' => 1,
                    'message' => $message->data(),
                    'raw_message' => json_encode($message->info()),
                    'raw_id' => $message->id(),
                    'ack_id' => $message->ackId(),
                    'ack' => $acknowledged ? 1 : 0,
                    'ack_at' => $acknowledged ? Carbon::now() : null,
                ]);
                $newMessage->save();
            }
        }

        return true;
    }
}
