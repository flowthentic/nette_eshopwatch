<?php
namespace App\Model\Database;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offers')]
final class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(unique: true, nullable: false)]
    protected int $id;

    #[ORM\Column(nullable: false)]
    protected int $shop_id;
    
    #[ORM\Column(nullable: false)]
    protected int $price;

    #[ORM\Column(nullable: false)]
    protected int $date;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(name: 'ean', referencedColumnName: 'ean')]
    protected Product $product;
}