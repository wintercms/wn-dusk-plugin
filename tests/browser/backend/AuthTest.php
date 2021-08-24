<?php namespace Winter\Dusk\Tests\Browser\Backend;

use Config;
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
                ->assertHasCookie(Config::get('session.cookie'), false);


            // Get current cookie value
            $cookie = $browser->plainCookie(Config::get('session.cookie'));

            $browser
                ->screenshot($this->testScreenshot('3. After Login'))
                ->click('@accountMenu')
                ->clickLink('Sign out');

            $browser
                ->on(new Login)
                ->screenshot($this->testScreenshot('4. After Logout'));

            // Get new cookie value - it should be different to the old value because the session is new.
            $newCookie = $browser->plainCookie(Config::get('session.cookie'));
            $this->assertNotEquals($cookie, $newCookie);
        });
    }

    public function testThrottling()
    {
        if (!Config::get('auth.throttle.enabled', true)) {
            $this->markTestSkipped('Throttling has been disabled.');
        }

        $maxAttempts = Config::get('auth.throttle.attemptLimit', 5);

        $this->browse(function (Browser $browser) use ($maxAttempts) {
            $username = $username ?? env('DUSK_ADMIN_USER', 'admin');
            $password = 'invalid-password';

            $browser
                ->visit(new Login)
                ->pause(500);

            for ($i = 0; $i < $maxAttempts; ++$i) {
                $browser
                    ->type('@loginField', $username)
                    ->type('@passwordField', $password)
                    ->click('@submitButton')
                    ->pause(250)
                    ->on(new Login)
                    ->waitFor('.flash-message')
                    ->assertDontSeeIn('.flash-message', 'suspended');
            }

            $browser->screenshot($this->testScreenshot('1. After ' . $maxAttempts. ' login attempts'));

            // Ensure we are now suspended after the final attempt
            $browser
                ->type('@loginField', $username)
                ->type('@passwordField', $password)
                ->click('@submitButton')
                ->pause(250)
                ->on(new Login)
                ->waitFor('.flash-message')
                ->screenshot($this->testScreenshot('2. After ' . ($maxAttempts + 1) . ' login attempts'))
                ->assertHasClass('.flash-message', 'error')
                ->assertSeeIn('.flash-message', 'suspended');
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
