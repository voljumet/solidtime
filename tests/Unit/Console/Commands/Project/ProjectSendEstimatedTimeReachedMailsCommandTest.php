<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Project;

use App\Console\Commands\Project\ProjectSendEstimatedTimeReachedMailsCommand;
use App\Enums\Role;
use App\Mail\ProjectEstimatedTimeReachedMail;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCaseWithDatabase;

#[CoversClass(ProjectSendEstimatedTimeReachedMailsCommand::class)]
class ProjectSendEstimatedTimeReachedMailsCommandTest extends TestCaseWithDatabase
{
    public function test_sends_mails_to_owners_and_admins_for_projects_that_reached_their_estimated_time(): void
    {
        // Arrange
        $owner = $this->createUserWithRole(Role::Owner);
        $adminUser = User::factory()->create();
        Member::factory()->forUser($adminUser)->forOrganization($owner->organization)->role(Role::Admin)->create();
        $project = Project::factory()->forOrganization($owner->organization)->create([
            'estimated_time' => 3600,
            'spent_time' => 3600,
        ]);

        // Act
        $exitCode = $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails');

        // Assert
        Mail::assertQueued(ProjectEstimatedTimeReachedMail::class, function ($mail) use ($owner, $project) {
            return $mail->hasTo($owner->user->email) && $mail->project->is($project);
        });
        Mail::assertQueued(ProjectEstimatedTimeReachedMail::class, function ($mail) use ($adminUser, $project) {
            return $mail->hasTo($adminUser->email) && $mail->project->is($project);
        });
        $project->refresh();
        $this->assertNotNull($project->estimated_time_reached_email_sent_at);
        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function test_does_not_send_emails_for_projects_below_their_estimated_time(): void
    {
        // Arrange
        $owner = $this->createUserWithRole(Role::Owner);
        $project = Project::factory()->forOrganization($owner->organization)->create([
            'estimated_time' => 3600,
            'spent_time' => 1800,
        ]);

        // Act
        $exitCode = $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails');

        // Assert
        Mail::assertNothingOutgoing();
        $this->assertNull($project->fresh()->estimated_time_reached_email_sent_at);
        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function test_does_not_send_emails_for_projects_without_an_estimated_time(): void
    {
        // Arrange
        $owner = $this->createUserWithRole(Role::Owner);
        $project = Project::factory()->forOrganization($owner->organization)->create([
            'estimated_time' => null,
            'spent_time' => 3600,
        ]);

        // Act
        $exitCode = $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails');

        // Assert
        Mail::assertNothingOutgoing();
        $this->assertNull($project->fresh()->estimated_time_reached_email_sent_at);
        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function test_does_not_send_emails_again_for_projects_that_were_already_notified(): void
    {
        // Arrange
        $owner = $this->createUserWithRole(Role::Owner);
        $project = Project::factory()->forOrganization($owner->organization)->create([
            'estimated_time' => 3600,
            'spent_time' => 3600,
            'estimated_time_reached_email_sent_at' => now(),
        ]);

        // Act
        $exitCode = $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails');

        // Assert
        Mail::assertNothingOutgoing();
        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function test_does_not_send_emails_to_employees(): void
    {
        // Arrange
        $employee = $this->createUserWithRole(Role::Employee);
        $project = Project::factory()->forOrganization($employee->organization)->create([
            'estimated_time' => 3600,
            'spent_time' => 3600,
        ]);

        // Act
        $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails');

        // Assert
        Mail::assertNotQueued(ProjectEstimatedTimeReachedMail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->user->email);
        });
    }

    public function test_dry_run_option_does_not_send_mails_but_outputs_what_would_happen(): void
    {
        // Arrange
        $owner = $this->createUserWithRole(Role::Owner);
        $project = Project::factory()->forOrganization($owner->organization)->create([
            'estimated_time' => 3600,
            'spent_time' => 3600,
        ]);

        // Act
        $exitCode = $this->withoutMockingConsoleOutput()->artisan('project:send-estimated-time-reached-mails --dry-run');

        // Assert
        Mail::assertNothingOutgoing();
        $this->assertNull($project->fresh()->estimated_time_reached_email_sent_at);
        $this->assertSame(Command::SUCCESS, $exitCode);
    }
}
