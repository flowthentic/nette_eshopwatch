<?php

namespace App\Model\Database;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Tracy\Debugger;

class OfferRepository extends EntityRepository
{
    private \DateTime $currentFetch;

    public function addOffer(array $offer, string $shop, bool $insertProduct = false) : Offer
    {
        $this->currentFetch ??= new \DateTime();
        $em = $this->getEntityManager();

        $product = $em->find(Product::class, $offer['ean']);
        if (is_null($product) && $insertProduct) {
            $product = new Product($offer);
            $em->persist($product);
        }

        $newOffer = new Offer($shop, $this->currentFetch);
        $newOffer->price = $offer['price'];
        $newOffer->for = $product;
        $em->persist($newOffer);
        return $newOffer;
    }

    public function getFetches(\DateTime $before = null) : array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o.timestamp')->distinct()
            ->from(Offer::class, 'o')
            ->orderBy('o.timestamp', 'DESC');
        if (!is_null($before)) {
            $qb->where($qb->expr()->lt('o.timestamp', ':dt'));
            $qb->setParameter('dt', $before, Types::DATETIME_MUTABLE);
        }
        return $qb->getQuery()->getSingleColumnResult();
    }

    public function filterFetch(\DateTime|int $fetch) : Criteria
    {
        if (!$fetch instanceof \DateTime) {
            @$fetch = $this->getFetches()[$fetch];
        }
        $fetch = is_null($fetch) ? 'null' : new \DateTime($fetch);
        return Criteria::create()
            ->where(Criteria::expr()->eq('timestamp', $fetch));
    }
}
