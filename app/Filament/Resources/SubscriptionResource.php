<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Actions\DeleteGCSubscription;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Helpers\PubSubHelper;
use App\Models\Subscription;
use App\Models\Topic;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'iconpark-usertousertransmission-o';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $topics = Topic::all();

        $topicId = request('topic');

        return $form
            ->schema([
                Select::make('topic_id')
                    ->label(__('Topic'))
                    ->options($topics->pluck('topic', 'id'))
                    ->default($topicId)
                    ->disabled(isset($topicId))
                    ->required(),
                TextInput::make('subscription')->label('Subscription')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('subscription')->label('Subscription'),
                TextColumn::make('topic.topic')->label('Topic'),
                ToggleColumn::make('status')->label('Status')->disabled(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Messages')
                    ->icon('iconpark-messagesent-o')
                    ->color('success')
                    ->url(fn (Subscription $sub): string => MessageResource::getUrl('index').'?subscription='.$sub->id.'&topic='.$sub->topic_id),

                Tables\Actions\DeleteAction::make()
                    ->action(function (Subscription $subscription) {
                        PubSubHelper::fromProjectId($subscription->project_id)->deleteSubscription($subscription);
                        $subscription->delete();
                        redirect(SubscriptionResource::getUrl('index').'?topic='.$subscription->topic_id);
                    }),
            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $topicId = request('topic');

        if (empty($topicId)) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('topic_id', $topicId);
    }
}
