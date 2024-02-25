<?php

namespace App\Helpers;

use App\Models\Project;
use Google\Cloud\PubSub\PubSubClient;

class PubSubHelper
{
    protected Project|null $project;
    protected PubSubClient $client;

    protected function __construct($project,$client)
    {
        $this->project = $project;
        $this->client = $client;
    }


    public static function fromProject($project, $config = []): PubSubHelper
    {

        $defaultConfig = [
            'projectId' => $project->project_id,
            // 'serviceDefinitionPath' => $project->service_account_key_file ?? env('GOOGLE_CLOUD_KEY_FILE'),
        ];

        $config = array_merge($defaultConfig, $config);

        $emulatorHost = getenv('PUBSUB_EMULATOR_HOST');

        if ($project->is_local && ! empty($project->emulator_host)) {
            putenv('PUBSUB_EMULATOR_HOST='.$project->emulator_host.':'.$project->emulator_port);
        }

        $client= new PubSubClient($config);
        putenv('PUBSUB_EMULATOR_HOST='.$emulatorHost);

       return new self($project, $client);


    }

    public static function fromProjectId($projectId, $config = [])
    {

        $project = Project::find($projectId);
        if (! $project) {
            throw new \Exception('Project not found');
        }

        return self::fromProject($project, $config);
    }

    #region Topics
    public function topics(){
        return $this->client->topics();
    }

    public function topic($topic){
        return $this->client->topic($topic);
    }
    public function createTopic($topic)
    {
        try {
           $this->client->createTopic($topic);
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }
    public function deleteTopic($topic){

        try {
            $gcTopic = $this->client->topic($topic->topic);
            $gcTopic->delete();
            return true;
        } catch (\Exception $e) {

        }
        return false;
    }
    #endregion


    #region Subscriptions
    public function subscriptions(){
        return $this->client->subscriptions();
    }

    public function subscription($subscription){
        return $this->client->subscription($subscription);
    }
    public function createSubscription($topic, $subscription)
    {
        try {
            $gcSub =   $this->topic($topic->topic)->subscription($subscription)->create();
            return true;
        } catch (\Exception $e) {

        }
        return false;

    }

    public function deleteSubscription($subscription)
    {
        try {
            $this->client->subscription($subscription->subscription)->delete();
            return true;
        } catch (\Exception $e) {

        }

        return false;

    }
    #endregion
}
