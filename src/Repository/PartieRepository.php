<?php

namespace App\Repository;

use App\Entity\Partie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Partie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partie[]    findAll()
 * @method Partie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partie::class);
    }

    public function cherchePartie($id){
        return $this->createQueryBuilder('p')
            ->where('p.j1_id = :id OR p.j2_id = :id')
            ->setParameter('id',$id)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('p')
            ->where('p.something = :value')->setParameter('value', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
