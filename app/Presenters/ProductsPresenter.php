<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\EntityManagerDecorator;
use App\Model\Database\Offer;
use App\Model\Database\Product;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Nette\Application\UI;
use Nette\Utils\Paginator;

final class ProductsPresenter extends UI\Presenter
{
    private EntityRepository $products;
    private Criteria $currentByPrice;
    public function injectEntityManager(EntityManagerDecorator $em)
    {
        $this->products = $em->getRepository(Product::class);
        $this->currentByPrice = $em->getRepository(Offer::class)
            ->filterFetch(0)
            ->orderBy(['price' => Criteria::ASC]);
    }

    public function beforeRender()
    {
        // insert column headers at the beginning of the list
        $this->template->thead = array('Product', 'Price', 'Eshop');
    }

    public function renderPages(int $page = 1)
    {
        $pgr = new Paginator();
        $pgr->setItemCount($this->products->count([]));
        $pgr->setItemsPerPage(100);
        $pgr->setPage($page);

        if ($pgr->getItemCount() > 0) {
            foreach (
                $this->products->findBy(
                    array(),
                    null,
                    $pgr->getItemsPerPage(),
                    $pgr->getFirstItemOnPage() - 1
                ) as $prod
            ) {
                $best = $prod->getOffers()
                    ->matching($this->currentByPrice)
                    ->first();
                $rows[] = array($prod->name, $best->price, $best->shop_id);
            }
            $this->template->rows = $rows;
            $this->template->paginator = $pgr;
        }
        $this->template->title = 'Product prices';
    }

    public function renderChanges(float $threshold, bool $voidOnOmpty = false)
    {
        foreach ($this->products->findAll() as $prod) {
            $currentBest = $prod->getOffers()
                ->matching($this->currentByPrice)
                ->first();
            if (!is_null($prod->last_signifficant) &&
                abs($prod->last_signifficant - $currentBest->price) >= $threshold
            ) {
                $rows[] = array(
                    $prod->name,
                    sprintf('% .2f eur', $currentBest->price),
                    $currentBest->shop_id,
                    sprintf('% .2f eur', $prod->last_signifficant)
                );
            }
            $prod->last_signifficant = $currentBest->price;
        }
        if ($voidOnOmpty && empty($rows)) {
            // This will make Presenter result in VoidResponse
            $this->terminate();
        }
        $this->template->rows = $rows ?? null;
        $this->template->title = 'Price changes';
        $this->template->thead[] = 'Old price'; // append column head
    }
}
