<?php

namespace App\Console;

use App\Model\Database;
use Nette\Mail;
use Nette\Application as NttApp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Notify extends Command
{
    private string $address;
    private float $threshold;
    private NttApp\IPresenter $presenter;
    public function __construct(
        private Database\EntityManagerDecorator $em,
        private Mail\Mailer $mailer,
        NttApp\IPresenterFactory $presenter
    ) {
        $this->presenter = $presenter->createPresenter('Products');
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:notify')
            ->setDescription('Sends email notification of price changes.');
    }

    protected function interact(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->em->getRepository(Database\Config::class);
        try {
            $this->address = $config->find('email')->value;
            $this->threshold = $config->find('threshold')->value;
        } catch (\Throwable $e) {
            $output->writeln("First you have to visit the settings page");
            exit();
        }
        return 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $textResponse = $this->presenter->run(
                new NttApp\Request('Products', null, [
                    'action' => 'Changes',
                    'threshold' => $this->threshold,
                    'voidOnOmpty' => true]));
            $mail = new Mail\Message;
            $mail->setFrom('Franta <franta@example.com>')
                ->addTo($this->address)
                ->setHtmlBody($textResponse->getSource());
            $this->mailer->send($mail);
        } catch (\Nette\MemberAccessException $e) {
            // presenter terminates when there are no results
            // returning VoidResponse instead of TextResponse
            // without getSource method - causing MemberAccessException
            $output->writeln('No signifficant changes happened');
        } finally {
            $this->em->flush();
        }
        return 0;
    }
}
