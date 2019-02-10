<?php

namespace App\Command;

use App\Manager\AirdropManagerInterface;
use App\Manager\ProfileManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddAirdropCommand extends Command
{
    /** @var AirdropManagerInterface */
    private $airdropManager;

    /** @var ProfileManagerInterface */
    private $profileManager;

    public function __construct(
        AirdropManagerInterface $airdropManager,
        ProfileManagerInterface $profileManager
    ) {
        $this->airdropManager = $airdropManager;
        $this->profileManager = $profileManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:airdrop:add')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'path to the file that contains winners emails list'
            )
            ->addArgument(
                'actions',
                InputArgument::OPTIONAL,
                'minimum required actions to add airdrops'
            )
            ->setDescription('Add airdrop to winners')
            ->setHelp('This command add imps that gained from airdrop to user wallet');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $file = $input->getArgument('file');
        $minActions = $input->getArgument('actions') ?? 0;
        try {
            if (false !== ($handle = fopen($file, 'r'))) {
                while (false !== ($data = fgetcsv($handle, 1024, ','))) {
                    $email = $data[0];
                    $totalActions = $data[9];
                    if ($totalActions < $minActions) {
                        $output->writeln($email . ': has only '.$totalActions.' TOTAL ENTRIES. It should have '.$minActions);
                        continue;
                    }
                    $profile = $this->profileManager->findByEmail($email);
                    if (null === $profile) {
                        $output->writeln($email . ': is not registered');
                        continue;
                    }
                    if ($this->airdropManager->hasAirdrops($profile)) {
                        $output->writeln($email . ': already got airdrop before');
                        continue;
                    }
                    $code = $this->airdropManager->getAirdropCode();
                    if (null === $code) {
                        $output->writeln($email . ': there is no available airdrops codes');
                        continue;
                    }
                    $airdropsProfile = $this->profileManager->findByEmail($this->airdropManager->getAirdropsProfileEmail());
                    $result = $this->airdropManager->addAirdrop($code, $airdropsProfile, $profile);
                    $output->writeln($email . ': ' . $result->getFlashType() . ': ' . $result->getMessage());
                }
                fclose($handle);
            } else {
                $output->writeln('could not open this file '.$file);
            }
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return;
        }
    }
}
