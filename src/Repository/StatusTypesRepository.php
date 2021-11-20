<?php

namespace App\Repository;

use App\Entity\StatusTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatusTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusTypes[]    findAll()
 * @method StatusTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusTypes::class);
    }

    /** */
    public function getAllStatus() {

        $dql = 'SELECT st FROM ' . StatusTypes::class . ' st '.
        'ORDER BY st.tipo ASC';
        return $this->_em->createQuery($dql);
    }
}
