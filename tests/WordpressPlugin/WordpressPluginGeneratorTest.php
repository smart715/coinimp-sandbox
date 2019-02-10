<?php

namespace App\Tests\WordpressPlugin;

use App\WordpressPlugin\WordpressPluginGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\TwigEngine;

class WordpressPluginGeneratorTest extends TestCase
{
    public function testGetPlugin(): void
    {
        $pluginGenerator = new WordpressPluginGenerator($this->createMock(TwigEngine::class), "");
        $_SERVER['HTTP_HOST'] = "coinimp.com";
        $pluginFilesAndContents = $pluginGenerator->getPlugin(['xmr' => 'blablabla', 'web' => 'blablablabla']);
        $this->assertArrayHasKey('coinimp-miner/coinimp.php', $pluginFilesAndContents);
        $this->assertArrayHasKey('coinimp-miner/options.php', $pluginFilesAndContents);
        $this->assertArrayHasKey('coinimp-miner/popup.html', $pluginFilesAndContents);
        $this->assertArrayHasKey('coinimp-miner/hidecontent.js', $pluginFilesAndContents);
        $this->assertArrayHasKey('coinimp-miner/script.js', $pluginFilesAndContents);
    }
}
