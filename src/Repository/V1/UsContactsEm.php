<?php

namespace App\Repository\V1;

use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;
use Doctrine\ORM\EntityManagerInterface;

class UsContactsEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function userIsFromEmpresa(array $data): array
    {
        $dql = 'SELECT partial ct.{id, user}, partial suc.{id, empresa}, partial emp.{id} '.
        'FROM ' . UsContacts:: class . ' ct '.
        'JOIN ct.sucursal suc '.
        'JOIN suc.empresa emp '.
        'WHERE ct.user = :idUser';
        $res = $this->em->createQuery($dql)->setParameter('idUser', $data['user'])->getScalarResult();
        if($res){
            $this->result['body'] = ($res[0]['emp_id'] == $data['emp']) ? true : false;
        }else{
            $this->result['abort'] = true;
            $this->result['body'] = false;
        }
        return $this->result;
    }

    /**
     * Creamos un registro temporar con la finalidad de obtener
     * un Id unico y proseguir con el registro nuevo.
     * 
     * @see UsContactsController::setContactos
     */
    public function setContactos($data)
    { 
        $rota = count($data);
        $ids = [];
        for ($c=0; $c < $rota; $c++) {

            // Buscamos primero la existencia del contacto.
            $dql = $this->getContactoById($data[$c]['ctkId']);
            $contac = $dql->getResult();
            if($contac) {
                $obj = $contac[0];
            }else{
                $obj = new UsContacts();
                $obj->setUser($data[$c]['user']);
            }

            $obj->setSucursal($this->em->getPartialReference(UsSucursales::class, $data[$c]['sucId']));
            $obj->setNombre($data[$c]['ctkNombre']);
            $obj->setCelular($data[$c]['ctkCelular']);
            $obj->setCargo($data[$c]['ctkCargo']);
            $obj->setNotifiKey('0');
            $this->em->persist($obj);
            try {
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['msg'] = 'ok';
                $ids[] = ['old' => $data[$c]['ctkId'], 'new' => $obj->getId()];
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
                $this->result['msg'] = $th->getMessage();
                $this->result['body'] = 'Error al Guardar los contactos, Inténtalo nuevamente.';
            }
        }
        if(!$this->result['abort']) {
            $this->result['body'] = $ids;
        }
        return $this->result;
    }

    /** */
    public function getRoleDeSucursalById(int $idSuc): String
    {
        $role = 'nop';
        $dql = 'SELECT partial suc.{id, empresa}, partial t.{id, role} '.
        'FROM ' . UsSucursales::class . ' suc '.
        'JOIN suc.empresa em '.
        'JOIN em.tipo t '.
        'WHERE suc.id = :idSuc';
        $result = $this->em->createQuery($dql)->setParameter('idSuc', $idSuc)->getScalarResult();

        if($result) {
            $role = $result[0]['t_role'];
        }
        return $role;
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getContactosByIdEmp(int $idEm)
    {
        // Obtenemos toda las sucursales de la empresa.
        $dql = 'SELECT partial suc.{id} FROM ' . UsSucursales::class . ' suc '.
        'WHERE suc.empresa = :idEm ';
        $sucursales = $this->em->createQuery($dql)->setParameter('idEm', $idEm)->getArrayResult();
        $rota = count($sucursales);
        $sucs = [];
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $sucs[] = $sucursales[$i]['id'];
            }
        }

        $dql = 'SELECT ctk, partial suc.{id, domicilio}, partial user.{id, username} FROM ' . UsContacts::class . ' ctk '.
        'JOIN ctk.sucursal suc '.
        'JOIN ctk.user user '.
        'WHERE ctk.sucursal IN (:sucs) ';
        return $this->em->createQuery($dql)->setParameter('sucs', $sucs);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getDataTarjetaDig(int $idUser)
    {
        $dql = 'SELECT ctk, partial suc.{id, domicilio, telefono, fachada, latLng, palclas}, '.
        'partial emp.{id, nombre, despeq, logo, pagWeb, slug} FROM ' . UsContacts::class . ' ctk '.
        'JOIN ctk.sucursal suc '.
        'JOIN suc.empresa emp '.
        'WHERE ctk.user = :idUser ';
        return $this->em->createQuery($dql)->setParameter('idUser', $idUser);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getDataTarjetaDigBySlug(string $slug)
    {
        $dql = 'SELECT ctk, partial suc.{id, domicilio, telefono, fachada, latLng, palclas}, '.
        'partial emp.{id, nombre, despeq, logo, pagWeb, slug} FROM ' . UsContacts::class . ' ctk '.
        'JOIN ctk.sucursal suc '.
        'JOIN suc.empresa emp '.
        'WHERE emp.slug = :slug ';
        return $this->em->createQuery($dql)->setParameter('slug', $slug);
    }

    /** */
    public function prepareDataForTarjeta($user, $contac)
    {
        unset($contac['ctk_notifiKey']);
        $newData = [
            'u_id' => $user['u_id'],
            'u_username' => $user['u_username'],
            'u_roles' => $user['u_roles'][0]
        ];
        $newData = array_merge($newData, $contac);
        if(strpos($newData['suc_fachada'], '[') !== false) {
            $newData['suc_fachada'] = json_decode($newData['suc_fachada'], true);
            $newData['suc_fachada'] = $newData['suc_fachada'][0];
        }
        if(strpos($newData['emp_logo'], '[') !== false) {
            $newData['emp_logo'] = json_decode($newData['emp_logo'], true);
            $newData['emp_logo'] = $newData['emp_logo'][0];
        }
        
        if(strpos($newData['suc_palclas'], ',')) {
            $partes = explode(',', $newData['suc_palclas']);
            $rota = count($partes);
            if($rota > 3) {
                for ($p=0; $p < $rota; $p++) { 
                    $palclas[] = strtoupper($partes[$p]);
                }
            }else{
                $palclas = str_replace($newData['suc_palclas'], ',', ', ');
                $palclas = strtoupper($palclas);
            }
        }
        if($newData['u_id'] == '0') {
            $dql = $this->getUserByIdContact($newData['ctk_id']);
            $user = $dql->getArrayResult();
            if($user) {
                $newData['u_id'] = $user[0]['user']['id'];
                $newData['u_username'] = $user[0]['user']['username'];
                $newData['u_roles'] = $user[0]['user']['roles'][0];
            }
        }
        return $newData;
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getContactoByIdUser(int $idUser)
    {
        $dql = 'SELECT ctk FROM ' . UsContacts::class . ' ctk '.
        'WHERE ctk.user = :idUser ';
        return $this->em->createQuery($dql)->setParameter('idUser', $idUser);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getContactoById(int $idCtk)
    {
        $dql = 'SELECT ctk FROM ' . UsContacts::class . ' ctk '.
        'WHERE ctk.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $idCtk);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getUserByIdContact(int $idCtk)
    {
        $dql = 'SELECT ctk, partial u.{id, username, roles} FROM ' . UsContacts::class . ' ctk '.
        'JOIN ctk.user u '.
        'WHERE ctk.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $idCtk);
    }

    /**
     * Optenemos los registros de los contactos por sus ids
     */
    public function getContactoByIds(array $idsCtk)
    {
        $dql = 'SELECT ctk FROM ' . UsContacts::class . ' ctk '.
        'WHERE ctk.id IN(:ids) ';
        return $this->em->createQuery($dql)->setParameter('ids', $idsCtk);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getTokenDeviceByIdContact(int $idCtk)
    {
        $dql = 'SELECT partial ctk.{id, notifiKey} FROM ' . UsContacts::class . ' ctk '.
        'WHERE ctk.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $idCtk);
    }

    /**
     * Optenemos el registro temporal en caso de haberse creado
     */
    public function getContactoByCelular($celular)
    {
        $dql = 'SELECT ctk FROM ' . UsContacts::class . ' ctk '.
        'WHERE ctk.celular = :celular ';
        return $this->em->createQuery($dql)->setParameter('celular', $celular);
    }

    /**
     * Optenemos la ultima empresa dada de Alta
     */
    public function getLastItem()
    {
        $dql = 'SELECT em FROM ' . UsEmpresa::class . ' em '.
        'ORDER BY em.id DESC';
        return $this->em->createQuery($dql)->getMaxResults(1);
    }

    /**
     * Actualizamos el token del dispositivo.
     */
    public function updateTokenDevice($data)
    {
        $dql = $this->getContactoByIdUser($data['id']);
        $user = $dql->execute();
        if($user) {
            $obj = $user[0];
            $obj->setNotifiKey($data['token']);
            $this->em->persist($obj);
            $this->em->flush();
        }
    }
}
