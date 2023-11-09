<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\EntityManagerDecorator;
use App\Model\Database\Offer;
use App\Model\Database\Product;
use Nette\Application\UI;
use Nette\Utils\Paginator;

final class ProductsPresenter extends UI\Presenter
{
    private EntityManagerDecorator $em;
    public function injectEntityManager(EntityManagerDecorator $em)
    {
        $this->em = $em;
        Offer::$em = $em;
    }

    public function renderDefault(int $page)
    {
        $rows[] = array('Product', 'Price', 'Eshop');
        $repo = $this->em->getRepository(Product::class);
        $pgr = new Paginator();
        $pgr->setItemCount($repo->count(array()));
        $pgr->setItemsPerPage(100);
        $pgr->setPage($page);

        if ($pgr->getItemCount() > 0) {
            foreach (
                $repo->findBy(
                    array(),
                    null,
                    $pgr->getItemsPerPage(),
                    $pgr->getFirstItemOnPage() - 1
                ) as $prod
            ) {
                $best = $prod->getBestOffer();
                $rows[] = array($prod->name, $best->price, $best->shop_id);
            }
            $this->template->rows = $rows;
            $this->template->paginator = $pgr;
        }
        $this->template->title = 'Product prices';
    }
}
