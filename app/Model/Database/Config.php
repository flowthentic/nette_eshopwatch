<?php
namespace App\Model\Database;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(ConfigRepository::class)]
final class Config
{
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->value = '';
    }

    #[ORM\Id]
    #[ORM\Column(unique: true, nullable: false)]
    protected string $key;

    #[ORM\Column(nullable: false)]
    public string $value;
}
