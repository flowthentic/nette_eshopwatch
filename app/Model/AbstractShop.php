<?php

namespace App\Model;

abstract class AbstractShop
{
    public function toDB(Database\OfferRepository $repo)
    {
        foreach ($this->query() as $data)
            $repo->addOffer($data, self::class, true);
    }

    abstract protected function query();
}
