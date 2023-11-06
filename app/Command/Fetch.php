<?php
namespace App\Console;
use App\Model\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Fetch extends Command
{
	public function __construct(private Database\EntityManagerDecorator $em)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('app:fetch')
			->setDescription('Queries configured eshops and saves their offerings to the database');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$now = new \DateTime;
		$loader = new \Nette\Loaders\RobotLoader;
		$loader->addDirectory(__DIR__.'/../Model');
		$loader->setTempDirectory(__DIR__.'/../../temp/');
		$parent = new \ReflectionClass(\App\Model\AbstractShop::class);
		foreach (array_keys($loader->getIndexedClasses()) as $shop)
		{
			$shop = new \ReflectionClass($shop);
			if ($shop->isAbstract() || $shop->getParentClass() != $parent) continue;

			foreach ($shop->newInstance()->query() as $itemData)
			{
				$product = $this->em->find(Database\Product::class, $itemData['ean']);
				if (is_null($product))
				{
					$product = new Database\Product($itemData);
					//$product
					$output->writeln("Fetched new product $product->name");
					$this->em->persist($product);
				}

				$newOffer = new Database\Offer($shop->getName(), $now);
				$newOffer->price = $itemData['price'];
				$newOffer->for = $product;
				$this->em->persist($newOffer);
			}
		}
		$this->em->flush();
		return 0;
	}

}