<?php
namespace App\Model;

class NeexistujuciShop extends AbstractShop
{
    public readonly int $shop_id;
    public function __construct()
    {
        $this->shop_id = 2;
    }
    public function query()
    {
        $json = json_decode($this->json);
        foreach ($json->products as $product)
            yield (array)$product;
    }

    private string $json = <<< 'EOF'
{
"products": [
{
"id": 1,
"name": "Apple iPhone 13",
"description": "Latest iPhone with A15 Bionic chip and Super Retina XDR display.",
"price": 899.00,
"currency": "EUR",
"url": "https://www.apple.com/iphone-13",
"ean": "190199822834"
},
{
"id": 2,
"name": "Sony WH-1000XM4 Headphones",
"description": "Wireless noise-canceling headphones with exceptional sound quality.",
"price": 320.00,
"currency": "EUR",
"url": "https://www.sony.com/wh-1000xm4",
"ean": "2724292819966"
},
{
"id": 3,
"name": "Samsung Galaxy S21",
"description": "Flagship Android smartphone with powerful camera capabilities.",
"price": 799.99,
"currency": "EUR",
"url": "https://www.samsung.com/galaxy-s21",
"ean": "8806090774321"
}
]
}
EOF;
}
