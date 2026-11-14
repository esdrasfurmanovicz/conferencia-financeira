<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // A recuperação por e-mail fica desligada até o envio de e-mail ser configurado.
    public function test_password_reset_is_disabled_by_default(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password', ['email' => 'test@example.com'])->assertNotFound();
        $this->get('/reset-password/token')->assertNotFound();
    }

    public function test_login_screen_has_no_forgot_password_link(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Esqueci a senha');
    }
}
