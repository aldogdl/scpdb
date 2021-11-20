<?php

namespace App\Repository\V1;

use App\Entity\UsAdmin;
use App\Entity\UsEmpresa;
use App\Entity\UsEmpresaTipos;
use Doctrine\ORM\EntityManagerInterface;

class UsEmpresaEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Obtenemos las ultimas empresas y sus sucursales
     * @see UsEmpresaController::getLastEmpresasByAVO
     */
    public function getLastEmpresasByAVO($idAvo, $cant)
    {
        $dql = 'SELECT partial em.{id, nombre}, partial t.{id, tipo, role} '.
        'FROM ' . UsEmpresa::class . ' em '.
        'JOIN em.tipo t '.
        'WHERE em.avo = :avo '.
        'ORDER BY em.id DESC';

        return $this->em->createQuery($dql)->setParameter('avo', $idAvo)->setMaxResults($cant);
    }

    /**
     * Buscamos empresas por el criterio enviado, buscando entre el usuario, id o nombre
     * @see UsEmpresaController::searchEmpresasBy
     */
    public function searchEmpresasBy($criterio)
    {
        try {
            $critParse = (integer) $criterio;
            $busqueda = 'WHERE em.id = :busqueda ';
            $tipo = 'IGUAL';
        } catch (\Throwable $th) {
            $critParse = $criterio;
            $busqueda = 'WHERE em.nombre LIKE :busqueda ';
            $tipo = 'LIKE';
        }

        $dql = 'SELECT partial em.{id, nombre, tipo, sucursales}, t, partial suc.{id, domicilio} '.
        'FROM ' . UsEmpresa::class . ' em '.
        'JOIN em.tipo t '.
        'JOIN em.sucursales suc '.
        $busqueda.
        'ORDER BY em.nombre ASC';

        $dqlFinal = $this->em->createQuery($dql);
        if($tipo == 'IGUAL'){
            return $dqlFinal->setParameter('busqueda', $busqueda);
        }else{
            return $dqlFinal->setParameter('busqueda', '%'.$busqueda.'%');
        }
    }

    /**
     * Creamos un registro temporar con la finalidad de obtener
     * un Id unico y proseguir con el registro nuevo
     * @see UsEmpresaController::setEmpresa
     */
    public function setEmpresa($data)
    {
        if($data['emId'] != 0) {
            $dql = $this->getEmpresaById($data['emId']);
            $empresa = $dql->getResult();
            if($empresa) {
                $obj = $empresa[0];
            }
        }else{
            $obj = new UsEmpresa();
            $obj->setAvo($this->em->getPartialReference(UsAdmin::class, $data['avo']['id']));
        }

        $obj->setNombre( $data['emNombre']);
        $obj->setDespeq($data['emDespeq']);
        $obj->setMarcas($data['emMarcas']);
        $obj->setNotas($data['emNotas']);
        $obj->setPagWeb($data['emPagWeb']);
        $obj->setTipo($this->em->getPartialReference(UsEmpresaTipos::class, $data['emTipo']));
        
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'Error';
            $this->result['body'] = 'Error al Guardar la Empresa, Inténtalo nuevamente.';
        }
        return $this->result;
    }

    /**
     * Checamos y guardamos el sub dominio de la empresa
     * @see UsEmpresaController::setSubDominioEmpresa
     */
    public function setSubDominioEmpresa($data)
    {
        $dql = $this->getEmpresaBySlug($data['emSlug']);
        $empresa = $dql->getResult();
        if($empresa) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'Error';
            $this->result['body'] = $data['emSlug'].', esta ocupado.';
        }else{

            $dql = $this->getEmpresaById($data['emId']);
            $empresa = $dql->getResult();
            if($empresa) {
                $obj = $empresa[0];
                $obj->setSlug($data['emSlug']);
    
                try {
                    $this->em->persist($obj);
                    $this->em->flush();
                    $this->result['body'] = $obj->getId();
                } catch (\Throwable $th) {
                    $this->result['abort'] = true;
                    $this->result['msg'] = 'Error';
                    $this->result['body'] = 'Error al Guardar, Inténtalo nuevamente.';
                }
            }else{
                $this->result['abort']= true;
                $this->result['msg']  = 'Error';
                $this->result['body'] = 'No se encontró la empresa indicada.';
            }
        }

        return $this->result;
    }

    /**
     * Optenemos la ultima empresa dada de Alta
     */
    public function getEmpresaByNombre(string $nombre)
    {
        $dql = 'SELECT em FROM ' . UsEmpresa::class . ' em '.
        'WHERE em.nombre = :nombre ';
        return $this->em->createQuery($dql)->setParameter('nombre', trim($nombre));
    }

    /**
     * Optenemos la empresa por medio de su slug
     * @see UsEmpresaController::getEmpresaById
     */
    public function getEmpresaBySlug(string $slug)
    {
        $dql = 'SELECT em, partial t.{id, role} FROM ' . UsEmpresa::class . ' em '.
        'JOIN em.tipo t '.
        'WHERE em.slug = :slug ';
        return $this->em->createQuery($dql)->setParameter('slug', $slug);
    }

    /**
     * Optenemos la empresa por medio de su ID
     * @see UsEmpresaController::getEmpresaById
     */
    public function getEmpresaById(int $idEm)
    {
        $dql = 'SELECT em, partial t.{id} FROM ' . UsEmpresa::class . ' em '.
        'JOIN em.tipo t '.
        'WHERE em.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $idEm);
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

    /** */
    public function setLogosEmpresa($emId, $fotos)
    {   
        $total = count($fotos);
        if($total > 0) {
            $dql = $this->getEmpresaById($emId);
            $empresa = $dql->getResult();
            if($empresa) {
                $empresa = $empresa[0];
                $empresa->setLogo(json_encode($fotos));
                try {
                    $this->em->persist($empresa);
                    $this->em->flush();
                    $this->result['abort'] = false;
                    $this->result['body'] = ''.$total;
                } catch (\Throwable $th) {
                    $this->result['abort'] = true;
                    $this->result['msg'] = 'Error';
                    $this->result['body'] = 'Error al Guardar la Empresa, Inténtalo nuevamente.';
                }
            }
        }
        return $this->result;
    }
}
