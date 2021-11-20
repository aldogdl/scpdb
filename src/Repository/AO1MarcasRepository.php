<?php

namespace App\Repository;

use App\Entity\AO1Marcas;
use App\Repository\V1\AO1MarcasEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AO1Marcas|null find($id, $lockMode = null, $lockVersion = null)
 * @method AO1Marcas|null findOneBy(array $criteria, array $orderBy = null)
 * @method AO1Marcas[]    findAll()
 * @method AO1Marcas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AO1MarcasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AO1Marcas::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new AO1MarcasEm($entityManager);
    }
}
