<?php

namespace App\Command;

use App\Manager\ProfileManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteProfileCommand extends Command
{
    /** @var ProfileManagerInterface */
    private $profileManager;

    public function __construct(ProfileManagerInterface $profileManager)
    {
        $this->profileManager = $profileManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:profile:delete')
            ->setDescription('Delete profile')
            ->setHelp('This command deletes profile')
            ->addArgument('email', InputArgument::REQUIRED, 'email address of the profile');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $email = $input->getArgument('email');
        $profile = $this->profileManager->findByEmail($email);

        if (is_null($profile)) {
            $output->writeln('<error>Profile "'.$email.'" was not found</error>');
            return;
        }

        if ($this->askDeleteConfirmation($email, $input, $output)) {
            $this->profileManager->deleteProfile($profile);
            $output->writeln('<info>Profile was successfully deleted</info>');
        } else {
            $output->writeln('<error>Aborted: incorrectly typed email</error>');
        }
    }

    private function askDeleteConfirmation(string $email, InputInterface $input, OutputInterface $output): bool
    {
        $prompt = $this->getHelper('question');
        $question = $this->buildQuestion('Retype email to confirm:', $email);

        return $prompt->ask($input, $output, $question);
    }

    private function buildQuestion(string $message, string $confirmPhrase): ConfirmationQuestion
    {
        return new ConfirmationQuestion(
            '<question>'.$message.'</question> ',
            false,
            "/^{$confirmPhrase}$/i"
        );
    }
}
