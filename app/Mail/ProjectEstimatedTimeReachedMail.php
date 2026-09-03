<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ProjectEstimatedTimeReachedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;

    public User $user;

    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->markdown('emails.project-estimated-time-reached', [
            'projectName' => $this->project->name,
            'projectUrl' => URL::route('projects.show', ['project' => $this->project->getKey()]),
        ])
            ->subject(__('Project ":name" has reached its estimated time', ['name' => $this->project->name]));
    }
}
