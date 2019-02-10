<?php

namespace App\Controller\Api;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\Entity\Site;
use App\Manager\SiteManagerInterface;
use App\Response\Decorator\JsonResponseDecoratorInterface;
use App\Utils\XMRConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseUserApiController extends AbstractController
{
    /** @var JsonResponseDecoratorInterface */
    protected $responseDecorator;

    /** @var SiteManagerInterface */
    protected $siteManager;

    /** @var CryptoFactoryInterface */
    protected $cryptoFactory;

    public function __construct(
        JsonResponseDecoratorInterface $responseDecorator,
        SiteManagerInterface $siteManager,
        CryptoFactoryInterface $cryptoFactory
    ) {
        $this->responseDecorator = $responseDecorator;
        $this->siteManager = $siteManager;
        $this->cryptoFactory = $cryptoFactory;
    }

    public function hashesAction(Request $request): JsonResponse
    {
        $crypto = $request->query->get('currency');

        try {
            $crypto = $this->cryptoFactory->create(
                $crypto ?? array_values($this->cryptoFactory->getSupportedList())[0]
            );
        } catch (\Throwable $exception) {
            return $this->responseDecorator->failure($exception);
        }

        if ($request->query->has('site-key'))
            return $this->siteHashes($request->query->get('site-key'));

        return $this->responseDecorator->success(
            $this->siteManager->getTotalInfo($crypto)->getHashes()
        );
    }

    public function rewardAction(Request $request): JsonResponse
    {
        $crypto = $request->query->get('currency');

        try {
            $crypto = $this->cryptoFactory->create(
                $crypto ?? array_values($this->cryptoFactory->getSupportedList())[0]
            );
        } catch (\Throwable $exception) {
            return $this->responseDecorator->failure("Unsupported currency");
        }

        if ($request->query->has('site-key'))
            return $this->siteReward($request->query->get('site-key'));

        return $this->responseDecorator->success(
            XMRConverter::toXMR($this->siteManager->getTotalInfo($crypto)->getPendingReward())
        );
    }

    protected function siteHashes(string $siteKey): JsonResponse
    {
        $site = $this->findSite($siteKey);

        if (null === $site)
            return $this->responseInvalidSite();

        return $this->responseDecorator->success($site->getHashesCount());
    }

    protected function siteReward(string $siteKey): JsonResponse
    {
        $site = $this->findSite($siteKey);

        if (null === $site)
            return $this->responseInvalidSite();

        return $this->responseDecorator->success(
            XMRConverter::toXMR($site->getReward())
        );
    }

    protected function responseInvalidSite(): JsonResponse
    {
        return $this->responseDecorator->failure('Can\'t find valid site.');
    }

    protected function responseInvalidParameters(): JsonResponse
    {
        return $this->responseDecorator->failure('Invalid parameters.');
    }

    protected function findSite(string $siteKey): ?Site
    {
        $site = $this->siteManager->findByKey($siteKey);

        if ($site)
            $this->siteManager->updateSite($site);

        return $site;
    }
}
