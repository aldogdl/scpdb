<?php

namespace App\Repository\V1\CpanelWeb;

use App\Entity\Sistemas;
use App\Entity\SisCategos;
use Doctrine\ORM\EntityManagerInterface;

class SisCatEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function getAllSistemas() {

        $dql = 'SELECT sys FROM ' . Sistemas::class . ' sys ';
        return $this->em->createQuery($dql);
    }

    /** */
    public function getAllCategos() {

        $dql = 'SELECT cat FROM ' . SisCategos::class . ' cat ';
        return $this->em->createQuery($dql);
    }

}
