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
    #[ORM\Column(length: 32, unique: true)]
    protected string $ean;

    #[ORM\Column(length: 32, unique: true)]
    public string $name;

    #[ORM\OneToMany('for', Offer::class)]
    public Collection $offers;

    #[ORM\Column(nullable: true)]
    public ?float $last_signifficant;

    public function getOffers(Criteria $criteria = null): Collection
    {
        return is_null($criteria)
            ? $this->offers
            : $this->offers->matching($criteria);
    }
}
