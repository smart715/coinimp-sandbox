<?php

namespace App\Controller;

use App\Communications\ApiCurrencyConverter;
use App\Communications\CurrencyConverterInterface;
use App\Entity\ApiKey;
use App\Entity\Crypto;
use App\Entity\Payment;
use App\Entity\User;
use App\Entity\WalletImp\WalletImp;
use App\Manager\DepositImpManager;
use App\Repository\CryptoRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\WalletServices\SystemWalletConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiController extends BaseController
{
    public function getStatsAction(string $crypto): JsonResponse
    {
        $this->enforceAuthentication();
        $parsedCrypto = $this->getCrypto($crypto);
        return new JsonResponse(
            [
                'sites' => array_map(
                    [ $this, 'createSiteEntry' ],
                    $this->getProfile()->getSites($parsedCrypto)
                ),
                'total' => $this->createTotalStats($parsedCrypto),
                'localMinerTotalHashes' => $this->getSiteManager()->getLocalMiner($parsedCrypto)->getHashesCount(),
                'referralReward' => $this->getProfile()->calculateIncomeReferralReward($parsedCrypto),
                'referralsCount' => $this->getProfile()->getReferralsCount(),
            ]
        );
    }

    public function getPoolStatsAction(string $crypto): JsonResponse
    {
        $this->enforceAuthentication();
        $parsedCrypto = $this->getCrypto($crypto);
        return new JsonResponse($this->createPoolStats($parsedCrypto));
    }

    public function revokeKeysAction(): JsonResponse
    {
        $this->enforceAuthentication();

        /** @var ApiKey $key */
        $key = $this->getUser()->generateApiKey();
        $this->getOrm()->persist($key);
        $this->getOrm()->flush();

        return new JsonResponse([
            'keys' => json_encode([
                'public' => $key->getPublicKey(),
                'private' => $key->getPlainPrivateKey(),
            ], JSON_PRETTY_PRINT),
        ]);
    }

    public function getPendingRewardAction(CurrencyConverterInterface $converter, string $crypto): JsonResponse
    {
        $this->enforceAuthentication();
        $totalInfo = $this->getSiteManager()->getTotalInfo($this->getCrypto($crypto));
        $pendingReward = $totalInfo->getPendingReward();
        $usdRate = $converter->getRate(CurrencyConverterInterface::XMR, CurrencyConverterInterface::USD);

        return new JsonResponse([
            'total' => $pendingReward,
            'usdRate' => $usdRate,
        ]);
    }

    public function getUsdRateAction(CurrencyConverterInterface $converter, string $currency): JsonResponse
    {
        $this->enforceAuthentication();
        $usdRate = $converter->getRate($currency, CurrencyConverterInterface::USD);

        return new JsonResponse([
            'usdRate' => $usdRate,
        ]);
    }

    public function getSessionStatusAction(): JsonResponse
    {
        return new JsonResponse(['loggedIn' => $this->isGranted('ROLE_USER')]);
    }

    public function getRegisteredUsersCountAction(): JsonResponse
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getOrm()->getRepository(User::class);
        $registeredUsersCount = $userRepository->getRegisteredUsersCount();

        return new JsonResponse([
            'count' => $registeredUsersCount,
        ]);
    }

    public function getTotalCoinsAction(ApiCurrencyConverter $converter): JsonResponse
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->getOrm()->getRepository(Payment::class);

        /** @var CryptoRepository $cryptoRepository */
        $cryptoRepository = $this->getOrm()->getRepository(Crypto::class);

        $totalPaidXMR = $paymentRepository->getTotalPaidBySymbolId(
            $cryptoRepository->findBySymbol(
                strtolower(CurrencyConverterInterface::XMR)
            )->getId()
        );

        $totalPaidWEB = $paymentRepository->getTotalPaidBySymbolId(
            $cryptoRepository->findBySymbol(
                strtolower(CurrencyConverterInterface::WEB)
            )->getId()
        );

        $usdRateXMR = $converter->getRate(
            CurrencyConverterInterface::XMR,
            CurrencyConverterInterface::USD
        );

        $usdRateWEB = $converter->getRate(
            CurrencyConverterInterface::WEB,
            CurrencyConverterInterface::USD
        );

        $totalXmrInUsd = ($totalPaidXMR * $usdRateXMR);
        $totalWebInUsd = ($totalPaidWEB * $usdRateWEB);

        $totalPaidWorthInUsd = $totalXmrInUsd + $totalWebInUsd;

        return new JsonResponse([
            'xmr' => $totalPaidXMR,
            'web' => $totalPaidWEB,
            'worth_usd' => $totalPaidWorthInUsd,
        ]);
    }

    public function token(Request $request, DepositImpManager $depositImpManager): JsonResponse
    {
        $currencySymbol = $request->request->get('currencySymbol');
        $currencyAmount = (float)$request->request->get('impAmount');

        $depositImpResponse = $depositImpManager->depositImp(
            $this->getProfile(),
            $currencySymbol,
            $currencyAmount
        );

        return new JsonResponse($depositImpResponse->getResponse());
    }

    public function getSoldImp(SystemWalletConfig $systemWallet): JsonResponse
    {
        bcscale(WalletImp::impUnitBase);
        $soldImp = $systemWallet->getSoldImp();
        return new JsonResponse([
            'soldImp' => bccomp($soldImp, '0') > 0 ? $soldImp : '0',
            'totalImp' => $systemWallet->getCurrentTotalImp(),
        ]);
    }

    public function acceptPolicyAction(): JsonResponse
    {
        $this->getOrm()->persist(
            $this->getProfile()->acceptPolicy()
        );
        $this->getOrm()->flush();
        return new JsonResponse();
    }

    private function enforceAuthentication(): void
    {
        if (!$this->isGranted('ROLE_USER'))
            throw new NotFoundHttpException();
    }
}
