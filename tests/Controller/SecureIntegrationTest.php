<?php

namespace App\Tests\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class SecureIntegrationTest extends WebTestCase
{
    /** @var Client $client */
    protected $client;
    /** @var Client $authClient */
    protected $authClient;
    /** @var UserManager $userManager */
    protected $userManager;

    private const USER_LOGIN = 'example@mail.com';
    private const USER_PASSW = 'test123';

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->userManager = $this->client->getContainer()->get('fos_user.user_manager');

        if (null === $this->userManager->findUserByEmail('example@mail.com'))
            $this->createTestUser();

        $this->authClient = static::createClient([], [
            'PHP_AUTH_USER' => self::USER_LOGIN,
            'PHP_AUTH_PW'   => self::USER_PASSW,
        ]);
    }

    private function createTestUser(): void
    {
        $user = $this->userManager->createUser();
        $user->setUsername(self::USER_LOGIN);
        $user->setEmail(self::USER_LOGIN);
        $user->setPlainPassword(self::USER_PASSW);
        $user->setEnabled(true);

        $this->userManager->updatePassword($user);
        $this->userManager->updateUser($user);
    }
}
