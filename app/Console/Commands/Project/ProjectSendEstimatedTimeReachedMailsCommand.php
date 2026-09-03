<?php

declare(strict_types=1);

namespace App\Console\Commands\Project;

use App\Enums\Role;
use App\Mail\ProjectEstimatedTimeReachedMail;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ProjectSendEstimatedTimeReachedMailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:send-estimated-time-reached-mails '.
        ' { --dry-run : Do not actually send emails or save anything to the database, just output what would happen }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends emails to organization admins and owners for projects that have reached their estimated time.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->comment('Sending project estimated time reached emails...');
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->comment('Running in dry-run mode. No emails will be sent and nothing will be saved to the database.');
        }

        $sentMails = 0;
        Project::query()
            ->whereNotNull('estimated_time')
            ->whereColumn('spent_time', '>=', 'estimated_time')
            ->whereNull('estimated_time_reached_email_sent_at')
            ->with([
                'organization',
            ])
            ->orderBy('created_at', 'asc')
            ->chunk(500, function (Collection $projects) use ($dryRun, &$sentMails): void {
                /** @var Collection<int, Project> $projects */
                foreach ($projects as $project) {
                    $recipients = $project->organization
                        ->members()
                        ->whereIn('role', [Role::Owner->value, Role::Admin->value])
                        ->with('user')
                        ->get();

                    foreach ($recipients as $member) {
                        $this->info('Start sending email to user "'.$member->user->email.'" ('.$member->user->getKey().') for project '.$project->getKey());
                        $sentMails++;
                        if (! $dryRun) {
                            Mail::to($member->user->email)
                                ->queue(new ProjectEstimatedTimeReachedMail($project, $member->user));
                        }
                    }

                    if (! $dryRun) {
                        $project->estimated_time_reached_email_sent_at = Carbon::now();
                        $project->save();
                    }
                }
            });

        $this->comment('Finished sending '.$sentMails.' project estimated time reached emails...');

        return self::SUCCESS;
    }
}
