<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends EntityRepository
{

	/**
	 * @param string $email
	 * @return User|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
    public function findOneByEmail($email)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT u
            FROM App\Entity\User u
            WHERE u.email =:email"
        )->setParameter('email', $email);
        return $query->getOneOrNullResult();
    }

    public function findById($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT u
            FROM App\Entity\User u
            WHERE u.id =:id"
        )->setParameter('id', $id);
        return $query->getOneOrNullResult();
    }

    public function getNameAndFirstnameByUser($user)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT u.name, u.firstname
            FROM App\Entity\User u
            WHERE u =:user"
        )->setParameter('user', $user);
        return $query->getOneOrNullResult();
    }

    public function getIdAndNomBySearch($search)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT s.name as id, s.name as text
          FROM App\Entity\User s
          WHERE s.name LIKE :search"
        )->setParameter('search', '%' . $search . '%');

        return $query->execute();
    }

    public function getNameById($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT n.name
            FROM App\Entity\User n
            WHERE n =:id"
        )->setParameter('id', $id);

        return $query->getOneOrNullResult();
    }
}
