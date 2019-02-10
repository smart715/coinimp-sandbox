<?php

namespace App\Controller;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\Entity\Payment;
use App\Entity\Profile;
use App\Entity\Site;
use App\Entity\User;
use App\Manager\SiteManagerInterface;
use App\Manager\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Knp\Menu\Renderer\TwigRenderer;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserAdminCRUDController extends Controller
{
    /** @var SiteManagerInterface */
    private $siteManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    public function __construct(CryptoFactoryInterface $cryptoFactory)
    {
        $this->cryptoFactory = $cryptoFactory;
    }

    /**
     * @param mixed $id
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function resetPasswordAction($id): RedirectResponse
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $user = $this->admin->getObject($id);

        if (!$user)
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        $request->request->set('username', $user->getUsername());
        $this->getUserManager()->sendResettingEmail($user);

        $this->logger = $this->container->get('monolog.logger.admin_action');
        $this->logger->info('reset_password_user', [$user->getUsername()]);

        $this->addFlash(
            'sonata_flash_success',
            'An email has been sent. It contains a link which user has to follow to reset their password.'
        );
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /** {@inheritdoc} */
    public function showAction($id = null)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());

        $user = $this->admin->getObject($id);

        if (!$user)
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));

        $this->admin->checkAccess('show', $user);

        $this->admin->setSubject($user);

        $profileManager = $this->container->get('profile_manager_readonly');
        $profile = $profileManager->getProfile($user->getId());

        $this->siteManager = $this->container->get('site_manager.normal_readonly');
        $this->siteManager->setProfile($profile);

        $this->logger = $this->container->get('monolog.logger.admin_action');
        $this->logger->info('show_user', [$user->getUsername()]);

        return $this->renderWithExtraParams('admin/custom_user_profile_show.html.twig', [
            'action' => 'show',
            'user' => $user,
            'profile' => $profile,
            'profileWallets' => $this->getWallets($profile),
            'profileStats' => $this->getProfileStats(),
            'sites' => $this->getSiteStats(),
            'payments' => array_map([$this, 'formatPayment'], $profile->getPayments()),
            'paidPayoutRewards' => $this->getPaidRewards($profile),
            'pendingPayoutRewards' => $this->getPendingPayoutRewards($profile),
            'referencer' => $this->getUserReferencer($profile),
            'referrals' => array_map([$this, 'getUserReferral'], $profile->getReferrals()->toArray()),
            'referralIncomes' => $this->getIncomeReferralRewards($profile),
            'elements' => $this->admin->getShow(),
        ], null);
    }

    private function getProfileStats(): array
    {
        $supportedCryptos = $this->cryptoFactory->getSupportedList();

        $profileStats = [];
        foreach ($supportedCryptos as $crypto)
            $profileStats[$crypto] = $this->getProfileStatsByCrypto($crypto);

        return $profileStats;
    }

    private function getProfileStatsByCrypto(string $crypto): array
    {
        $parsedCrypto = $this->cryptoFactory->create($crypto);
        $statsByCrypto = $this->siteManager->getTotalInfo($parsedCrypto);

        return [
            'totalHashes' => $statsByCrypto->getHashes(),
            'hashRate' => $statsByCrypto->getRate(),
            'pendingReward' => $statsByCrypto->getPendingReward(),
            'totalReward' => $statsByCrypto->getTotalReward(),
        ];
    }

    private function getSiteStats(): array
    {
        $supportedCryptos = $this->cryptoFactory->getSupportedList();

        $siteStats = [];
        foreach ($supportedCryptos as $crypto)
            $siteStats = array_merge($siteStats, array_map(
                function (Site $site) {
                    return $this->getSiteStatsByCrypto($site);
                },
                $this->siteManager->getAll($this->cryptoFactory->create($crypto))
            ));

        return $siteStats;
    }

    private function getSiteStatsByCrypto(Site $site): array
    {
        return [
            'name' => $site->getHumanReadableName(),
            'hashes' => $site->getHashesCount(),
            'hashRate' => $site->getHashRate(),
            'reward' => $site->getReward(),
            'key' => $site->getKey(),
            'crypto' => $site->getCrypto()->getSymbol(),
        ];
    }

    public function formatPayment(Payment $payment): array
    {
        return [
            'date' => $payment->getTimestamp(),
            'amount' => $payment->getAmount(),
            'fee' => $payment->getFee(),
            'status' => $payment->getStatus(),
            'id' => $payment->getHash(),
            'privateKey' => $payment->getKey(),
            'walletAddress' => $payment->getWalletAddress(),
            'crypto' => $payment->getCrypto()->getSymbol(),
        ];
    }

    private function getWallets(Profile $profile): array
    {
        return $this->getValuesByCrypto(function (Crypto $crypto) use ($profile) {
            return $profile->getWalletAddress($crypto);
        });
    }

    private function getPaidRewards(Profile $profile): array
    {
        return $this->getValuesByCrypto(function (Crypto $crypto) use ($profile) {
            return $profile->getPaidPayoutReward($crypto);
        });
    }

    private function getPendingPayoutRewards(Profile $profile): array
    {
        return $this->getValuesByCrypto(function (Crypto $crypto) use ($profile) {
            return $profile->getPendingPayoutReward($crypto);
        });
    }

    private function getIncomeReferralRewards(Profile $profile): array
    {
        return $this->getValuesByCrypto(function (Crypto $crypto) use ($profile) {
            return $profile->calculateIncomeReferralReward($crypto);
        });
    }

    private function getUserReferencer(Profile $profile): ?UserInterface
    {
        $profileReferencer = $profile->getReferencer();
        if (is_null($profileReferencer))
            return null;

        return $this->getUserManager()->findUserByEmail($profileReferencer->getEmail());
    }

    public function getUserReferral(Profile $profileReferral): array
    {
        $userReferral = $this->getUserManager()->findUserByEmail($profileReferral->getEmail());
        return [
            'id' => $userReferral->getId(),
            'email' => $userReferral->getEmail(),
            'outcomes' => $this->getValuesByCrypto(function (Crypto $crypto) use ($profileReferral) {
                return $profileReferral->calculateOutcomeReferralReward($crypto);
            }),
        ];
    }

    private function getValuesByCrypto(callable $expression): array
    {
        $supportedCryptos = $this->cryptoFactory->getSupportedList();

        $values = [];
        foreach ($supportedCryptos as $crypto)
            $values[$crypto] = $expression($this->cryptoFactory->create($crypto));

        return $values;
    }

    private function getUserManager(): UserManagerInterface
    {
        return $this->container->get('App\Manager\UserManager');
    }


    public function createAction(): Response
    {
        $request = $this->getRequest();
        $data = $request->request->all();
        $user = array_shift($data)["email"];
        if ($request->isMethod('POST') && null != $this->getUserManager()->findUserByEmail($user) &&
            '/admin/app/user/create' == $request->getPathInfo()) {
            if (!$this->isXmlHttpRequest()) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        "This email \"%name%\" is already used.",
                        ['%name%' => $this->escapeHtml($user)],
                        'SonataAdminBundle'
                    )
                );
                $request->request->set('btn_create_and_create', '');
                $request->request->set('btn_create_and_list', null);
                return $this->redirectTo($user);
            }
        }
        return parent::createAction();
    }
}
