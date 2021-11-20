<?php

namespace App\Repository;

use App\Entity\UsEmpresa;
use App\Repository\V1\CpanelWeb\PnlEmpresaEm;
use App\Repository\V1\UsEmpresaEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsEmpresa[]    findAll()
 * @method UsEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsEmpresa::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new UsEmpresaEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1CpanelWeb($entityManager) {
        return new PnlEmpresaEm($entityManager);
    }
}
