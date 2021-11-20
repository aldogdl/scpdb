<?php

namespace App\Repository;

use App\Entity\Resenias;
use App\Repository\V1\ReseniasEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Resenias|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resenias|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resenias[]    findAll()
 * @method Resenias[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReseniasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resenias::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new ReseniasEm($entityManager);
    }
}
