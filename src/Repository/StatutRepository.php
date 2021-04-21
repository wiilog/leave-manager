<?php

namespace App\Repository;

use App\Entity\Access;
use App\Entity\Statut;
use Doctrine\ORM\EntityRepository;

/**
 * @method Statut|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statut|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statut[]    findAll()
 * @method Statut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutRepository extends EntityRepository
{

    /**
     * @param string $label
     * @return Statut|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function findOneByLabelAndCategorie($label, $categorie)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT s
            FROM App\Entity\Statut s
            WHERE s.label LIKE :label AND s.categorie LIKE :categorie"
        )->setParameters([
            'label' => $label,
            'categorie' => $categorie
        ]);

        return $query->getOneOrNullResult();
    }

    /**
     * @param $categorie
     * @return Access[]
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function getByCategorie($categorie)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT s
            FROM App\Entity\Statut s
            WHERE s.categorie LIKE :categorie"
        )->setParameters([
            'categorie' => $categorie
        ]);

        return $query->execute();
    }
}
