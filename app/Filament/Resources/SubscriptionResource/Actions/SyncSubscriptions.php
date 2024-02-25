<?php

namespace App\Filament\Resources\SubscriptionResource\Actions;

use App\Helpers\PubSubHelper;
use App\Models\Subscription;

class SyncSubscriptions
{
    public function sync($topic)
    {
        if (is_null($topic)) {
            return false;
        }

        $subscriptions = [];
        try {
            $subscriptions = PubSubHelper::fromProjectId($topic->project_id)->subscriptions();
        } catch (\Exception $e) {

        }

        $gcSubs = [];

        foreach ($subscriptions as $sub) {
            $subNameParts = explode('/', $sub->name());
            $gcSub = array_pop($subNameParts);

            $topicNameParts = explode('/', $sub->info()['topic']);
            $gcTopic = array_pop($topicNameParts);

            if ($gcTopic == $topic->topic) {
                $gcSubs[$gcSub] = $gcSub;
            }
        }

        $savedSubs = Subscription::where('topic_id', $topic->id)->pluck('subscription', 'id')->toArray();

        $syncSubscriptions = [];
        $unsyncSubscriptions = [];
        foreach ($savedSubs as $id => $savedSub) {
            if (array_key_exists($savedSub, $gcSubs)) {
                $syncSubscriptions[] = $id;
                unset($gcSubs[$savedSub]);
            } else {
                $unsyncSubscriptions[] = $id;
            }
        }

        $newSubs = [];

        foreach ($gcSubs as $gcSub) {
            $newSubs[] = [
                'project_id' => $topic->project_id,
                'topic_id' => $topic->id,
                'subscription' => $gcSub,
                'status' => 1,
            ];
        }

        if (! empty($gcSubs)) {
            Subscription::insert($newSubs);
        }

        Subscription::where('topic_id', $topic->id)->whereIn('id', $syncSubscriptions)->update(['status' => 1]);

        Subscription::where('topic_id', $topic->id)->whereIn('id', $unsyncSubscriptions)->update(['status' => 0]);

        return true;
    }
}
