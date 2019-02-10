<?php

namespace App\Manager;

use App\Communications\CurrentTime;
use App\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface as BaseUserManager;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Psr\Log\LoggerInterface;

class UserManager implements UserManagerInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TokenGeneratorInterface */
    private $tokenGenerator;

    /** @var BaseUserManager */
    private $userManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var CurrentTime */
    private $currentTime;

    public function __construct(
        BaseUserManager $userManager,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer,
        LoggerInterface $logger,
        CurrentTime $currentTime
    ) {
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->currentTime = $currentTime;
    }

    public function findUserByEmail(string $email): ?UserInterface
    {
        return $this->userManager->findUserByEmail($email);
    }

    public function updatePassword(User $user): void
    {
        $this->userManager->updatePassword($user);
    }

    public function sendEmailConfirmation(User $user): void
    {
        $user->setConfirmationToken($this->tokenGenerator->generateToken());
        $user = clone $user;

        if ($user->hasTempEmail())
            $user->setEmail($user->getTempEmail());

        $this->mailer->sendConfirmationEmailMessage($user);

        $this->logger->info(
            'change email',
            ['request_type' => 'action', 'new email' => $user->getTempEmail()]
        );
    }

    public function confirmTempEmail(User $user): void
    {
        $user->setEmail($user->getTempEmail());
        $user->setUsername($user->getTempEmail());
        $user->setTempEmail('');
    }

    public function sendResettingEmail(User $user): void
    {
        $user->setConfirmationToken($this->tokenGenerator->generateToken());
        $user->setPasswordRequestedAt($this->currentTime->getCurrentTime());
        $this->userManager->updateUser($user);
        $this->mailer->sendResettingEmailMessage($user);
    }
}
