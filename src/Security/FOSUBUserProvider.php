<?php

namespace App\Security;

use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class FOSUBUserProvider extends BaseClass
{
    /** {@inheritdoc} */
    public function __construct(UserManagerInterface $userManager, array $properties = [])
    {
        parent::__construct($userManager, $properties);
    }

    /** {@inheritdoc} */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $property = $this->getProperty($response);

        $user = $this->userManager->findUserBy([$property => $username]);

        $email = $response->getEmail();
        $existing = $this->userManager->findUserByEmail($email);

        if (null !== $existing) {
            $methodName = 'get'.ucfirst($property);
            if (!method_exists($existing, $methodName))
                throw new AccessDeniedHttpException('Invalid method name');

            if (empty($existing->$methodName()))
                throw new CustomUserMessageAuthenticationException("Please login with your Coinimp Account");

            $method = 'set'.ucfirst($property);
            if (!method_exists($existing, $method))
                throw new AccessDeniedHttpException('Invalid method name');

            $existing->$method($username);
            $this->userManager->updateUser($existing);
            return $existing;
        }

        if (null === $user) {
            $user = $this->userManager->createUser();

            $user->setLastLogin(new \DateTime());
            $user->setEnabled(true);

            $user->setEmail($response->getEmail());
            $user->setUsername($email);
            $user->setUsernameCanonical($email);
            $user->setPassword(hash("sha256", uniqid()));
            $user->addRole('ROLE_USER');

            $method = 'set'.ucfirst($property);
            if (!method_exists($user, $method))
                throw new AccessDeniedHttpException('Invalid method name');

            $user->$method($username);
            $this->userManager->updateUser($user);
            return $user;
        }
    }
}
