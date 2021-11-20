<?php

namespace App\Repository;

use App\Entity\RepoPzaInfo;
use App\Repository\V1\RepositorioEm;
use App\Repository\V1\CpanelWeb\RepositorioEm as CpanelWebRepositorioEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepoPzaInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepoPzaInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepoPzaInfo[]    findAll()
 * @method RepoPzaInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepoPzaInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepoPzaInfo::class);
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
    public function getV1CpanelWeb($entityManager) {
        return new CpanelWebRepositorioEm($entityManager);
    }
}
