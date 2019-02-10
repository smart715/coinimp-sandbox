<?php

namespace App\Command;

use App\Payer\Communicator\CommunicatorInterface;
use App\Payer\PayoutCallbackHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenPaymentProcessorCommand extends Command
{
    /** @var CommunicatorInterface */
    private $communicator;

    /** @var PayoutCallbackHandlerInterface */
    private $payoutHandler;

    public function __construct(
        CommunicatorInterface $communicator,
        PayoutCallbackHandlerInterface $payoutHandler
    ) {
        $this->communicator = $communicator;
        $this->payoutHandler = $payoutHandler;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:listen')
            ->setDescription('Listen for payment processor responses')
            ->setHelp('This command opens socket to listen for payment processor\'s responses');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Started payment processor callback listener');
        $this->communicator->listenForPayouts(function (array $payload)
 use ($output): void {
            $output->writeln('Received message: '.json_encode($payload));
            if ($this->payoutHandler->process($payload))
                $output->writeln('Payment status changed to '.
                    ('ok' === $payload['status'] ? '"paid"' : '"error"').
                    ', ID: '.$payload['id']);
            else $output->writeln('Payment process failed. Retrying.');
        });
    }
}
