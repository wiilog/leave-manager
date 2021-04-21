<?php

namespace App\Repository;

use App\Entity\Access;

use Doctrine\ORM\EntityRepository;

/**
 * @method Access|null find($id, $lockMode = null, $lockVersion = null)
 * @method Access|null findOneBy(array $criteria, array $orderBy = null)
 * @method Access[]    findAll()
 * @method Access[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessRepository extends EntityRepository
{

    function findByCode($code)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT r
            FROM App\Entity\Access r
            WHERE r.code =:code"
        )->setParameter('code', $code);

        return $query->getOneOrNullResult();
    }

	/**
	 * @param string $label
	 * @return Access|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
    function findOneByLabel($label)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT r
            FROM App\Entity\Access r
            WHERE r.label LIKE :label"
        )->setParameter('label', '%'.$label.'%');

        return $query->getOneOrNullResult();
    }
}
