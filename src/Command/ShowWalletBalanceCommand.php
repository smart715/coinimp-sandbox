<?php

namespace App\Command;

use App\Crypto\CryptoFactoryInterface;
use App\Payer\Communicator\CommunicatorInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowWalletBalanceCommand extends Command
{
    /** @var CommunicatorInterface */
    private $communicator;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    public function __construct(
        CommunicatorInterface $communicator,
        CryptoFactoryInterface $cryptoFactory
    ) {
        $this->communicator = $communicator;
        $this->cryptoFactory = $cryptoFactory;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:wallet:balance')
            ->addArgument(
                'crypto',
                InputArgument::REQUIRED,
                'Currency to get balance (either \'xmr\' or \'web\')'
            )
            ->setDescription('Show wallet balance')
            ->setHelp('This command shows total and spendable balance of wallet');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $crypto = $input->getArgument('crypto');
        try {
            $response = $this->communicator->fetchBalance($this->cryptoFactory->create($crypto));
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return;
        }

        foreach ($response as $name => $balance)
            $output->writeln(ucfirst($name).': '.$this->format($balance, $crypto));
    }

    private function format(string $balance, string $crypto): string
    {
        return is_numeric($balance)
            ? number_format(floatval($balance) / pow(10, 12), 8)
                .' '.strtoupper($crypto)
            : $balance;
    }
}
