<?php

namespace App\Repository;

use App\Entity\AO2Modelos;
use App\Repository\V1\AO2ModelosEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AO2Modelos|null find($id, $lockMode = null, $lockVersion = null)
 * @method AO2Modelos|null findOneBy(array $criteria, array $orderBy = null)
 * @method AO2Modelos[]    findAll()
 * @method AO2Modelos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AO2ModelosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AO2Modelos::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new AO2ModelosEm($entityManager);
    }
}
