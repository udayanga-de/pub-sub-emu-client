<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Actions\SyncProjects;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        $syncWithGC = Action::make('syncWithGC')
            ->label('Sync')
            ->color('info')
            ->icon('iconpark-refresh-o')

            ->action(function () {
                if ((new SyncProjects())->sync()) {
                    Notification::make()
                        ->title('Sync with GC successfully')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error occurred during the environment sync with GC')
                        ->danger()
                        ->send();
                }
            });

        return [
            Actions\CreateAction::make()
                ->label('New')
                ->icon('iconpark-addone-o'),
            $syncWithGC,
        ];
    }
}
