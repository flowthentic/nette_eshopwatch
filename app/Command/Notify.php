<?php

namespace App\Console;

use App\Model\Database;
use App\Model\Database\Config;
use Nette\Mail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Notify extends Command
{
    private Config $address;
    private Config $threshold;
    public function __construct(
        private Database\EntityManagerDecorator $em,
        private Mail\Mailer $mailer
    ) {
        Database\Offer::$em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:notify')
            ->setDescription('Sends email notification of price changes.');
    }

    protected function interact(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->em->getRepository(Config::class);
        try {
            $this->address = $config->findBy(['key' => 'email']);
            $this->threshold = $config->findBy(['key' => 'threshold']);
        } catch (\TypeError $e) {
            $output->writeln("First you have to visit the settings page");
            exit();
        }
        return 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = $this->em->getRepository(Database\Product::class)->findAll();
        $rows = array();
        try {
            foreach ($products as $key => $prod) {
                $currentBest = $prod->getBestOffer();
                if (is_null($prod->last_signifficant)) {
                    // if it is new product
                    $prod->last_signifficant = $currentBest->price;
                    $output->writeln("$prod->name now for $prod->last_signifficant");
                    continue;
                }
                //$lastBest = $prod->getBestOffer($currentBest->getOlderOffers());
                if (abs($prod->last_signifficant - $currentBest->price) < $this->threshold->value) {
                    continue;
                }
                $rows[] = array(
                    $prod->name,
                    sprintf('% .2f eur', $currentBest->price),
                    $currentBest->shop_id,
                    sprintf('% .2f eur', $prod->last_signifficant)
                );
                vprintf('Product %s changed price from %4$.2f to %2$.2f', end($rows));
                $prod->last_signifficant = $currentBest->price;
            }
            if (empty($rows)) {
                $output->writeln("No signifficant changes happened");
                return 0;
            }
            $mail = new Mail\Message();
            array_unshift($rows, array('Product', 'Price', 'Eshop', 'Old price')); //headings
            $latte = new \Latte\Engine();
            $latte = $latte->renderToString('app/Presenters/templates/Products.default.latte', array(
                'rows' => $rows,
                'title' => 'Price changes on configured eshops'));
            $mail->setFrom('Franta <franta@example.com>')
                ->addTo($this->address->value)
                ->setHtmlBody($latte);

            $this->mailer->send($mail);
            return 0;
        } finally {
            $this->em->flush();
        }
    }
}
