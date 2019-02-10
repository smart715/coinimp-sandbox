<?php

namespace App\Controller;

use App\Communications\MailSender\ContactUsFormData;
use App\Communications\MailSender\MailSenderInterface;
use App\Entity\Crypto;
use App\Entity\Payment;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\WalletImp\WalletImp;
use App\Form\AddSiteType;
use App\Form\ChangePasswordFormType;
use App\Form\ContactUsType;
use App\Form\DeleteSiteType;
use App\Form\EditEmail2FAType;
use App\Form\EditEmailType;
use App\Form\EditSiteType;
use App\Form\EditWalletType;
use App\Form\Model\EditEmail;
use App\Form\Model\EditPassword;
use App\Form\Model\EditWallet;
use App\Form\PaymentTwoFactorType;
use App\Form\ResettingType;
use App\Form\TwoFactorType;
use App\InvestGateway\InvestGatewayCommunicator;
use App\Manager\AirdropManagerInterface;
use App\Manager\InvestBalanceManager;
use App\Manager\TwoFactorManagerInterface;
use App\Manager\UserManagerInterface;
use App\Payer\PayerInterface;
use App\Response\ApiResponseCreatorInterface;
use App\Results\Airdrop\AirdropResult;
use App\WalletServices\SystemWalletConfig;
use DateInterval;
use DateTime;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends BaseController
{
    private const REFERRAL_COOKIE_EXPIRE_DAYS = 7;
    private const PAYMENTS_ON_HOMEPAGE_COUNT = 10;

    public function indexAction(): Response
    {
        $repository = $this->getOrm()->getRepository(Payment::class);

        $recentPayments = $repository->findBy(
            ['status' => 'paid'],
            ['id' => 'DESC'],
            self::PAYMENTS_ON_HOMEPAGE_COUNT
        );
        $recentPayments = array_map(function (Payment $payment) {
            return $this->updatePaymentCrypto($payment);
        }, $recentPayments);
        $formattedRecentPayments = $this->formatPayments($recentPayments);

        return $this->render('default/index.html.twig', [
            'recentPayments' => $formattedRecentPayments,
        ]);
    }

    public function referralAction(): Response
    {
        return $this->render('default/referral.html.twig');
    }

    public function error500Action(): Response
    {
        throw new \Exception('Exception to test 500 error page in production');
    }

    public function dashboardAction(Request $request, string $crypto): Response
    {
        $parsedCrypto = $this->getCrypto($crypto);
        $this->getSiteManager()->updateReferralSites($parsedCrypto);
        $response = $this->render('default/dashboard.html.twig', [
            'poolStats' => $this->createPoolStats($parsedCrypto),
            'userStats' => $this->createTotalStats($parsedCrypto),
            'defaultSite' => $this->createDefaultSite($parsedCrypto),
            'localMinerSite' => $this->createSiteEntry(
                $this->getSiteManager()->getLocalMiner($parsedCrypto)
            ),
            'scriptName' => $request->attributes->get('scriptName'),
            'referralCode' => $this->createProfileReferralCode(),
            'referralReward' => $this->getProfile()->calculateIncomeReferralReward($parsedCrypto),
            'referralsCount' => $this->getProfile()->getReferralsCount(),
            'apiKey' => $this->getUser()->getApiKey(),
            'crypto' => $crypto,
            'minimalPayout' => $parsedCrypto->getMinimalPayout(),
        ]);
        $this->getOrm()->flush();

        return $response;
    }

    public function buyAction(
        Request $request,
        InvestGatewayCommunicator $invest,
        InvestBalanceManager $investBalanceManager,
        AirdropManagerInterface $airdropManager,
        SystemWalletConfig $systemWallet
    ): Response {

        $userId = $this->getUser()->getId();
        $profile = $this->getProfile();

        return $response = $this->render('default/buy.html.twig', [
            'availableImp' => bcdiv($profile->getWalletImp()->getAvailableAmount(), bcpow(10, WalletImp::impUnitBase)),
            'balance' => $investBalanceManager->getAllRealBalances($profile),
            'btcAddress' => $invest->getPayoutCredentials($userId, 'btc')['address'] ?? '',
            'ethAddress' => $invest->getPayoutCredentials($userId, 'eth')['address'] ?? '',
            'xmrAddress' => $invest->getPayoutCredentials($userId, 'xmr')['address'] ?? '',
            'xmrPaymentId' => $invest->getPayoutCredentials($userId, 'xmr')['payment_id'] ?? '',
            'impPriceInUsd' => $investBalanceManager->getImpPriceInUsd(),
            'depositUrl' => $this->generateUrl('api_token'),
            'hasAirdrops' => $airdropManager->hasAirdrops($this->getProfile()) ,
            'airdropErrorMsg' => (new AirdropResult(AirdropResult::ALREADY_RECIEVED_AIRDROP))->getMessage(),
            'isPolicyAccepted' => $profile->isPolicyAccepted(),
            'referralCode' => $this->createProfileReferralCode(),
            'depositImpMinAmount' => $systemWallet->getDepositImpMinAmount(),
            'depositImpMaxAmount' => $systemWallet->calculateAvailableAmount(),
            'connectionToApiFailed' => $invest->apiConnectionError(),
            'decimals' => WalletImp::impUnitBase,
        ]);
    }

    public function tokenSaleAction(): Response
    {
        return $this->render('default/token_sale.html.twig');
    }

    public function documentationRootAction(Request $request): Response
    {
        return $this->render('default/documentation.html.twig', [
            'scriptName' => $request->attributes->get('scriptName'),
        ]);
    }

    public function documentationAction(?string $section): Response
    {
        $pages = [
            'reference' => 'default/docs_reference.html.twig',
            'http-api' => 'default/docs_http_api.html.twig',
            'possible-errors' => 'default/docs_possible_errors.html.twig',
        ];
        return $this->render($pages[$section], [
            'defaultCrypto' => $this->getDefaultCrypto()->getSymbol(),
        ]);
    }

    public function acceptPolicyAction(Request $request): Response
    {
        $this->getOrm()->persist(
            $this->getProfile()->acceptPolicy()
        );
        $this->getOrm()->flush();
        $this->addFlash('success', 'You agreed to CoinIMP Terms and Conditions.');
        return $this->redirectToRoute('buy');
    }

    public function contactAction(Request $request, MailSenderInterface $mailSender): Response
    {
        $form = $this->createForm(ContactUsType::class);
        $form->handleRequest($request);

        return $form->isSubmitted() && $form->isValid()
            ? $this->onContactFormSubmit($form, $mailSender)
            : $this->renderForm($form, 'Contact Us');
    }

    public function aboutAction(): Response
    {
        return $this->render('default/about.html.twig');
    }

    public function faqAction(): Response
    {
        return $this->render('default/faq.html.twig');
    }

    public function addSiteAction(
        Request $request,
        string $crypto,
        ApiResponseCreatorInterface $apiResponseCreator
    ): Response {
        $parsedCrypto = $this->getCrypto($crypto);
        $site = $this->getSiteManager()->createSite($parsedCrypto);

        $form = $this->createForm(AddSiteType::class, $site, [
            'action' => $this->generateUrl('site_add', [ 'crypto' => $crypto ]),
        ]);
        $form->handleRequest($request);

        return ($form->isSubmitted() && $form->isValid())
            ? $this->addSite($site, $apiResponseCreator, $parsedCrypto)
            : $this->renderAjaxForm($form, 'Add Site');
    }


    public function deleteSiteAction(
        Request $request,
        string $siteWords,
        ApiResponseCreatorInterface $apiResponseCreator
    ): Response {
        $form = $this->createForm(DeleteSiteType::class, null, [
            'action' => $this->generateUrl('site_delete', ['siteWords' => $siteWords]),
        ]);
        $form->handleRequest($request);

        $site = $this->getSiteManager()->getByWords($siteWords);

        if (null === $site) {
            throw $this->createNotFoundException('The site does not exist');
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            $description= 'Do you want to delete the site "'.$site->getName().'"? Current balance will remain unchanged.';
            return $this->renderAjaxForm($form, 'Delete Site "'.$site->getName().'"', $description);
        }

        $this->getSiteManager()->deleteSite($site);
        $siteEntry = $this->createSiteEntry($site);

        return new JsonResponse(
            $apiResponseCreator->createSuccessfulSiteDeletion($siteEntry)
        );
    }

    public function editSiteAction(
        Request $request,
        string $siteWords,
        ApiResponseCreatorInterface $apiResponseCreator
    ): Response {
        $site = $this->getSiteManager()->getByWords($siteWords);

        if (null === $site) {
            throw $this->createNotFoundException('The site does not exist');
        }

        $form = $this->createForm(EditSiteType::class, $site, [
            'action' => $this->generateUrl('site_edit', ['siteWords' => $siteWords]),
        ]);
        $form->handleRequest($request);

        return ($form->isSubmitted() && $form->isValid())
            ? $this->editSite($site, $form, $apiResponseCreator)
            : $this->renderAjaxForm($form, 'Edit Site');
    }

    public function twoFactorAuthAction(
        Request $request,
        TwoFactorManagerInterface $twoFactorManager
    ): Response {
        /** @var User */
        $user = $this->getUser();
        $form = $this->createForm(TwoFactorType::class);
        $isTwoFactor = $user->isGoogleAuthenticatorEnabled();

        if (!$isTwoFactor) {
            $user->setGoogleAuthenticatorSecret($twoFactorManager->generateSecretCode());
            $imgUrl = $twoFactorManager->generateUrl($user);
            $formHeader = 'Enable two-factor authentication';
        }

        $form->handleRequest($request);

        $parameters = [
            'form' => $form->createView(),
            'imgUrl' => $imgUrl ?? '',
            'formHeader' => $formHeader ?? 'Disable two-factor authentication',
            'backupCodes' => [],
            'isTwoFactor' => $isTwoFactor,
            'twoFactorKey' => $user->getGoogleAuthenticatorSecret(),
        ];

        if (!$form->isSubmitted() || !$form->isValid())
            return $this->render('security/2fa_manager.html.twig', $parameters);

        if ($twoFactorManager->checkCode($user, $form)) {
            if ($isTwoFactor) {
                $this->turnOffAuthenticator($twoFactorManager);
                return $this->redirectToRoute('profile');
            }
            $parameters['backupCodes'] = $this->turnOnAuthenticator($twoFactorManager, $user);
            return $this->render('security/2fa_manager.html.twig', $parameters);
        }

        $this->addFlash('error', 'Invalid two-factor authentication code.');
        return $this->render('security/2fa_manager.html.twig', $parameters);
    }

    public function profileSettingsAction(
        Request $request,
        UserManagerInterface $userManager,
        TwoFactorManagerInterface $twoFactorManager
    ): Response {
        /** @var User */
        $user = $this->getUser();

        $settingsEmail = new EditEmail($this->getProfile());
        $email = $this->createForm(EditEmailType::class, $settingsEmail);
        $email->handleRequest($request);

        $settingsPassword = new User();
        $password = $this->createForm(ChangePasswordFormType::class, $settingsPassword);
        $password->handleRequest($request);

        if ($user->isGoogleAuthenticatorEnabled())
            return $this->handle2FAProfile($request, $email, $password, $settingsEmail, $userManager, $twoFactorManager);

        if ($email->isSubmitted() && $email->isValid())
            $this->submitEmailForm($userManager, $settingsEmail);

        if ($password->isSubmitted() && $password->isValid())
            $this->submitPasswordForm($userManager, $settingsPassword);

        return $this->renderProfile($email, $password);
    }

    protected function handle2FAProfile(
        Request $request,
        FormInterface $email,
        FormInterface $password,
        EditEmail $settingsEmail,
        UserManagerInterface $userManager,
        TwoFactorManagerInterface $twoFactorManager
    ): Response {
        /** @var User */
        $user = $this->getUser();

        $email2FA = $this->createForm(EditEmail2FAType::class);
        $header = 'Enter two-factor code to confirm Edit Email';
        $email2FA->handleRequest($request);

        if ($email->isSubmitted() && $email->isValid()) {
            $email2FA->get('email')->setData($settingsEmail->getEmail());
            return $this->renderForm($email2FA, $header);
        }

        $isValidCode = $twoFactorManager->checkCode($user, $email2FA);
        if ($email2FA->isSubmitted()) {
            if (! $email2FA->isValid() || ! $isValidCode) {
                $this->addFlash('danger', 'Invalid two-factor authentication code.');
                return $this->renderForm($email2FA, $header);
            }
            $settingsEmail->setEmail($email2FA->get('email')->getData());
            $this->submitEmailForm($userManager, $settingsEmail);
        }

        return $this->renderProfile($email, $password);
    }

    protected function renderProfile(FormInterface $email, FormInterface $password): Response
    {
        return $this->render('default/profile.html.twig', [
            'emailHeader' => "EMAIL",
            'passwordHeader' => "PASSWORD",
            'formEmail' => $email->createView(),
            'formPassword' => $password->createView(),
            'twoFactorAuth' => $this->getUser()->isGoogleAuthenticatorEnabled(),
        ]);
    }

    private function submitEmailForm(UserManagerInterface $userManager, EditEmail $settingsEmail): void
    {
        $user = $this->getUser();

        if (!$settingsEmail->isExistingEmail())
            return;

        $this->updateTempEmail($userManager, $user, $settingsEmail->getEmail());
        $this->getOrm()->persist($user);
        $this->getOrm()->flush();
        $this->addFlash('success', 'Check your email to confirm your new email.');
    }

    private function submitPasswordForm(UserManagerInterface $userManager, User $settingsPassword): void
    {
        $user = $this->getUser();

        if (!$settingsPassword->isNotEmptyPassword())
            return;

        $this->updatePassword($userManager, $user, $settingsPassword->getPlainPassword());
        $this->getOrm()->persist($user);
        $this->getOrm()->flush();
        $this->addFlash('success', 'Profile Settings was saved successfully.');
    }

    public function walletAddressAction(Request $request, string $crypto): Response
    {
        $profile = $this->getProfile();
        $crypto = $this->getCrypto($crypto);
        $wallet = new EditWallet();
        $wallet->setWalletAddress($profile->getWalletAddress($crypto));
        $form = $this->createForm(EditWalletType::class, $wallet, [
            'walletLengths' => $crypto->getWalletLengths(),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid())
            return $this->toWallet($crypto, $form);

        $profile->setWalletAddress($wallet->getWalletAddress(), $crypto);
        $this->getOrm()->persist($profile);
        $this->getOrm()->flush();

        $this->addFlash('success', 'Wallet address was saved successfully.');
        return $this->toWallet($crypto, $form);
    }

    public function payAction(
        string $crypto,
        Request $request,
        PayerInterface $payer,
        TwoFactorManagerInterface $twoFactorManager
    ): Response {

        $paymentId = $request->get('paymentId') ?? '';
        $quantity = floatval($request->get('quantity'));

        /** @var User */
        $user = $this->getUser();
        if ($user->isGoogleAuthenticatorEnabled()) {
            $form = $this->createForm(PaymentTwoFactorType::class);
            $form->handleRequest($request);
            $header = 'Enter two-factor code to confirm payout';
            $isValidCode = $twoFactorManager->checkCode($user, $form);

            if ($form->isSubmitted() && !$isValidCode) {
                $this->addFlash('danger', 'Invalid two-factor authentication code.');
            }
            if (!$form->isSubmitted()) {
                $form->get('paymentId')->setData($paymentId);
                $form->get('quantity')->setData($quantity);
            }

            if (!$form->isSubmitted() || !$form->isValid() || !$isValidCode) {
                return $this->renderForm($form, $header);
            }

            $paymentId = $form->get('paymentId')->getData() ?? '';
            $quantity = floatval($form->get('quantity')->getData());
        }

        $payResult = $payer->pay($this->getProfile(), $this->getCrypto($crypto), $paymentId, $quantity);
        $this->addFlash($payResult->getFlashType(), $payResult->getMessage());
        return $this->redirectToRoute('wallet', ['crypto' => $crypto]);
    }

    public function registerReferralAction(SessionInterface $session, string $referralCode): Response
    {
        $response = $this->redirectToRoute('fos_user_registration_register');
        $response->headers->setCookie($this->createReferralCookie($referralCode));
        $session->set('referral', $referralCode);
        return $response;
    }

    public function registerIcoReferralAction(Request $request, string $referralCode): Response
    {
        $response = $this->redirectToRoute('fos_user_registration_register');
        $response->headers->setCookie($this->createReferralCookie($referralCode, 'ico_referral'));
        return $response;
    }

    public function requestResetAction(Request $request): Response
    {
        $form = $this->createForm(ResettingType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid())
            return $this->render('bundles/FOSUserBundle/Resetting/request.html.twig', [
                'form' => $form->createView(),
            ]);

        return $this->forward('fos_user.resetting.controller:sendEmailAction');
    }

    public function addAirdropAction(
        Request $request,
        AirdropManagerInterface $airdropManager
    ): Response {
        $code = $request->request->get('airdrop_code') ?? '';
        $airdropsProfile = $this->getProfileByEmail($airdropManager->getAirdropsProfileEmail());
        $airdropResult = $airdropManager->addAirdrop($code, $airdropsProfile, $this->getProfile());
        $this->addFlash($airdropResult->getFlashType(), $airdropResult->getMessage());
        return $this->redirectToRoute('buy');
    }

    public function whitePaperAction(): Response
    {
        return $this->file(
            $this->getParameter('kernel.project_dir').'/public/files/whitepaper.pdf',
            'coinimp_whitepaper.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    public function impTokenPolicyAction(): Response
    {
        return $this->file(
            $this->getParameter('kernel.project_dir').'/public/files/imp_token_sale _policy.pdf',
            'imp_token_sale _policy.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    public function impTokenTermsOfServiceAction(): Response
    {
        return $this->file(
            $this->getParameter('kernel.project_dir').'/public/files/imp_tokens_terms _of_service.pdf',
            'imp_tokens_terms _of_service.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    private function renderAjaxForm(FormInterface $form, string $header, string $description = ''): Response
    {
        $template = $this->renderView('default/ajax_form.html.twig', [
            'form' => $form->createView(),
            'description' => $description,
        ]);
        return new JsonResponse([
            'header' => $header,
            'body' => $template,
        ]);
    }

    private function renderForm(FormInterface $form, string $header): Response
    {
        return $this->render('default/simple_form.html.twig', [
            'formHeader' => $header,
            'form' => $form->createView(),
        ]);
    }

    private function addSite(
        Site $site,
        ApiResponseCreatorInterface $apiResponseCreator,
        Crypto $crypto
    ): Response {
        $siteEntry = $this->createSiteEntry($site);
        if (!$this->getProfile()->canAddSites($crypto))
            return new JsonResponse(
                $apiResponseCreator->createMaximumSitesNumberReached($siteEntry)
            );

        $this->getOrm()->persist($site);
        $this->getOrm()->flush();

        return new JsonResponse(
            $apiResponseCreator->createSuccessfulSiteCreation($siteEntry)
        );
    }

    private function editSite(
        Site $site,
        FormInterface $form,
        ApiResponseCreatorInterface $apiResponseCreator
    ): Response {
        $this->getSiteManager()->editSite($site, $form->get('name')->getData());
        $siteEntry = $this->createSiteEntry($site);

        return new JsonResponse(
            $apiResponseCreator->createSuccessfulSiteEdition($siteEntry)
        );
    }

    private function onContactFormSubmit(
        FormInterface $form,
        MailSenderInterface $mailSender
    ): Response {
        $formData = $form->getData();
        $formDataObject = new ContactUsFormData(
            $formData['body'],
            (int)$formData['subject'],
            $formData['email'],
            $formData['name']
        );

        if (!$mailSender->sendContactUsMail($formDataObject))
            $this->addFlash('error', 'Failed to send message.');
        else $this->addFlash('notice', 'Your message is sent!');

        return $this->renderForm($this->createForm(ContactUsType::class), 'Contact Us');
    }

    private function createDefaultSite(Crypto $crypto): array
    {
        return [
            'key' => $this->getProfile()->getDefaultSite($crypto)->getKey(),
        ];
    }

    /** @param Payment[] $payments */
    private function formatPayments(array $payments): array
    {
        return array_map(function (Payment $payment): array {
            return [
                'date' => $payment->getTimestamp()->getTimestamp(),
                'amount' => $payment->getAmount(),
                'fee' => $payment->getFee(),
                'status' => $payment->getStatus(),
                'id' => $payment->getHash(),
                'explorerUrl' => $payment->getCrypto()->getExplorerUrl(),
                'wallet' => $payment->getWalletAddress(),
                'crypto' => $payment->getCrypto()->getSymbol(),
                'paymentId' => $payment->getPaymentId(),
            ];
        }, $payments);
    }

    private function updatePaymentCrypto(Payment $payment): Payment
    {
        $symbol = $payment->getCrypto()->getSymbol();
        $crypto = $this->getCrypto($symbol);
        $payment->setCrypto($crypto);
        return $payment;
    }

    private function toWallet(Crypto $crypto, ?FormInterface $form = null): Response
    {
        if (is_null($form))
            $form = $this->createForm(EditWalletType::class, $this->getProfile());

        return $this->render('default/wallet.html.twig', [
            'formHeader' => 'Edit Wallet Address',
            'form' => $form->createView(),
            'total' => $this->getSiteManager()->getTotalInfo($crypto)->getPendingReward(),
            'payments' => $this->formatPayments(
                $this->getProfile()->getPaymentsByCrypto($crypto)
            ),
            'totalPaidRewardAmount' => $this->getProfile()->getPaidPayoutReward($crypto),
            'totalPendingPaidRewardAmount' => $this->getProfile()->getPendingPayoutReward($crypto),
            'walletAddress' => $this->getProfile()->getWalletAddress($crypto),
            'minimalPayout' => $crypto->getMinimalPayout(),
            'paymentFee' => $crypto->getFee(),
            'crypto' => $crypto->getSymbol(),
        ]);
    }

    private function createReferralCookie(string $referralCode, string $referral = 'referral'): Cookie
    {
        $cookieExpireTime = new DateTime();
        $cookieExpireTime->add(new DateInterval(
            'P'.self::REFERRAL_COOKIE_EXPIRE_DAYS.'D'
        ));
        return new Cookie($referral, $referralCode, $cookieExpireTime);
    }

    private function updateTempEmail(UserManagerInterface $manager, User $user, string $email): void
    {
        $user->setTempEmail($email);
        $manager->sendEmailConfirmation($user);
    }

    private function updatePassword(UserManagerInterface $manager, User $user, string $password): void
    {
        $user->setPlainPassword($password);
        $manager->updatePassword($user);
    }

    private function turnOnAuthenticator(TwoFactorManagerInterface $twoFactorManager, User $user): array
    {
        $backupCodes = $twoFactorManager->generateBackupCodes();
        $user->setGoogleAuthenticatorBackupCodes($backupCodes);
        $this->getOrm()->persist($user);
        $this->getOrm()->flush();
        $this->addFlash('success', 'Congratulations! You have enabled two-factor authentication!');
        return $backupCodes;
    }

    private function turnOffAuthenticator(TwoFactorManagerInterface $twoFactorManager): void
    {
        /** @var User */
        $user = $this->getUser();
        $googleAuth = $twoFactorManager->getGoogleAuthEntry($user->getId());
        $this->getOrm()->remove($googleAuth);
        $this->getOrm()->flush();
        $this->addFlash('notice', 'You have disabled two-factor authentication!');
    }
}
