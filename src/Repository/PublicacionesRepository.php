<?php

namespace App\Repository;

use App\Entity\Publicaciones;
use App\Repository\V1\PublicacionesEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Publicaciones|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publicaciones|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publicaciones[]    findAll()
 * @method Publicaciones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicacionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publicaciones::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new PublicacionesEm($entityManager);
    }
}
