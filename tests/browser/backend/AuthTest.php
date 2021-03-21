<?php namespace Winter\Dusk\Tests\Browser\Backend;

use Laravel\Dusk\Browser;
use Winter\Dusk\Classes\BrowserTestCase;
use Winter\Dusk\Tests\Pages\Backend\Dashboard;
use Winter\Dusk\Tests\Pages\Backend\ForgotPassword;
use Winter\Dusk\Tests\Pages\Backend\Login;

class AuthTest extends BrowserTestCase
{
    public function testSignInAndOut()
    {
        $this->browse(function (Browser $browser) {
            $username = $username ?? env('DUSK_ADMIN_USER', 'admin');
            $password = $password ?? env('DUSK_ADMIN_PASS', 'admin');

            $browser
                ->visit(new Login)
                ->pause(500)
                ->type('@loginField', $username)
                ->type('@passwordField', $password)
                ->click('@submitButton');

            $browser
                ->on(new Dashboard)
                ->click('@accountMenu')
                ->clickLink('Sign out');

            $browser
                ->on(new Login);
        });
    }

    public function testPasswordReset()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->pause(500)
                ->click('@forgotPasswordLink');

            $browser
                ->on(new ForgotPassword)
                ->type('@loginField', 'admin')
                ->click('@submitButton');

            $browser
                ->on(new Login)
                ->waitFor('.flash-message')
                ->assertSeeIn('.flash-message', 'Message sent to your email address');
        });
    }
}
