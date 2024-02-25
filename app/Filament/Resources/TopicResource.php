<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Actions\DeleteGCTopic;
use App\Filament\Resources\TopicResource\Pages;
use App\Helpers\PubSubHelper;
use App\Models\Project;
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

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'iconpark-topicdiscussion';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $projects = Project::all();

        $projectId = request('project');

        return $form
            ->schema([
                Select::make('project_id')
                    ->label(__('Project'))
                    ->options($projects->pluck('project_id', 'id'))
                    ->default($projectId)
                    ->disabled(isset($projectId))
                    ->required(),
                TextInput::make('topic')->label('Topic')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('topic')->label('Topic')->disabled(),
                TextColumn::make('project.project_id')->label('Project ID'),
                ToggleColumn::make('status')->label('Status')->disabled(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\Action::make(' Message')
                    ->icon('iconpark-send')
                    ->color('info')
                    ->url(fn (Topic $topic): string => MessageResource::getUrl('create').'?topic='.$topic->id),

                Tables\Actions\Action::make('Subscriptions')
                    ->icon('iconpark-usertousertransmission-o')
                    ->color('success')
                    ->url(fn (Topic $topic): string => SubscriptionResource::getUrl('index').'?topic='.$topic->id),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Topic $topic) {
                        PubSubHelper::fromProjectId($topic->project_id)->deleteTopic($topic);
                        $topic->delete();
                        redirect(TopicResource::getUrl('index').'?project='.$topic->project_id);
                    }),

            ])
            ->bulkActions([

            ]);
    }

    public static function getPages(): array
    {

        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $projectId = request('project');

        if (empty($projectId)) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('project_id', $projectId);
    }
}
