<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Helpers\PubSubHelper;
use App\Models\Topic;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $topic = Topic::find($data['topic_id']);
        PubSubHelper::fromProjectId($topic->project_id)->publish($topic, $data['message']);
        $data['project_id'] = $topic->project_id;
        $data['topic_id'] = $topic->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return MessageResource::getUrl('index').'?topic='.$this->record->topic_id;
    }
}
