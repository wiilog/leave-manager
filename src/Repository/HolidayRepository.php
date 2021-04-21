<?php

namespace App\Repository;

use App\Entity\Holiday;
use Doctrine\ORM\EntityRepository;

/**
 * @method Holiday|null find($id, $lockMode = null, $lockVersion = null)
 * @method Holiday|null findOneBy(array $criteria, array $orderBy = null)
 * @method Holiday[]    findAll()
 * @method Holiday[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HolidayRepository extends EntityRepository
{

    public function findByUserOrValidator($user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT h
            FROM App\Entity\Holiday h
            JOIN h.requester r
            WHERE r = :validator OR h.validator = :validator"
        )->setParameter('validator', $user);

        return $query->execute();
    }

    public function findByStatus($status)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT h
            FROM App\Entity\Holiday h
            WHERE h.status =:status'
        )->setParameter('status', $status);
        return $query->execute();
    }

    public function findByRequester($requesterId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT h
            FROM App\Entity\Holiday h
            WHERE h.user =:requesterId'
        )->setParameter('requesterId', $requesterId);
        return $query->execute();
    }

    public function findByDate($dateMin, $dateMax)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT h
            FROM App\Entity\Holiday h
            WHERE h.startDate >=:dateMin AND h.endDate <=:dateMax'
        )->setParameters(['dateMin' => $dateMin, 'dateMax' => $dateMax]);

        return $query->execute();
    }
}
