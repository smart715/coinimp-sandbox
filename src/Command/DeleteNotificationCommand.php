<?php

namespace App\Command;

use Craue\ConfigBundle\Util\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteNotificationCommand extends Command
{
    /** @var Config $config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:notification:hide')
            ->setDescription('Hide dashboard notification')
            ->setHelp('Hides any notifications showing on dashboard');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);

        if ($style->confirm('Are you sure you want to delete dashboard notification?', false)) {
            $this->config->set(
                'notification',
                null
            );
            $style->success('Notification deleted!');
        }
    }
}
