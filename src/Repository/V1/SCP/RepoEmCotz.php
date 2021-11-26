<?php

namespace App\Repository\V1\SCP;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\StatusTypes;
use App\Repository\V1\SCP\RepoEm;
use Doctrine\ORM\EntityManagerInterface;

class RepoEmCotz extends RepoEm
{

    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /** */
    public function getAllMarcas()
    {
        $dql = 'SELECT mk FROM ' . AO1Marcas::class . ' mk ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllModelos()
    {
        $dql = 'SELECT md, partial mk.{id} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mk '.
        'ORDER BY mk.id ASC';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllStatus()
    {
        $dql = 'SELECT st FROM ' . StatusTypes::class . ' st ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getRepoById(int $idRepo)
    {
        return $this->getRepoMainAndPiezasByIdMain([$idRepo]);
    }
}