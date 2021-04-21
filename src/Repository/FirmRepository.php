<?php

namespace App\Repository;

use App\Entity\Firm;
use Doctrine\ORM\EntityRepository;

/**
 * @method Firm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Firm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Firm[]    findAll()
 * @method Firm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirmRepository extends EntityRepository
{

    public function findByLabel($label)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT f
            FROM App\Entity\Firm f
            WHERE f.label LIKE :label"
        )->setParameter('label', '%' . $label . '%');
        
        return $query->getOneOrNullResult();
    }
}
