<?php

namespace App\Repository;

use App\Entity\Sistemas;
use App\Repository\V1\CpanelWeb\SisCatEm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sistemas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sistemas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sistemas[]    findAll()
 * @method Sistemas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SistemasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sistemas::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new SisCatEm($entityManager);
    }

    /**
     * @see PublicacionesController::getSistemasDelAuto
     */
    public function getSistemas() {
        $dql = 'SELECT sis FROM ' . Sistemas::class . ' sis ';
        return $this->getEntityManager()->createQuery($dql);
    }
}
