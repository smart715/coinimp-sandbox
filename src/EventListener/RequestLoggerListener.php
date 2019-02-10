<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use function str_replace;

class RequestLoggerListener
{
    // routeName => logMessage
    private const PAGE_MESSAGES = [
        'homepage' => 'show index',
        'referral' => 'show referral promotion',
        'dashboard' => 'show dashboard',
        'site_add' => 'show add site dialog',
        'site_edit' => 'show edit site dialog for %siteWords%',
        'site_delete' => 'show delete site dialog for %siteWords%',
        'site_show_code' => 'show code for site %siteWords%',
        'wallet' => 'show wallet',
        'payout' => 'trigger payout',
        'documentation_root' => 'show documentation root',
        'documentation' => 'show documentation section: %section%',
        'contact' => 'show contact us form',
        'register_referral' => 'redirected to register page after following referral link %referralCode%',
        'fos_user_resetting_request' => 'show reset password form',
        'fos_user_resetting_check_email' => 'show reset password\'s check email page',
        'fos_user_registration_register' => 'show registration form',
        'fos_user_registration_confirmed' => 'show registration confirmed page',
        'fos_user_security_login' => 'show login form',
        'error500' => 'intentionally trigger 500 error',
    ];

    private const API_MESSAGES = [
        'api_profile_get_stats' => 'get sites stats',
        'api_get_pool_stats' => 'get network stats',
    ];

    private const FORM_MESSAGES = [
        'contact' => 'submit contact us form',
        'site_add' => 'submit add site dialog',
        'site_edit' => 'submit edit site dialog for %siteWords%',
        'site_delete' => 'submit delete site dialog for %siteWords%',
        'wallet' => 'submit edit wallet address form',
        'fos_user_registration_register' => 'submit registration form',
    ];

    /** @var RequestMatcherInterface */
    private $requestMatcher;

    /** @var LoggerInterface */
    private $actionLogger;

    public function __construct(
        RequestMatcherInterface $requestMatcher,
        LoggerInterface $actionLogger
    ) {
        $this->requestMatcher = $requestMatcher;
        $this->actionLogger = $actionLogger;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        try {
            $routeInfo = $this->requestMatcher->matchRequest($request);
            $this->logNormalRequest($request, $routeInfo);
        } catch (ResourceNotFoundException $e) {
            $this->logUnknownRequest($request);
        }
    }

    private function logNormalRequest(Request $request, array $routeInfo): void
    {
        $isFormSubmit = $request->request->count() > 0;

        if ($isFormSubmit)
            $this->logFormSubmit($routeInfo);
        else $this->logRequestWithoutFormSubmit($routeInfo);
    }

    private function logRequestWithoutFormSubmit(array $routeInfo): void
    {
        $routeName = $routeInfo['_route'] ?? '';
        $message = $this->findLogMessage($routeName);

        if (is_null($message)) {
            $this->writeUnknownRouteLog($routeName);
            return;
        }

        $this->writeLog(
            $this->replaceVariables($message, $routeInfo),
            $this->getRequestType($routeName)
        );
    }

    private function logFormSubmit(array $routeInfo): void
    {
        $routeName = $routeInfo['_route'] ?? '';

        if (!isset(self::FORM_MESSAGES[$routeName]))
            return;

        $message = self::FORM_MESSAGES[$routeName];
        $this->writeLog($this->replaceVariables($message, $routeInfo), 'page');
    }

    private function logUnknownRequest(Request $request): void
    {
        $this->writeLog(
            'Requested unknown URL '.$request->getUri(),
            'request'
        );
    }

    private function writeLog(string $message, string $requestType): void
    {
        $this->actionLogger->info($message, [ 'request_type' => $requestType ]);
    }

    private function writeUnknownRouteLog(string $routeName): void
    {
        $this->actionLogger->debug(
            'Requested route '.$routeName,
            [ 'request_type' => 'request' ]
        );
    }

    private function findLogMessage(string $routeName): ?string
    {
        return self::PAGE_MESSAGES[$routeName]
            ?? self::API_MESSAGES[$routeName]
            ?? null;
    }

    private function getRequestType(string $routeName): string
    {
        if (isset(self::PAGE_MESSAGES[$routeName]))
            return 'page';

        if (isset(self::API_MESSAGES[$routeName]))
            return 'api';

        return 'request';
    }

    private function replaceVariables(string $message, array $routeInfo): string
    {
        $resultMessage = $message;

        foreach ($this->cleanRouteInfo($routeInfo) as $variableName => $value)
            $resultMessage = str_replace("%{$variableName}%", $value, $resultMessage);

        return $resultMessage;
    }

    private function cleanRouteInfo(array $routeInfo): array
    {
        unset($routeInfo['_controller']);
        unset($routeInfo['_route']);
        
        return $routeInfo;
    }
}
