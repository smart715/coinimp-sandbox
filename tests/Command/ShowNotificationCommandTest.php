<?php

namespace App\Tests\Command;

use App\Command\ShowNotificationCommand;
use App\Notification\Notification;
use Craue\ConfigBundle\Util\Config;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ShowNotificationCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ShowNotificationCommand(
            $this->createConfigMock(),
            $this->createNotificationMock()
        ));

        $command = $application->find('app:notification:show');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            'message' => 'We CAN',
            '--level' => 'INFO',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains("Message: 'We CAN'", $output);
        $this->assertContains('Level: Info', $output);
    }

    private function createConfigMock(): Config
    {
        return $this->createMock(Config::class);
    }

    private function createNotificationMock(): Notification
    {
        $notificationMock = $this->createMock(Notification::class);

        $notificationMock
            ->expects($this->once())
            ->method('levelNameIsValid')
            ->willReturn(true)
        ;

        return $notificationMock;
    }
}
