<?php

namespace App\Repository;

use App\Entity\LO2Estados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LO2Estados|null find($id, $lockMode = null, $lockVersion = null)
 * @method LO2Estados|null findOneBy(array $criteria, array $orderBy = null)
 * @method LO2Estados[]    findAll()
 * @method LO2Estados[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LO2EstadosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LO2Estados::class);
    }

}
