<?php

namespace App\Repository\V1;

use App\Entity\AO1Marcas;
use Doctrine\ORM\EntityManagerInterface;

class AO1MarcasEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** 
     * @see $this->setMarcasFirstTime
     * @see publicas::AO1MarcasController::getAllMarcas
    */
    public function getAllMarcas()
    {
        $dql = 'SELECT partial mrk.{id, nombre, logo} FROM ' . AO1Marcas::class . ' mrk '.
        'ORDER BY mrk.nombre ASC';
        return $this->em->createQuery($dql);
    }
}