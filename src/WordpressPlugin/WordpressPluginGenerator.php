<?php

namespace App\WordpressPlugin;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Filesystem\Filesystem;

class WordpressPluginGenerator
{
    /** @var TwigEngine */
    private $twig;

    /** @var string */
    private $resourcesURL;

    public function __construct(TwigEngine $twig, string $resourcesURL)
    {
        $this->twig = $twig;
        $this->resourcesURL = $resourcesURL;
    }

    public function getPlugin(array $siteKeys): array
    {
        $pluginOptionsPage = $this->twig->render('wordpressPlugin/options.php.twig');
        $pluginPopupNotification = $this->twig->render('wordpressPlugin/popup.html.twig');
        $hideContentScript = $this->twig->render('wordpressPlugin/hidecontent.js.twig');
        $minerScript = $this->twig->render('wordpressPlugin/script.js.twig');
        $coinImpPlugin = $this->twig->render('wordpressPlugin/coinimp.php.twig', [
            'cryptos' => array_keys($siteKeys),
            'siteKeys' => $siteKeys,
            'resourcesPaths' => $this->resourcesURL . "/wppluginfile/"]);
        $filesAndContent = [
            'coinimp-miner/coinimp.php' => $coinImpPlugin,
            'coinimp-miner/options.php' => $pluginOptionsPage,
            'coinimp-miner/popup.html' => $pluginPopupNotification,
            'coinimp-miner/hidecontent.js' => $hideContentScript,
            'coinimp-miner/script.js' => $minerScript,
        ];
        return $filesAndContent;
    }
}
