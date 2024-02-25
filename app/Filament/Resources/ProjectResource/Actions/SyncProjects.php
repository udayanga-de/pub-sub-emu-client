<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Helpers\PubSubHelper;
use App\Models\Project;

class SyncProjects
{
    public function sync()
    {

        $projects = Project::all();

        foreach ($projects as $project) {
            $isLive = false;

            try {
                PubSubHelper::fromProject($project)->topics();
                $isLive = true;
            } catch (\Exception $e) {
                // Handle exception if needed, or simply ignore
            }

            $project->status = $isLive ? 1 : 0;
            $project->save();
        }

        return true;

    }
}
