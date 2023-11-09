<?php
namespace App\Model\Database;
use Doctrine\ORM\EntityRepository;

class ConfigRepository extends EntityRepository
{
    public function getByKey(string $key) : Config
    {
        if (empty($key))
            throw new \OutOfRangeException('Invalid key value');
        $em = $this->getEntityManager();
        $record = $em->find(Config::class, $key);
        if (is_null($record)) {
            $record = new Config($key);
            $em->persist($record);
        }
        return $record;
    }
}
