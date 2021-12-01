<?php

namespace App\Repository\V1\SCP;

use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
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
    public function getAllProveedores()
    {
        $dql = 'SELECT partial emp.{id, nombre, logo, pagWeb}, tipo, sucs ' .
        'FROM ' . UsEmpresa::class . ' emp '.
        'JOIN emp.sucursales sucs '.
        'JOIN emp.tipo tipo '.
        'WHERE emp.tipo IN (:tipos)';
        return $this->em->createQuery($dql)->setParameter('tipos', [11,12,13]);
    }

    /** */
    public function getAllContactosByIdSucursal($idSuc)
    {
        $dql = 'SELECT partial ct.{id, nombre, celular, cargo} ' .
        'FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.sucursal = :idSuc';
        return $this->em->createQuery($dql)->setParameter('idSuc', $idSuc);
    }

// 263 - 36
// 115.09
// 26-ene.

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