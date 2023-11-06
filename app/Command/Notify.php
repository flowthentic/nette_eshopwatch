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
    private Config $address, $lastNotify, $threshold;
	public function __construct(
        private Database\EntityManagerDecorator $em,
        private Mail\Mailer $mailer)
	{
        Database\Offer::$em = $em;
        $this->address = $em->find(Config::class, 'email');
        $this->threshold = $em->find(Config::class, 'threshold');
        $this->lastNotify = $em->find(Config::class, 'lastNotify') ?? new Config('lastNotify');
        $em->persist($this->lastNotify);
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('app:notify')
			->setDescription('Sends email notification of price changes.');
	}

    protected function interact(InputInterface $input, OutputInterface $output): int
    {
        if (is_null($this->address))
        {
            $output->writeln("First you have to configure your email address");
            return Command::FAILURE;
        }
        if (is_null($this->threshold))
        {
            $output->writeln("First you have to configure minimum price difference");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
        $products = $this->em->getRepository(Database\Product::class)->findAll();
        $rows = array();
        foreach ($products as $key => $prod)
        {
            $currentBest = $prod->getBestOffer();
            if ($key == array_key_first($products) && $currentBest->timestamp <= $this->lastNotify->value)
            {
                $output->writeln("Notification has already been sent after last fetch at ".$this->lastNotify->value);
                return 0;
            }
            $lastBest = $prod->getBestOffer($currentBest->getOlderOffers());
            if (abs($lastBest->price - $currentBest->price) < $this->threshold->value) continue;
            $rows[] = array(
                $prod->name, 
                sprintf('% .2f eur', $currentBest->price),
                $currentBest->shop_id,
                sprintf('% .2f eur', $lastBest->price)
            );
        }
        try
        {
            if (empty($rows))
            {
                $output->writeln("No signifficant changes happened");
                return 0;
            }
            $mail = new Mail\Message;
            array_unshift($rows, array('Product', 'Price', 'Eshop', 'Old price')); //headings
            $latte = new \Latte\Engine;
            $latte = $latte->renderToString('app/Presenters/templates/Products.default.latte', array(
                'rows'=>$rows,
                'title'=> 'Price changes on configured eshops'));
            $mail->setFrom('Franta <franta@example.com>')
                ->addTo($this->address->value)
                ->setHtmlBody($latte);

            $this->mailer->send($mail);
            $output->writeln($latte);
            return 0;
        }
        finally
        {
            $this->lastNotify = $currentBest->timestamp;
            $this->em->flush();
        }
	}

}