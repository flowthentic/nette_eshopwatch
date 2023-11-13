<?php

namespace App\Console;

use App\Model\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Fetch extends Command
{
    public function __construct(
        private Database\EntityManagerDecorator $em,
        private array $shops)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:fetch')
            ->setDescription('Queries configured eshops and saves their offerings to the database')
            ->addOption('list', 'l', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(Database\Offer::class);
        if ($input->getOption('list')) {
            var_dump($repo->getFetches());
        }
        else {
            foreach ($this->shops as $shop)
                $shop->toDB($repo);
            $this->em->flush();
        }
        return 0;
    }
}
