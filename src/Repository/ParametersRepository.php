<?php

namespace App\Repository;

use App\Entity\Parameters;
use Doctrine\ORM\EntityRepository;

/**
 * @method Parameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parameters[]    findAll()
 * @method Parameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParametersRepository extends EntityRepository
{

    // /**
    //  * @return Parameters[] Returns an array of Parameters objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Parameters
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Parameters|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOne() {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT p
            FROM App\Entity\Parameters p"
        )->setMaxResults(1);
        return $query->getOneOrNullResult();
    }
}
