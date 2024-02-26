<?php

namespace App\Filament\Resources\TopicResource\Actions;

use App\Helpers\PubSubHelper;
use App\Models\Topic;

class SyncTopics
{
    public function sync($project)
    {
        $topics = [];
        try {
            $topics = PubSubHelper::fromProject($project)->topics();
        } catch (\Exception $e) {

        }

        $savedTopicIds = Topic::where('project_id', $project->id)->pluck('id', 'topic')->toArray();
        $savedTopics = array_keys($savedTopicIds);

        // Process new and existing topics
        $existingTopics = [];
        foreach ($topics as $topic) {
            $parts = explode('/', $topic->name());
            $gcTopic = array_pop($parts);

            if (! in_array($gcTopic, $savedTopics)) {
                // If topic doesn't exist, create a new one
                $topic = Topic::create([
                    'project_id' => $project->id,
                    'topic' => $gcTopic,
                    'status' => 1,
                ]);
                $existingTopics[] = $topic->id;
            } else {
                $existingTopics[] = $savedTopicIds[$gcTopic];
            }
        }

        // Update status for existing topics
        Topic::where('project_id', $project->id)
            ->whereIn('id', array_keys($existingTopics))
            ->update(['status' => 1]);

        // Update status for topics not found in the retrieved list
        Topic::where('project_id', $project->id)
            ->whereNotIn('id', array_keys($existingTopics))
            ->update(['status' => 0]);

        return true;

    }
}
