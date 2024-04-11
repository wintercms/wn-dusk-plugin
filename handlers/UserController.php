<?php

namespace Winter\Dusk\Handlers;

use Winter\Storm\Exception\ApplicationException;

class UserController
{
    /**
     * Retrieve the authenticated user identifier and class name.
     * @param string $manager
     * @return array
     */
    public function user($manager = 'backend')
    {
        $user = $this->getManager($manager)->getUser();

        if (!$user) {
            return [];
        }

        return [
            'id' => $user->id,
            'className' => get_class($user),
        ];
    }

    /**
     * Login using the given user ID / email.
     * @param string|int $userId
     * @param string $manager
     * @return void
     */
    public function login($userId, $manager = 'backend')
    {
        $loggedIn = $this->getManager($manager)->loginUsingId($userId);

        if (!$loggedIn) {
            throw new ApplicationException('Invalid user ID provided.');
        }
    }

    /**
     * Log the user out of the application.
     * @param string $manager
     * @return void
     */
    public function logout($manager = 'backend')
    {
        $this->getManager($manager)->logout();
    }

    /**
     * Get the model class for the auth manager.
     * @param string $manager
     * @return string
     */
    protected function userModel($manager)
    {
        switch ($manager) {
            case 'cms':
                return \Winter\User\Models\User::class;
            case 'backend':
                return \Backend\Models\User::class;
        }

        throw new ApplicationException('Invalid manager provided. Must be either "cms" or "backend".');
    }

    /**
     * Get the specific auth manager for the module.
     * @param $provider
     * @return \Winter\Storm\Auth\Manager
     */
    protected function getManager($manager)
    {
        switch ($manager) {
            case 'cms':
                return \Winter\User\Classes\AuthManager::instance();
            case 'backend':
                return \Backend\Classes\AuthManager::instance();
        }

        throw new ApplicationException('Invalid manager provided. Must be either "cms" or "backend".');
    }
}
