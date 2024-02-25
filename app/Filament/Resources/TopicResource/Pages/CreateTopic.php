<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TopicResource;
use App\Helpers\PubSubHelper;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;

class CreateTopic extends CreateRecord
{
    protected static string $resource = TopicResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $project = Project::find($data['project_id']);

        $topicCreated =PubSubHelper::fromProject($project)->createTopic( $data['topic']);
        $data['status'] = $topicCreated ? 1 : 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return TopicResource::getUrl('index').'?project='.$this->record->project_id;
    }

    public function getBreadcrumbs(): array
    {
        $projectId = request('project', null);

        if (! is_null($projectId)) {
            $project = Project::find($projectId);

            return [
                ProjectResource::getUrl() => 'Projects',
                '#' => $project->project_id,
                TopicResource::getUrl('index').'?project='.$project->id => 'Topics',
                $this->getBreadcrumb(),
            ];

        } else {
            return parent::getBreadcrumbs();
        }
    }
}
