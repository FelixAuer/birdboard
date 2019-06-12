<?php

namespace Tests\Setup;

class ProjectFactory
{
    protected $tasksCount = 0;
    protected $user;

    public function withTasks($count)
    {
        $this->tasksCount = $count;
        return $this;
    }

    public function ownedBy($user)
    {
        $this->user = $user;
        return $this;
    }

    public function create()
    {
        $project = factory('App\Project')->create([
            'owner_id' => $this->user ?? factory('App\User')
        ]);

        factory('App\Task', $this->tasksCount)->create([
            'project_id' => $project->id
        ]);

        return $project;
    }
}
