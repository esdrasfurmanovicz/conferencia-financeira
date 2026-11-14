<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_cannot_open_user_management(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get("/users/{$other->id}/edit")->assertForbidden();
        $this->actingAs($user)->delete("/users/{$other->id}")->assertForbidden();
        $this->assertModelExists($other);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_admin_sees_the_user_list_and_the_menu_link(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create(['name' => 'Maria Souza']);

        $this->actingAs($admin)->get('/users')->assertOk()->assertSee('Maria Souza');
        $this->actingAs($admin)->get('/reconciliation')->assertSee('Administração');
        $this->actingAs($other)->get('/reconciliation')->assertDontSee('Administração');
    }

    public function test_admin_can_edit_a_user_and_change_the_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Novo Nome',
            'email' => 'novo@example.com',
            'role' => 'admin',
        ])->assertRedirect('/users');

        $user->refresh();
        $this->assertSame('Novo Nome', $user->name);
        $this->assertTrue($user->isAdmin());
    }

    public function test_admin_can_change_a_users_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch("/users/{$user->id}/password", [
            'password' => 'nova-senha-123',
            'password_confirmation' => 'nova-senha-123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nova-senha-123', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch("/users/{$user->id}/password", [
            'password' => 'nova-senha-123',
            'password_confirmation' => 'outra',
        ])->assertSessionHasErrors('password', errorBag: 'password');
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->delete("/users/{$user->id}")->assertRedirect('/users');
        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_or_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete("/users/{$admin->id}")->assertSessionHas('error');
        $this->assertModelExists($admin);

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'user',
        ])->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_the_last_admin_cannot_be_removed_or_demoted(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        // com dois admins, um remove o outro
        $this->actingAs($admin)->delete("/users/{$other->id}")->assertRedirect('/users');

        // agora só resta um: nenhum outro admin existe para tentar, então testamos pela conta própria
        $this->actingAs($admin)->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('password', errorBag: 'userDeletion');
        $this->assertModelExists($admin);
    }

    public function test_first_registered_user_becomes_admin_and_next_ones_do_not(): void
    {
        $this->post('/register', ['name' => 'Primeiro', 'email' => 'a@example.com', 'password' => 'password', 'password_confirmation' => 'password']);
        $this->assertTrue(User::where('email', 'a@example.com')->first()->isAdmin());

        auth()->logout();

        $this->post('/register', ['name' => 'Segundo', 'email' => 'b@example.com', 'password' => 'password', 'password_confirmation' => 'password']);
        $this->assertFalse(User::where('email', 'b@example.com')->first()->isAdmin());
    }

    public function test_role_cannot_be_set_through_the_profile_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'role' => 'admin']);

        $this->assertFalse($user->fresh()->isAdmin());
    }
}
