<?php

namespace App\Security;

use App\Entity\User as InternalUser;
use App\Manager\ApiKeyManagerInterface;
use App\Security\Model\ApiKeyCredentials;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /** @var ApiKeyManagerInterface */
    private $keyManager;

    public function __construct(ApiKeyManagerInterface $keyManager)
    {
        $this->keyManager = $keyManager;
    }

    /** {@inheritdoc} */
    public function createToken(Request $request, $providerKey): PreAuthenticatedToken
    {
        $public = $request->headers->get('X-API-ID');
        $private = $request->headers->get('X-API-KEY');

        if (!$public || !$private || 64 != strlen($public) || 64 != strlen($private))
            throw new BadCredentialsException();

        return new PreAuthenticatedToken(
            'anon.',
            new ApiKeyCredentials($public, $private),
            $providerKey
        );
    }

    /** {@inheritdoc} */
    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /** {@inheritdoc} */
    public function authenticateToken(
        TokenInterface $token,
        UserProviderInterface $userProvider,
        $providerKey
    ): PreAuthenticatedToken {
        if (!$userProvider instanceof ApiKeyUserProvider)
            throw new InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );

        /** @var ApiKeyCredentials $credentionals */
        $credentionals = $token->getCredentials();

        $key = $this->keyManager->findApiKey($credentionals->getPublic());

        if (is_null($key) || !password_verify($credentionals->getPrivate(), $key->getPrivateKey()))
            throw new BadCredentialsException();

        $username = $userProvider->getUsernameForApiKey($credentionals->getPublic());

        if (!$username)
            throw new CustomUserMessageAuthenticationException(
                sprintf('API Key "%s" does not exist.', $credentionals->getPublic()),
                [],
                400
            );

        $user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $credentionals->getPublic(),
            $providerKey,
            [InternalUser::ROLE_API]
        );
    }

    /** {@inheritdoc} */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            401
        );
    }
}
