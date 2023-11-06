<?php
namespace App\Model\Database;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offers')]
final class Offer
{
    public function __construct(string $shop_id, \DateTime $timestamp)
    {
        $this->shop_id = $shop_id;
        $this->timestamp = $timestamp;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(unique: true, nullable: false)]
    protected int $id;

    #[ORM\Column(nullable: false)]
    public readonly string $shop_id;

    #[ORM\Column(nullable: false)]
    public readonly \DateTime $timestamp;
    
    #[ORM\Column(nullable: false)]
    public float $price;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(name: 'ean', referencedColumnName: 'ean')]
    public Product $for;

    public static EntityManagerDecorator $em;
    public static function filterLastFetch(\DateTime $lowerThan = null): Criteria
    {
        $qb = self::$em->createQueryBuilder();
        $qb->select($qb->expr()->max('o.timestamp'))
            ->from(Offer::class, 'o');
        if (!is_null($lowerThan))
        {
            $qb->where($qb->expr()->lt('o.timestamp', ':dt'));
            $qb->setParameter('dt', $lowerThan, Types::DATETIME_MUTABLE);
        }

        $lastFetch = $qb->getQuery()->getSingleResult()[1];
        $lastFetch = is_null($lastFetch) ? 'null' : new \DateTime($lastFetch);
        return Criteria::create()
            ->where(Criteria::expr()->eq('timestamp', $lastFetch));
    }

    public function getPeerOffers(Criteria $criteria = null) : Collection
    {
        $criteria ??= Criteria::create();
        $criteria
            ->where(Criteria::expr()->eq('timestamp', $this->timestamp))
            ->orderBy(array('price' => Criteria::ASC));
        return $this->for->offers->matching($criteria);
    }

    public function getOlderOffers() : Collection
    {
        $criteria = self::filterLastFetch($this->timestamp);
        return $this->getPeerOffers($criteria);
    }
}