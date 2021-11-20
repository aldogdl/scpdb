<?php

namespace App\Repository;

use App\Entity\UsSucursales;
use App\Repository\V1\CpanelWeb\PnlEmpresaEm;
use App\Repository\V1\UsSucursalesEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsSucursales|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsSucursales|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsSucursales[]    findAll()
 * @method UsSucursales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsSucursalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsSucursales::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new UsSucursalesEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1CpanelWeb($entityManager) {
        return new PnlEmpresaEm($entityManager);
    }
}
