<?php

namespace App\Repository;

use App\Entity\UsContacts;
use App\Repository\V1\CpanelWeb\PnlEmpresaEm;
use App\Repository\V1\SCP\ProveEm;
use App\Repository\V1\UsContactsEm;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsContacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsContacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsContacts[]    findAll()
 * @method UsContacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsContactsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsContacts::class);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new UsContactsEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1CpanelWeb($entityManager) {
        return new PnlEmpresaEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1SCP($entityManager) {
        return new ProveEm($entityManager);
    }
}
