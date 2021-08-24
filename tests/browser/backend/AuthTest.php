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
                ->screenshot($this->testScreenshot('1. On Login Page'))
                ->type('@loginField', $username)
                ->type('@passwordField', $password)
                ->click('@submitButton')
                ->pause(250)
                ->screenshot($this->testScreenshot('2. Submit Login'));

            $browser
                ->on(new Dashboard)
                ->screenshot($this->testScreenshot('3. After Login'))
                ->click('@accountMenu')
                ->clickLink('Sign out');

            $browser
                ->on(new Login)
                ->screenshot($this->testScreenshot('4. After Logout'));
        });
    }

    public function testPasswordReset()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->pause(500)
                ->screenshot($this->testScreenshot('1. On Login Page'))
                ->click('@forgotPasswordLink')
                ->pause(250)
                ->screenshot($this->testScreenshot('2. Click Password Link'));

            $browser
                ->on(new ForgotPassword)
                ->type('@loginField', 'admin')
                ->screenshot($this->testScreenshot('3. Type Login to Reset'))
                ->click('@submitButton')
                ->pause(250)
                ->screenshot($this->testScreenshot('4. Submit Password Reset'));

            $browser
                ->on(new Login)
                ->waitFor('.flash-message')
                ->pause(100)
                ->screenshot($this->testScreenshot('5. Message Received'))
                ->assertSeeIn('.flash-message', 'email has been sent');
        });
    }
}
