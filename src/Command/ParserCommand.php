<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Service\ReviewParser\ReviewParserService;
use Symfony\Component\Console\Command\LockableTrait;


#[AsCommand(name: 'app:parser')]
final class ParserCommand extends Command
{
    use LockableTrait;

    public function __construct(private readonly ReviewParserService $parserService)
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Парсер отзывов')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Режим', 'all');
    }


    //php bin/console app:parser
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('Команда уже выполняется другим процессом.');
            return Command::SUCCESS;
        }

        $mode = $input->getOption('mode');
        $output->writeln("<info>Parsing $mode</info>");
        $res = $this->parserService->run($mode);

        $this->release();

        if($res) {
            $output->writeln("<info>Success</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Error</error>");
        }
        return Command::FAILURE;
    }
}
