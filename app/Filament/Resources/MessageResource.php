<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use App\Models\Topic;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'iconpark-messagesent-o';

    protected static ?int $navigationSort = 4;

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
                TextInput::make('message')->label('Message')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('message')->label('Message'),
                TextColumn::make('subscription.subscription')->label('Subscription'),
                TextColumn::make('topic.topic')->label('Topic'),
                CheckboxColumn::make('sync')->label('Sync')->disabled(),
                CheckboxColumn::make('ack')->label('Acknowledged')->disabled(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $subscriptionId = request('subscription');

        if (empty($subscriptionId)) {
            return parent::getEloquentQuery()->whereNotNull('subscription_id');
        }

        return parent::getEloquentQuery()->where('subscription_id', $subscriptionId);
    }
}
