<?php

namespace App\Repository;

use App\Entity\SisCategos;
use App\Repository\V1\CpanelWeb\SisCatEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SisCategos|null find($id, $lockMode = null, $lockVersion = null)
 * @method SisCategos|null findOneBy(array $criteria, array $orderBy = null)
 * @method SisCategos[]    findAll()
 * @method SisCategos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SisCategosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SisCategos::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new SisCatEm($entityManager);
    }
}
