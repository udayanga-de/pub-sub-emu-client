<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\SubscriptionResource\Actions\SyncSubscriptions;
use App\Filament\Resources\TopicResource;
use App\Models\Topic;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    public ?Topic $topic;

    public function __construct()
    {

        $topicId = request('topic');
        if (isset($topicId)) {
            $this->topic = Topic::find($topicId);
        } else {
            $this->topic = null;
        }
    }

    protected function getHeaderActions(): array
    {

        $topicId = request('topic');

        $syncWithGC = Action::make('syncFromGC')
            ->label('Sync')
            ->color('info')
            ->icon('iconpark-refresh-o')
            ->action(function (array $arguments) {
                if ((new SyncSubscriptions())->sync($arguments['topic'])) {
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
                $this->redirect(SubscriptionResource::getUrl('index').'?topic='.$arguments['topic']->id);
            })->arguments([
                'topic' => $this->topic,
            ])
            ->disabled(! $this->topic);

        return [
            Actions\CreateAction::make()
                ->url(fn (): string => SubscriptionResource::getUrl('create').'?topic='.$topicId)
                ->label('New')
                ->icon('iconpark-addone-o'),
            $syncWithGC,
        ];

    }

    public function getBreadcrumbs(): array
    {
        if ($this->topic) {
            return [
                ProjectResource::getUrl() => 'Projects',
                '#' => $this->topic->project->project_id,
                TopicResource::getUrl('index').'?project='.$this->topic->project_id => 'Topics',
                SubscriptionResource::getUrl('index').'?topic='.$this->topic->id => $this->topic->topic,
                SubscriptionResource::getUrl('index').'?topic='.$this->topic->id.'&list=true' => 'Subscriptions',
            ];
        } else {
            return parent::getBreadcrumbs();
        }
    }
}
