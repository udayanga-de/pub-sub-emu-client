<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\SubscriptionResource\Actions\CreateGCSubscription;
use App\Helpers\PubSubHelper;
use App\Models\Topic;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $topic = Topic::find($data['topic_id']);

        $topicCreated = PubSubHelper::fromProjectId($topic->project_id)->createSubscription($topic, $data['subscription']);
        $data['status'] = $topicCreated ? 1 : 0;
        $data['project_id'] = $topic->project_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return SubscriptionResource::getUrl('index').'?topic='.$this->record->topic_id;
    }
}
