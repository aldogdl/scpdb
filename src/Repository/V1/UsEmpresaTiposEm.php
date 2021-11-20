<?php

namespace App\Repository\V1;

use App\Entity\UsEmpresaTipos;
use Doctrine\ORM\EntityManagerInterface;

class UsEmpresaTiposEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Optenemos todos los tipos de empresas existentes
     */
    public function getAllTipos()
    {
        $dql = 'SELECT emt FROM ' . UsEmpresaTipos::class . ' emt '.
        'ORDER BY emt.id ASC';
        return $this->em->createQuery($dql);
    }

}
