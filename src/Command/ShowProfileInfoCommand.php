<?php

namespace App\Command;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\Entity\Profile;
use App\Manager\ProfileManagerInterface;
use App\Utils\XMRConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowProfileInfoCommand extends Command
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    public function __construct(ProfileManagerInterface $profileManager, CryptoFactoryInterface $cryptoFactory)
    {
        $this->profileManager = $profileManager;
        $this->cryptoFactory = $cryptoFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:profile:info')
            ->setDescription('Show profile info')
            ->setHelp('This command shows all related to profile info')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the profile')
            ->addArgument('currency', InputArgument::REQUIRED, 'Needed currency');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);

        $currency = $input->getArgument('currency');
        $email = $input->getArgument('email');
        $profile = $this->profileManager->findByEmail($email);

        if (!in_array($currency, $this->cryptoFactory->getSupportedList())) {
            $style->error('Unsupported currency');
            return;
        }

        if (is_null($profile))
            $style->error('Profile "'.$email.'" was not found');
        else $style->table(
            ['Parameter', 'Info'],
            $this->buildProfileFields($profile, $this->cryptoFactory->create($currency))
        );
    }

    private function buildProfileFields(Profile $profile, Crypto $crypto): array
    {
        return [
            ['Name', $profile->getEmail()],
            ['Wallet', $profile->getWalletAddress($crypto)],
            ['Register date', $profile->getCreatedDate()->format('Y-m-d H:i:s')],
            ['Referral code', $profile->getReferralCode()],
            ['Paid reward', XMRConverter::toXMR($profile->getPaidPayoutReward($crypto))],
        ];
    }
}
