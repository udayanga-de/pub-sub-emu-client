<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)->schema([
                    TextInput::make('project_id')->label('Project ID')->required(),
                    Toggle::make('is_local')->label('Runs In Local'),
                    TextInput::make('emulator_host')->label('Emulator Host'),
                    TextInput::make('emulator_port')->label('Emulator Port')->maxLength(5),
                    TextInput::make('service_key')->label('Service Key Path'),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project_id')->label('Project ID'),
                ToggleColumn::make('is_local')->label('Runs In Local')->disabled(),
                TextColumn::make('emulator_host')->label('Emulator Host'),
                TextColumn::make('emulator_port')->label('Emulator Port'),
                ToggleColumn::make('status')->label('Status')->disabled(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Topics')
                    ->color('success')
                    ->icon('iconpark-topicdiscussion')
                    ->url(fn (Project $project): string => TopicResource::getUrl('index').'?project='.$project->id),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
