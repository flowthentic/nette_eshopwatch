<?php declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\EntityManagerDecorator;
use App\Model\Database\Offer;
use App\Model\Database\Product;
use Nette\Application\UI;

final class ProductsPresenter extends UI\Presenter
{
    private EntityManagerDecorator $entityManager;
    public function injectEntityManager(EntityManagerDecorator $em)
    {
        $this->entityManager = $em;
        Offer::$em = $em;
    }

    public function renderDefault()
    {
        $this->template->table = $this->entityManager->getRepository(Product::class)->findAll();
    }
}