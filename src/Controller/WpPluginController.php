<?php

namespace App\Controller;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Profile;
use App\Manager\ProfileManagerInterface;
use App\WordpressPlugin\WordpressPluginGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipStream;

class WpPluginController extends AbstractController
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    /** @var TwigEngine */
    private $twig;

    public function __construct(ProfileManagerInterface $profileManager, CryptoFactoryInterface $cryptoFactory, TwigEngine $twig)
    {
        $this->profileManager = $profileManager;
        $this->cryptoFactory = $cryptoFactory;
        $this->twig = $twig;
    }

    public function minerScriptsURLAction(): Response
    {
        return new Response(
            $this->getParameter('miner_url')
        );
    }

    public function avFriendlyURLAction(): Response
    {
        return new Response(
            $this->getParameter('scripts_url')
        );
    }

    public function downloadAction(Request $request): StreamedResponse
    {
        $supportedCryptos = $this->cryptoFactory->getSupportedList();
        $siteKeys = [];
        foreach ($supportedCryptos as $crypto) {
            $siteKeys[$crypto] = $this->getCurrentProfile()->getDefaultSite($this->cryptoFactory->create($crypto))->getKey();
        }

        $wpPluginGenerator = new WordpressPluginGenerator($this->twig, $request->getSchemeAndHttpHost());
        $pluginFilesAndContents = $wpPluginGenerator->getPlugin($siteKeys);
        $fileName = 'coinimp-miner.zip';
        $response = new StreamedResponse(function () use ($pluginFilesAndContents, $fileName): void {
            $zip = new ZipStream\ZipStream($fileName);
            foreach ($pluginFilesAndContents as $file => $content)
                $zip->addFile($file, $content);
            $zip->finish();
        });
        $response->headers->set('Content-Type', 'application/zip');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Accept-Ranges', 'bytes');
        return $response;
    }

    private function getCurrentProfile(): Profile
    {
        return $this->profileManager->getProfile($this->getUser()->getId());
    }
}
