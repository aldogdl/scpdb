<?php

namespace App\Repository;

use App\Entity\UsEmpresaTipos;
use App\Repository\V1\UsEmpresaTiposEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsEmpresaTipos|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsEmpresaTipos|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsEmpresaTipos[]    findAll()
 * @method UsEmpresaTipos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsEmpresaTiposRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsEmpresaTipos::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new UsEmpresaTiposEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1SCPCotz($entityManager) {
        return new UsEmpresaTiposEm($entityManager);
    }
    
}
