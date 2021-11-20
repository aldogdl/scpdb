<?php

namespace App\Repository;

use App\Entity\RepoAutos;
use App\Repository\V1\SCP\RepoEm;
use App\Repository\V1\RepositorioEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepoAutos|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepoAutos|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepoAutos[]    findAll()
 * @method RepoAutos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepoAutosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepoAutos::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new RepositorioEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1SCP($entityManager) {
        return new RepoEm($entityManager);
    }
}
