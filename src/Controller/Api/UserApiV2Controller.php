<?php

namespace App\Controller\Api;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\SiteUser;
use App\Manager\SiteManagerInterface;
use App\Manager\SiteUserManagerInterface;
use App\Response\Decorator\JsonResponseDecoratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserApiV2Controller extends BaseUserApiController
{
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        JsonResponseDecoratorInterface $responseDecorator,
        SiteManagerInterface $siteManager,
        NormalizerInterface $normalizer,
        CryptoFactoryInterface $cryptoFactory
    ) {
        parent::__construct($responseDecorator, $siteManager, $cryptoFactory);

        $this->normalizer = $normalizer;
    }

    public function withdrawUserAction(Request $request, SiteUserManagerInterface $siteUserManager): JsonResponse
    {
        $siteUser = $request->request->get('user');
        $site = $request->request->get('site-key');
        $amount = $request->request->get('amount');

        if (!$siteUser || !$site || !$amount)
            return $this->responseInvalidParameters();

        $site = $this->findSite($site);

        if (!$site)
            return $this->responseInvalidSite();

        $siteUser = $siteUserManager->findUserByName($site, $siteUser);

        if (!$siteUser)
            return $this->responseDecorator->failure('Invalid user.');

        if ((int)$amount > $siteUser->getHashes() - $siteUser->getWithdrawn())
            return $this->responseDecorator->failure('Not enough reward to withdraw.');

        $siteUserManager->makeWithdraw($siteUser, (int)$amount);

        $request->query->add($request->request->getIterator()->getArrayCopy());

        return $this->getSiteUserBalanceAction($request, $siteUserManager);
    }

    public function getSiteUserListAction(Request $request): JsonResponse
    {
        $site = $request->query->get('site-key');

        if (!$site)
            return $this->responseInvalidParameters();

        $site = $this->findSite($site);

        if (!$site)
            return $this->responseInvalidSite();

        return $this->responseDecorator->success(array_map(function (SiteUser $siteUser) {
            return $siteUser->getName();
        }, $site->getUsers()));
    }

    public function getSiteUserBalanceAction(Request $request, SiteUserManagerInterface $siteUserManager): JsonResponse
    {
        $siteUser = $request->query->get('user');
        $site = $request->query->get('site-key');

        if (!$siteUser || !$site)
            return $this->responseInvalidParameters();

        $site = $this->findSite($site);

        if (!$site)
            return $this->responseInvalidSite();

        $siteUser = $siteUserManager->findUserByName($site, $siteUser);

        if (!$siteUser)
            return $this->responseDecorator->failure('Invalid user.');

        return $this->responseDecorator->success(array_merge([
            'pending' => $siteUser->getHashes() - $siteUser->getWithdrawn(),
        ], $this->serialize($siteUser, ['balance'])));
    }

    /**
     * @param string[] $groups
     * @param array|object $object
     * @return array
     */
    private function serialize($object, array $groups = []): array
    {
        return $this->normalizer->normalize($object, 'json', [
            'groups' => array_merge(['api'], $groups),
        ]);
    }
}
