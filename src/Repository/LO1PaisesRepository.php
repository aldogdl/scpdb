<?php

namespace App\Repository;

use App\Entity\LO1Paises;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LO1Paises|null find($id, $lockMode = null, $lockVersion = null)
 * @method LO1Paises|null findOneBy(array $criteria, array $orderBy = null)
 * @method LO1Paises[]    findAll()
 * @method LO1Paises[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LO1PaisesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LO1Paises::class);
    }
}
