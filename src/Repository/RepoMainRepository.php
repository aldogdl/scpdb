<?php

namespace App\Repository;

use App\Entity\RepoMain;
use App\Repository\V1\SCP\RepoEm;
use App\Repository\V1\CpanelWeb\RepositorioEm as CpanelWebRepositorioEm;
use App\Repository\V1\RepositorioEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepoMain|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepoMain|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepoMain[]    findAll()
 * @method RepoMain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepoMainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepoMain::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     * borrar
     */
    public function getV1($entityManager) {
        return new RepositorioEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     * borrar
     */
    public function getV1CpanelWeb($entityManager) {
        return new CpanelWebRepositorioEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1SCP($entityManager) {
        return new RepoEm($entityManager);
    }
}
