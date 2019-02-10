<?php

namespace App\Command;

use App\Notification\NotificationInterface;
use Craue\ConfigBundle\Util\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowNotificationCommand extends Command
{
    /** @var Config $config */
    private $config;

    /** @var NotificationInterface $notification */
    private $notification;

    public function __construct(Config $config, NotificationInterface $notification)
    {
        $this->config = $config;
        $this->notification = $notification;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:notification:show')
            ->setDescription('Show dashboard notification')
            ->setHelp('Shows a notification on dashboard')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('message', InputArgument::OPTIONAL),
                    new InputOption('level', 'l', InputOption::VALUE_OPTIONAL),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);

        $message = $input->getArgument('message');
        $level = strtolower($input->getOption('level'));

        if (!trim($message)) {
            $style->error('Empty message not allowed');
            return;
        }

        if (!$this->notification->levelNameIsValid($level))
            $level = $this->notification->getDefaultLevelName();

        $serializedData = serialize([
            $message,
            $this->notification->convertLevelNameToNumber($level),
        ]);

        if (mb_strlen($serializedData) > 255) {
            $style->error('Message is too long, cut it!');
            return;
        }

        $this->config->set(
            'notification',
            $serializedData
        );

        $style->success('Done! now you can see the notification on dashboard');
        $style->listing([
            "Message: '$message'",
            'Level: ' . ucfirst($level),
        ]);
    }
}
