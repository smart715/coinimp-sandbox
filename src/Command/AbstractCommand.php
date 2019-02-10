<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /** @var int */
    protected $consoleWidth;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->consoleWidth = min(80, (new Terminal())->getWidth());
    }

    protected function configure(): void
    {
        $this->addOption(
            'pretty',
            null,
            InputOption::VALUE_NONE,
            'If set, show results in multi-line format instead of table.'
        );
    }

    protected function printResults(array $entries, array $header, InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('pretty'))
            $this->formatOutputMultiline($entries, $header, $output);
        else $this->formatOutputTable($entries, $header, $output);
    }

    private function formatOutputMultiline(array $entries, array $header, OutputInterface $output): void
    {
        $formattedOutput = [];
        foreach ($entries as $entry)
            $formattedOutput = array_merge($formattedOutput, $this->formatEntry($entry, $header));
        $output->write($formattedOutput, true);
    }

    private function formatOutputTable(array $entries, array $header, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders($header)->setRows($entries);
        $table->render();
    }

    private function formatEntry(array $entry, array $header): array
    {
        $formattedEntry = [];
        foreach ($entry as $index => $value)
            $formattedEntry[] = $this->formatLine($header[$index], $value);
        $formattedEntry[] = str_repeat('─', $this->consoleWidth);

        return $formattedEntry;
    }

    private function formatLine(string $header, string $value): string
    {
        return str_pad($header.': ', 17).$value;
    }
}
