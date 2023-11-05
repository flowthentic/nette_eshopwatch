<?php
namespace App\Model;

class FiktivnyShop extends AbstractShop
{
    public readonly int $shop_id;
    public function __construct()
    {
        $this->shop_id = 1;
    }
    public function query()
    {
        $feed = new \SimpleXMLElement($this->feed);
        foreach ($feed->children() as $product)
        {
            $data = array();
            foreach ($product->children() as $prop)
                $data[$prop->getName()] = $prop->__toString();
            yield $data;
        }

        return $data;

    }

    private string $feed = <<< 'EOF'
<products>
<product>
<id>10</id>
<name>Apple iPhone 13</name>
<description>Latest iPhone with A15 Bionic chip and Super Retina XDR display.</description>
<price>879.00</price>
<currency>EUR</currency>
<url>https://www.apple.com/iphone-13</url>
<ean>190199822834</ean>
</product>
<product>
<id>11</id>
<name>Samsung Galaxy S21</name>
<description>Flagship Android smartphone with powerful camera capabilities.</description>
<price>799.99</price>
<currency>EUR</currency>
<url>https://www.samsung.com/galaxy-s21</url>
<ean>8806090774321</ean>
</product>
<product>
<id>12</id>
<name>Sony WH-1000XM4 Headphones</name>
<description>Wireless noise-canceling headphones with exceptional sound quality.</description>
<price>349.00</price>
<currency>EUR</currency>
<url>https://www.sony.com/wh-1000xm4</url>
<ean>2724292819966</ean>
</product>
<product>
<id>13</id>
<name>Logitech MX Master 3S</name>
<description>Mouse - wireless, laser, for right-handed people, connection via bluetooth.</description>
<price>100</price>
<currency>EUR</currency>
<url>https://www.sony.com/wh-1000xm4</url>
<ean>910006559</ean>
</product>
</products>
EOF;
}
