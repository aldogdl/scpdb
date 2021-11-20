<?php

namespace App\Repository;

use App\Entity\LO3Ciudades;
use App\Repository\V1\LocalidadesEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LO3Ciudades|null find($id, $lockMode = null, $lockVersion = null)
 * @method LO3Ciudades|null findOneBy(array $criteria, array $orderBy = null)
 * @method LO3Ciudades[]    findAll()
 * @method LO3Ciudades[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LO3CiudadesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LO3Ciudades::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new LocalidadesEm($entityManager);
    }
}
