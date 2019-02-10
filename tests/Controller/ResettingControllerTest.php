<?php

namespace App\Tests\Controller;

class ResettingControllerTest extends SecureIntegrationTest
{
    public function testRedirectToProfileWhenResetPassword(): void
    {
        $router = $this->authClient->getContainer()->get('router');

        $user = $this->userManager->findUserByEmail('example@mail.com');

        $user->setConfirmationToken($this->generateToken());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->userManager->updateUser($user);

        $url = $router->generate('fos_user_resetting_reset', [
            'token' => $user->getConfirmationToken(),
        ]);

        $crawler = $this->authClient->request('GET', $url);
        $form = $crawler->selectButton('Change password')->form();
        $this->authClient->submit($form, [
            'fos_user_resetting_form[plainPassword][first]' => 'Testtest123',
            'fos_user_resetting_form[plainPassword][second]' => 'Testtest123',
        ]);

        $this->assertTrue($this->authClient->getResponse()->isRedirect('/profile'));
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
