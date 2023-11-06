<?php
namespace App\Model\Database;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
final class Product
{
    public function __construct(array $props)
    {
        $this->ean = $props['ean'];
        $this->name = $props['name'];
    }

    #[ORM\Id]
    #[ORM\Column(length: 32, unique: true, nullable: false)]
    protected string $ean;
    #[ORM\Column(length: 32, unique: true, nullable: false)]
    public string $name;

    #[ORM\OneToMany('for', Offer::class)]
    public Collection $offers;

    public function getOffers() : Collection
    {
        $newestFirst = Criteria::create()->orderBy(array('timestamp' => Criteria::DESC, ''));
        return $this->offers->matching($newestFirst);
    }
    public function getCurrentOffers() : Collection
    {
        return $this->offers->matching(Offer::filterLastFetch());
    }
    public function getBestOffer(Collection $offers = null) : Offer
    {
        $offers ??= $this->getCurrentOffers();
        $lowestFirst = Criteria::create()
            ->orderBy(array('price' => Criteria::ASC));
        return $offers->matching($lowestFirst)[0];
    }
}
