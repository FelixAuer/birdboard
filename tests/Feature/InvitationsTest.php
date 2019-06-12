<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\Tests\Setup\ProjectFactory;
use App\User;

class InvitationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invited_users_may_update_project_details()
    {
        $this->withoutExceptionHandling();
        $project = ProjectFactory::create();

        $project->invite($newuser = factory(User::class)->create());

        $this->signIn($newuser);
        $this->post(action('ProjectTasksController@store', $project), $attributes = ['body' => 'task']);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    /** @test */
    public function a_project_owner_can_invite_a_user()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::create();

        $userToInvite = factory(User::class)->create();

        $this->actingAs($project->owner)->post($project->path() . '/invitations', [
            'email' => $userToInvite->email
        ])->assertRedirect($project->path());

        $this->assertTrue($project->members->contains($userToInvite));
    }

    /** @test */
    public function the_invited_email_adress_must_be_associated_with_a_valid_birdboard_account()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner)->post($project->path() . '/invitations', [
            'email' => 'notauser@birdboard.com'
        ])
        ->assertSessionHasErrors([
            'email' => 'The user you are inviting must have a Birdboard account.'
        ], null, 'invitations');
    }

    /** @test */
    public function non_owners_may_not_invite_users()
    {
        $project = ProjectFactory::create();
        $user = factory(User::class)->create();

        $assertInvitationsForbidden = function () use ($user, $project) {
            $this->actingAs($user)
                ->post($project->path() . '/invitations')
                ->assertStatus(403);
        };

        $assertInvitationsForbidden();

        $project->invite($user);

        $assertInvitationsForbidden();
    }
}
