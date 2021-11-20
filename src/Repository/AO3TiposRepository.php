<?php

namespace App\Repository;

use App\Entity\AO3Tipos;
use App\Repository\V1\AO3TiposEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AO3Tipos|null find($id, $lockMode = null, $lockVersion = null)
 * @method AO3Tipos|null findOneBy(array $criteria, array $orderBy = null)
 * @method AO3Tipos[]    findAll()
 * @method AO3Tipos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AO3TiposRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AO3Tipos::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new AO3TiposEm($entityManager);
    }
}
