<?php

namespace App\Repository\V1;

use App\Entity\Catalogo;
use Doctrine\ORM\EntityManagerInterface;

class CatalogoEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function getPiezaByNombre(String $pieza) {

        $dql = 'SELECT pz FROM ' . Catalogo::class . ' pz '.
        'WHERE pz.nombre = :pieza';
        return $this->em->createQuery($dql)->setParameter('pieza', $pieza);
    }

    /** */
    public function getPiezasByNombres(array $pieza) {

        $dql = 'SELECT pz FROM ' . Catalogo::class . ' pz '.
        'WHERE pz.nombre IN(:pieza) '.
        'ORDER BY pz.nombre ASC ';
        return $this->em->createQuery($dql)->setParameter('pieza', $pieza);
    }
}
