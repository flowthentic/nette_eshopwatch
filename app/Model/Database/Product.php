<?php
namespace App\Model\Database;
use Doctrine\Common\Collections\Collection;
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
    protected string $name;

    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'for')]
    protected Collection $offers;

}
