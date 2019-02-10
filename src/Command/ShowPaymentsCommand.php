<?php

namespace App\Command;

use App\Entity\Payment;
use App\Manager\ProfileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowPaymentsCommand extends AbstractCommand
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var EntityManagerInterface */
    private $orm;

    public function __construct(
        ProfileManagerInterface $profileManager,
        EntityManagerInterface $orm
    ) {
        $this->profileManager = $profileManager;
        $this->orm = $orm;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:payments')
            ->setDescription('Show payments')
            ->addOption(
                'failed',
                null,
                InputOption::VALUE_NONE,
                'If set, shows only failed payments.'
            )
            ->addOption(
                'paid',
                null,
                InputOption::VALUE_NONE,
                'If set, shows only succeeded payments.'
            )
            ->addOption(
                'profile',
                'p',
                InputOption::VALUE_REQUIRED,
                'Filters by given profile (input email)'
            )
            ->setHelp('This command shows payments');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('failed') && $input->getOption('paid')) {
            $output->writeln('Use either --failed or --paid');
            return;
        }

        try {
            $payments = $this->getMatchingPayments($input);
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $header = [
            'Profile',
            'Amount',
            'Fee',
            'Status',
            'Timestamp',
            'ID',
            'Private key',
            'Wallet address',
            'Currency',
        ];
        $rows = [];
        foreach ($payments as $payment)
            $rows[] = $this->createPaymentRow($payment);
        
        $this->printResults($rows, $header, $input, $output);
    }

    /**
     * @param InputInterface $input
     * @return Payment[]
     * @throws Exception
     */
    private function getMatchingPayments(InputInterface $input): array
    {
        $repository = $this->orm->getRepository(Payment::class);
        $criteria = [];

        if ($input->getOption('failed'))
            $criteria['status'] = 'error';

        if ($input->getOption('paid'))
            $criteria['status'] = 'paid';

        if (!empty($input->getOption('profile')))
            $criteria['profile'] = $this->fetchProfileId($input->getOption('profile'));

        return $repository->findBy($criteria);
    }

    private function fetchProfileId(string $email): ?int
    {
        $profile = $this->profileManager->findByEmail($email);

        if (is_null($profile))
            throw new Exception('Profile '.$email.' does not exist');

        return $profile->getId();
    }

    private function createPaymentRow(Payment $payment): array
    {
        return [
            $payment->getEmail(),
            $payment->getAmount(),
            $payment->getFee(),
            ucfirst($payment->getStatus()),
            $payment->getTimestamp()->format('Y-m-d H:i:s'),
            $payment->getHash(),
            $payment->getKey(),
            $payment->getWalletAddress(),
            $payment->getCrypto()->getSymbol(),
        ];
    }
}
