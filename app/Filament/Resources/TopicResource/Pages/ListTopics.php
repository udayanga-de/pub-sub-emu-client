<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TopicResource;
use App\Filament\Resources\TopicResource\Actions\SyncTopics;
use App\Models\Project;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTopics extends ListRecords
{
    protected static string $resource = TopicResource::class;

    public ?Project $project;

    public function __construct()
    {

        $projectId = request('project');
        if (isset($projectId)) {
            $this->project = Project::find($projectId);
        } else {
            $this->project = null;
        }
    }

    protected function getActions(): array
    {

        $projectId = request('project');

        $syncWithGC = Action::make('syncFromGC')
            ->label('Sync')
            ->color('info')
            ->icon('iconpark-refresh-o')
            ->action(function (array $arguments) {
                if ((new SyncTopics())->sync($arguments['project'])) {
                    Notification::make()
                        ->title('Sync from GC successfully')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error occurred during the environment sync from GC')
                        ->danger()
                        ->send();
                }
                $this->redirect(TopicResource::getUrl('index').'?project='.$arguments['project']->id);
            })->arguments([
                'project' => $this->project,
            ])->disabled(! $this->project);

        return [
            Actions\CreateAction::make()
                ->url(fn (): string => TopicResource::getUrl('create').'?project='.$projectId)
                ->label('New')
                ->icon('iconpark-addone-o'),
            $syncWithGC,
        ];
    }

    public function getBreadcrumbs(): array
    {
        if ($this->project) {
            return [
                ProjectResource::getUrl() => 'Projects',
                '#' => $this->project->project_id,
                TopicResource::getUrl('index').'?project='.$this->project->id => 'Topics',
            ];
        } else {
            return parent::getBreadcrumbs();
        }
    }
}
