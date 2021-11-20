<?php

namespace App\Repository;

use App\Entity\LO4Localidades;
use App\Repository\V1\LocalidadesEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LO4Localidades|null find($id, $lockMode = null, $lockVersion = null)
 * @method LO4Localidades|null findOneBy(array $criteria, array $orderBy = null)
 * @method LO4Localidades[]    findAll()
 * @method LO4Localidades[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LO4LocalidadesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LO4Localidades::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new LocalidadesEm($entityManager);
    }
}
