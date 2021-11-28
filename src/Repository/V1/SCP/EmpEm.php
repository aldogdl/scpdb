<?php

namespace App\Repository\V1\SCP;

use App\Entity\UsContacts;
use Doctrine\ORM\EntityManagerInterface;

class EmpEm extends RepoEm
{

    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /** */
    public function getOwnById($idUser)
    {
        $dql = 'SELECT partial ct.{id, nombre, celular, cargo}, suc, partial emp.{id, nombre, logo, pagWeb} ' .
        'FROM ' . UsContacts::class . ' ct '.
        'JOIN ct.sucursal suc '.
        'JOIN suc.empresa emp '.
        'WHERE ct.user = :idUser';
        return $this->em->createQuery($dql)->setParameter('idUser', $idUser);
    }
}