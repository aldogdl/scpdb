<?php

namespace App\Repository\V1;

use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsAdminEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * hi
     */
    public function crearUserAdmin(
        UserPasswordEncoderInterface $encoder,
        $data, $returnId = false
    ): array
    {
        if (empty($data['username']) || empty($data['password'])){
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! Datos Invalidos';
            return $this->result;
        }
        if(array_key_exists('id', $data)) {
            $dql = $this->getUserByCampo('id', $data['id']);
            $result = $dql->getResult();
            if($result) {
                $user = $result[0];
            }else{
                $this->result['abort'] = true;
                $this->result['body'] = 'ERROR!! No se encontró el Usuario con el ID: ' . $data['id'];
                return $this->result;
            }
        }else{
            $user = new UsAdmin();
        }
        
        $user->setPassword($encoder->encodePassword($user, $data['password']));
        $user->setUsername($data['username']);
        $user->setRoles([$data['role']]);
        try {
            $this->em->persist($user);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = ($returnId)
            ? $user->getId()
            : sprintf('Usuario %s creado satisfactoriamente', $user->getUsername());
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        
        return $this->result;
    }

    /**
     * @see UsAdminController::regUsuariosContacts
     */
    public function regUsuariosContacts(UserPasswordEncoderInterface $encoder, $role, $data): array
    {
        $rota = count($data);
        for ($u=0; $u < $rota; $u++) {
            $dql = $this->getUserByCampo('usernameTOTAL', $data[$u]['user']);
            $result = $dql->getResult();
            if($result) {
                $user = $result[0];
            }else{
                $user = new UsAdmin();
                $pass = (array_key_exists('password', $data)) ? $data['password'] : '1234567';
                $user->setPassword($encoder->encodePassword($user, $pass));
            }
            $user->setUsername($data[$u]['user']);
            $user->setRoles([$role]);
            try {
                $this->em->persist($user);
                $this->em->flush();
                $this->result['abort'] = false;
                $data[$u]['user'] = $user;

            } catch (\Exception $th) {
                $this->result['abort'] = true;
                $this->result['body'] = 'ERROR!! ' . $th->getMessage();
                break;
            }
        }

        $this->result['body'] = $data;
        return $this->result;
    }

    /**
     * hi
     */
    public function editUser(UserPasswordEncoderInterface $encoder, $data): array
    {
        if (empty($data['id']) || empty($data['username'])){
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! Datos Invalidos';
            return $this->result;
        }

        $user = $this->em->find(UsAdmin::class, $data['id']);
        if(array_key_exists('password', $data)) {
            $user->setPassword($encoder->encodePassword($user, $data['password']));
        }
        $user->setUsername($data['username']);
        $user->setRoles([$data['role']]);
        try {
            $this->em->persist($user);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = sprintf('Usuario Administrador %s editado satisfactoriamente', $user->getUsername());
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        
        return $this->result;
    }

    /**
     * hi
     */
    public function existeUsername($username)
    {
        $dql = 'SELECT u FROM ' . UsAdmin::class . ' u '.
        'WHERE u.username = :username';
        
        $res = $this->em->createQuery($dql)
            ->setParameter('username', strtolower(trim($username)))
            ->getArrayResult();
        return (count($res) > 0) ? true : false;
    }

    /**
     * @see $this->regUsuariosContacts
     */
    public function getUserByCampo($campo, $data = 'sinData', $abs = 0)
    {
        $dql = 'SELECT partial u.{id, roles, username} FROM ' . UsAdmin::class . ' u ';
        $order = 'ORDER BY u.username ASC';

        if ($campo == 'all'){
            $dql = $dql . $order;
            return $this->em->createQuery($dql);
        }
        if ($campo == 'id'){
            $dql = $dql . 'WHERE u.id = :valor ' . $order;
            return $this->em->createQuery($dql)->setParameter('valor', $data);
        }
        if ($campo == 'usernameTOTAL'){
            $dql = $dql . 'WHERE u.username = :valor ';
            return $this->em->createQuery($dql)->setParameter('valor', $data);
        }
        if ($campo == 'username' || $campo == 'roles'){
            if($abs == 1) {
                $dql = $dql . 'WHERE u.'.$campo.' = :valor ' . $order;
                return $this->em->createQuery($dql)->setParameter('valor', $data);
            }else{
                $dql = $dql . 'WHERE u.'.$campo.' LIKE :valor ' . $order;
                return $this->em->createQuery($dql)->setParameter('valor', '%' . $data . '%');
            }
        }
    }

    /**
     * @see 
     */
    public function getUserByRole($role)
    {
        if(strpos($role, 'SOCIO_') !== false) {
            $dql = 'SELECT ct, partial u.{id, username, roles} FROM ' . UsContacts::class . ' ct '.
            'JOIN ct.user u '.
            'WHERE u.roles LIKE :role '.
            'ORDER BY ct.nombre ASC';
        }else{
            $dql = 'SELECT partial u.{id, username, roles} FROM ' . UsAdmin::class . ' u '.
            'WHERE u.roles LIKE :role '.
            'ORDER BY u.username ASC';
        }

        return $this->em->createQuery($dql)
        ->setParameter('role', '%_' . $role . '%');
    }

    /**
     * hi
     */
    public function deleteUserByID($idUser)
    {
        $user = $this->em->find(UsAdmin::class, $idUser);
        try {
            $this->em->remove($user);
            $this->em->flush();
            return ['abort' => false, 'msg' => 'ok', 'body' => 'Eliminado con Éxito'];
        } catch (\Throwable $th) {
            return ['abort' => true, 'msg' => 'error', 'body' => $th->getMessage()];
        }
    }

}
