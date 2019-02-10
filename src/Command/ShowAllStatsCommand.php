<?php

namespace App\Command;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\Entity\Profile;
use App\Manager\CryptoManagerInterface;
use App\Manager\Model\TotalStats;
use App\Manager\SiteManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowAllStatsCommand extends AbstractCommand
{
    /** @var EntityManagerInterface */
    private $orm;

    /** @var CryptoManagerInterface */
    private $cryptoManager;

    /** @var SiteManagerInterface $siteManager */
    private $siteManager;

    /** @var mixed[] */
    private $stats = [];

    /** @var string[] */
    private $usedEmails = [];

    public function __construct(
        EntityManagerInterface $orm,
        CryptoManagerInterface $cryptoManager
    ) {
        $this->orm = $orm;
        $this->cryptoManager = $cryptoManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:stats')
            ->setDescription('Show all profiles stats sorted by total hashes')
            ->setHelp('This command shows total stats of each profile sorted by total hashes count')
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'If set, updates database with fetched values, only shows stats otherwise.'
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setUp($input->getOption('update'));
        $this->stats = [];
        $output->writeln('Please wait, fetching data...');

        foreach ($this->getAllProfiles() as $profile)
            foreach ($this->cryptoManager->getAll() as $crypto)
                $this->processProfile($profile, $crypto);

        $this->sortStats();

        $header = ['Email', 'Currency', 'Total hashes', 'Hash rate', 'Pending rew.', 'Total reward'];
        $rows = [];
        foreach ($this->stats as $email => $profileStatsCrypto)
            foreach ($profileStatsCrypto as $symbol => $profileStats)
                $rows[] = $this->createRow($email, $profileStats, $symbol);
        $this->printResults($rows, $header, $input, $output);

        if ($input->getOption('update'))
            $output->writeln('Stats were successfully updated.');
    }

    /**
     * @return Profile[]
     */
    private function getAllProfiles(): array
    {
        return $this->orm->getRepository(Profile::class)->findAll();
    }

    private function processProfile(Profile $profile, Crypto $crypto): void
    {
        $this->siteManager->setProfile($profile);
        $this->stats[$profile->getEmail()][$crypto->getSymbol()] = $this->siteManager->getTotalInfo($crypto);
    }

    private function sortStats(): void
    {
        uasort($this->stats, function (array $a, array $b): int {
            return $b['xmr']->getHashes() - $a['xmr']->getHashes();
        });
    }

    private function createRow(string $email, TotalStats $stats, string $symbol): array
    {
        $emailUsed = in_array($email, $this->usedEmails);

        if (!$emailUsed)
            $this->usedEmails[] = $email;

        return [
            $emailUsed ? '' : $email,
            $symbol,
            $stats->getHashes(),
            number_format($stats->getRate(), 2),
            number_format($stats->getPendingReward() / pow(10, 12), 8),
            number_format($stats->getTotalReward() / pow(10, 12), 8),
        ];
    }

    private function setUp(bool $isUpdateOn): void
    {
        $suffix = $isUpdateOn ? '' : '_readonly';
        /** @var SiteManagerInterface $siteManager */
        $this->siteManager = $this->getContainer()->get(
            'site_manager.normal'.$suffix
        );
    }
}
