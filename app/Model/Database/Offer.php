<?php
namespace App\Model\Database;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offers')]
final class Offer
{
    public function __construct(string $shop_id, \DateTime $timestamp = new \DateTime)
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
}