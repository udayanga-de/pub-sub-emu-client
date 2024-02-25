<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Filament\Resources\MessageResource\Actions\PullMessages;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\SubscriptionResource;
use App\Filament\Resources\TopicResource;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    public ?Subscription $subscription;

    public function __construct()
    {

        $subId = request('subscription');
        if (isset($subId)) {
            $this->subscription = Subscription::find($subId);
        } else {
            $this->subscription = null;
        }
    }

    protected function getHeaderActions(): array
    {
        $subId = request('subscription');
        $topicId = request('topic');

        $syncWithGC = Action::make('syncFromGC')
            ->label('Sync')
            ->color('info')
            ->icon('iconpark-refresh-o')
            ->action(function (array $arguments) {
                if ((new MessageResource\Actions\SyncMessages())->syncOnly($arguments['subscription'])) {
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
                $this->redirect(MessageResource::getUrl('index').'?subscription='.$arguments['subscription']->id);
            })->arguments([
                'subscription' => $this->subscription,
            ])
            ->disabled(! $this->subscription);

        $syncAndAckGC = Action::make('syncAndAckGC')
            ->label('Sync and Ack')
            ->color('success')
            ->icon('iconpark-refreshone')
            ->action(function (array $arguments) {
                if ((new MessageResource\Actions\SyncMessages())->syncAndAcknowledge($arguments['subscription'])) {
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
                $this->redirect(MessageResource::getUrl('index').'?subscription='.$arguments['subscription']->id);
            })->arguments([
                'subscription' => $this->subscription,
            ])
            ->disabled(! $this->subscription);

        $pull = Action::make('pullMessages')
            ->label('Pull Messages')
            ->color('danger')
            ->icon('iconpark-eyes-o')
            ->form([
                Placeholder::make('Messages')
                    ->content(function () {
                        $messages = (new PullMessages())->pull($this->subscription);
                        if (count($messages)) {
                            $tableData = '<table>'.
                                '<tr><th>Message ID</th>'.
                                '<th>Message Data</th></tr>';
                            foreach ($messages as $key => $message) {
                                $tableData .= '<tr>';
                                $tableData .= '<td>'.$key.'</td>';
                                $tableData .= '<td>'.$message.'</td>';
                                $tableData .= '</tr>';
                            }
                            $tableData .= '</table>';

                            return new HtmlString($tableData);
                        }

                        return ' No messages found';
                    }),

            ])
            ->action(function (array $arguments) {
                return true;

            })->arguments([
                'subscription' => $this->subscription,
            ])
            ->disabled(! $this->subscription)
            ->modalSubmitActionLabel('Done');

        return [
            CreateAction::make()
                ->url(fn (): string => MessageResource::getUrl('create').'?topic='.$topicId)
                ->label('New')
                ->icon('iconpark-addone-o'),
            $syncAndAckGC,
            $syncWithGC,
            $pull,
        ];
    }

    public function getBreadcrumbs(): array
    {
        if ($this->subscription) {
            $subscription = $this->subscription;
            $topic = $subscription->topic;

            return [
                ProjectResource::getUrl() => 'Projects',
                '#' => $topic->project->project_id,
                TopicResource::getUrl('index').'?project='.$topic->project_id => 'Topics',
                SubscriptionResource::getUrl('index').'?topic='.$topic->id => $topic->topic,
                SubscriptionResource::getUrl('index').'?topic='.$topic->id.'&list=true' => 'Subscriptions',
                MessageResource::getUrl('index').'?subscription='.$subscription->id => $subscription->subscription,
                MessageResource::getUrl('index').'?subscription='.$subscription->id.'&list=true' => 'Messages',
            ];
        } else {
            return parent::getBreadcrumbs();
        }
    }
}
