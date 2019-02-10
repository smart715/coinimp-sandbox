<?php

namespace App\Command;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\Entity\Site;
use App\Manager\CryptoManagerInterface;
use App\Manager\Model\TotalStats;
use App\Manager\ProfileManagerInterface;
use App\Manager\SiteManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowProfileStatsCommand extends AbstractCommand
{
    /** @var SiteManagerInterface $siteManager */
    private $siteManager;

    /** @var ProfileManagerInterface $profileManager */
    private $profileManager;

    /** @var CryptoManagerInterface $cryptoManager */
    private $cryptoManager;

    public function __construct(CryptoManagerInterface $cryptoManager, ?string $name = null)
    {
        parent::__construct($name);

        $this->cryptoManager = $cryptoManager;
    }

    protected function configure(): void
    {
        $this->setName('app:profile:stats')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address of the profile'
            )
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'If set, updates database with fetched values, only shows stats otherwise.'
            )
            ->setDescription('Show profile stats')
            ->setHelp('This command shows stats of each site belonging to given profile, and total stats');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setUp($input->getOption('update'));
        $email = $input->getArgument('email');
        $profile = $this->profileManager->findByEmail($email);

        if (is_null($profile)) {
            $output->writeln('Profile "'.$email.'" was not found');
            return;
        }

        $this->siteManager->setProfile($profile);

        $header = ['Site name', 'Hashes', 'Hash rate', 'Reward', 'Key', 'Currency' ];
        $rows = [];

        foreach ($this->cryptoManager->getAll() as $crypto) {
            foreach ($this->siteManager->getAll($crypto) as $site)
                $rows[] = $this->createSiteRow($site);

            $rows[] = $this->createTotalRow($this->siteManager->getTotalInfo($crypto));
        }


        $this->printResults($rows, $header, $input, $output);

        if ($input->getOption('update'))
            $output->writeln('Stats were successfully updated.');
    }

    private function createSiteRow(Site $site): array
    {
        return $this->createRow(
            $site->getHumanReadableName(),
            $site->getHashesCount(),
            $site->getHashRate(),
            $site->getReward(),
            $site->getKey(),
            $site->getCrypto()->getSymbol()
        );
    }

    private function createTotalRow(TotalStats $stats): array
    {
        return $this->createRow(
            'TOTAL',
            $stats->getHashes(),
            $stats->getRate(),
            $stats->getPendingReward(),
            '-',
            ''
        );
    }

    private function createRow(
        string $name,
        int $hashes,
        float $rate,
        int $reward,
        string $key,
        string $currency
    ): array {
        return [
            $name,
            $hashes,
            number_format($rate, 2),
            number_format($reward / pow(10, 12), 8),
            $key,
            $currency,
        ];
    }

    private function setUp(bool $isUpdateOn): void
    {
        $suffix = $isUpdateOn ? '' : '_readonly';
        $this->siteManager = $this->getContainer()->get(
            'site_manager.normal'.$suffix
        );
        $this->profileManager = $this->getContainer()->get(
            'profile_manager'.$suffix
        );
    }
}
