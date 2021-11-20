<?php

namespace App\Repository;

use App\Entity\ReseniasResp;
use App\Repository\V1\ReseniasEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReseniasResp|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReseniasResp|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReseniasResp[]    findAll()
 * @method ReseniasResp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReseniasRespRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReseniasResp::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new ReseniasEm($entityManager);
    }
}
