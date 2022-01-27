<?php

namespace App\Repository\V1\SCP;

use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use App\Entity\UsAdmin;
use App\Entity\UsEmpresaTipos;
use App\Entity\UsSucursales;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EmpEm extends RepoEm
{

    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /**
     * Damos de alta un nuevo usuario tipo proveedor 
     */
    public function addProvBasicoUser(
        UserPasswordEncoderInterface $encoder,
        $data
    ): array
    {
        if (empty($data['username']) || empty($data['password'])){
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! Datos Invalidos';
            return $this->result;
        }

        $dql = $this->getUserByUsername($data['username']);
        $hasUsername = $dql->execute();
        if(!$hasUsername) {

            $user = new UsAdmin();
            $user->setPassword($encoder->encodePassword($user, $data['password']));
            $user->setUsername($data['username']);
            $user->setRoles([$data['role']]);
            try {
                $this->em->persist($user);
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['body'] = $user->getId();
            } catch (\Exception $th) {
                $this->result['abort'] = true;
                $this->result['body'] = 'ERROR!! ' . $th->getMessage();
            }
        }else{
            $this->result['abort'] = false;
            $this->result['msg'] = 'has';
            $this->result['body'] = $hasUsername[0]->getId();
        }
        return $this->result;
    }

    /**
     * Damos de alta un nuevo proveedor 
     */
    public function addProvBasicoEmp($data): array
    {
        $obj = null;
        if(array_key_exists('idEmp', $data)) {
            if($data['idEmp'] != 0) {
                $dql = $this->getProveedorById($data['idEmp']);
                $res = $dql->execute();
                if($res) {
                    $obj = $res[0];
                }
            }
        }

        if($obj == null) {
            $obj = new UsEmpresa();
        }
        $obj->setNombre($data['emp']);
        $obj->setDespeq('Alta TMP desde SCP');
        $obj->setTipo($this->em->getPartialReference(UsEmpresaTipos::class, $data['tipo']));
        $obj->setAvo($this->em->getPartialReference(UsAdmin::class, 1));
        $obj->setLogo('0');
        
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = $obj->getId();
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        return $this->result;
    }

    /**
     * Damos de alta un nuevo proveedor 
     */
    public function addProvBasicoSuc($idEmp, $data): array
    {
        $obj = null;
        if(array_key_exists('idSuc', $data)) {
            if($data['idSuc'] != 0) {
                $dql = $this->getSucursalById($data['idSuc']);
                $res = $dql->execute();
                if($res) {
                    $obj = $res[0];
                }
            }
        }

        if($obj == null) {
            $obj = new UsSucursales();
            $obj->setEmpresa($this->em->getPartialReference(UsEmpresa::class, $idEmp));
        }
        $obj->setDomicilio($data['dom']);
        $obj->setTelefono($data['tel']);

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = $obj->getId();
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        return $this->result;
    }

    /**
     * Damos de alta un nuevo proveedor 
     */
    public function addProvBasicoContact($ids, $data): array
    {
        $obj = null;
        if(array_key_exists('id', $data)) {
            if($data['id'] != 0) {
                $dql = $this->getContactoById($data['id']);
                $res = $dql->execute();
                if($res) {
                    $obj = $res[0];
                }
            }
        }
        if($obj == null) {
            $obj = new UsContacts();
            $obj->setUser($this->em->getPartialReference(UsAdmin::class, $ids['idUser']));
        }
        $obj->setSucursal($this->em->getPartialReference(UsSucursales::class, $ids['suc']));
        $obj->setNombre($data['cta']);
        $obj->setCelular($data['cel']);
        $obj->setCargo($data['carg']);
        $obj->setNotifiKey('0');
        $obj->setNotifWeb('0');

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = $obj->getId();
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        return $this->result;
    }

    ///
    public function getUserByUsername(String $username) {

        $dql = 'SELECT us FROM ' . UsAdmin::class . ' us '.
        'WHERE us.username = :username';
        return $this->em->createQuery($dql)->setParameter('username', $username);
    }

    /** */
    public function getProveedorById($idProv)
    {
        $dql = 'SELECT partial emp.{id, nombre, logo, pagWeb}, tipo, sucs ' .
        'FROM ' . UsEmpresa::class . ' emp '.
        'JOIN emp.sucursales sucs '.
        'JOIN emp.tipo tipo '.
        'WHERE emp.id = :idProv';
        return $this->em->createQuery($dql)->setParameter('idProv', $idProv);
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
    public function getSucursalById($idSuc)
    {
        $dql = 'SELECT partial suc.{id, domicilio, telefono, empresa}, emp ' .
        'FROM ' . UsSucursales::class . ' suc '.
        'JOIN suc.empresa emp '.
        'WHERE suc.id = :id';
        return $this->em->createQuery($dql)->setParameter('id', $idSuc);
    }

    /** */
    public function getContactoById($idCtc)
    {
        $dql = 'SELECT partial ct.{id, nombre, celular, cargo} ' .
        'FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.id = :id';
        return $this->em->createQuery($dql)->setParameter('id', $idCtc);
    }

    /** */
    public function getAllContactosByIdSucursal($idSuc)
    {
        $dql = 'SELECT partial ct.{id, nombre, celular, cargo} ' .
        'FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.sucursal = :idSuc';
        return $this->em->createQuery($dql)->setParameter('idSuc', $idSuc);
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

    /** */
    public function existeCelular($celular)
    {
        $dql = 'SELECT partial ct.{id, nombre, celular, cargo} ' .
        'FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.celular = :celular';
        return $this->em->createQuery($dql)->setParameter('celular', $celular);
    }
}