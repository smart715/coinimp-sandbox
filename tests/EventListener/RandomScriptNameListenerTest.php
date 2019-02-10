<?php

namespace App\Tests\EventListener;

use App\EventListener\RandomScriptNameListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RandomScriptNameListenerTest extends TestCase
{
    public function testGenerateRandomScriptName(): void
    {
        $event = $this->createMock(GetResponseEvent::class);
        $event->method('isMasterRequest')->willReturn(true);

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn('dashboard');
        $event->method('getRequest')->willReturn($request);

        $session = new Session(new MockArraySessionStorage());
        $request->method('getSession')->willReturn($session);

        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('set')->willReturn($session->get('scriptName'));

        (new RandomScriptNameListener())->onKernelRequest($event);
        $this->assertArrayHasKey('php', $session->get('scriptName'));
        $this->assertArrayHasKey('js', $session->get('scriptName'));
        $this->assertRegExp('/[\w]{4}/', $session->get('scriptName')['php']);
        $this->assertRegExp('/[\w]{4}/', $session->get('scriptName')['js']);
    }
}
