<?php

namespace App\Repository\V1;

use App\Entity\Publicaciones;
use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;
use Doctrine\ORM\EntityManagerInterface;

class PublicacionesEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function setPublicacion($publi)
    {
        $obj = null;
        if(array_key_exists('idPub', $publi)) {
            if($publi['idPub'] != 0) {
                $dql = $this->getPublicacionById($publi['idPub']);
                $pubs = $dql->getResult();
                if($pubs) {
                    $obj = $pubs[0];
                }else{
                    $this->result['abort'] = true;
                    $this->result['body'] = 'Error::No se encontró la Publicación solicitada';
                    return $this->result;
                }
                $pubs = null;
            }
        }

        if($obj == null) {
            $obj = new Publicaciones();
            $obj->setUser($this->em->getPartialReference(UsAdmin::class, $publi['user']));
            $dql = $this->getContactByIdUser($publi['user']);
            $item = $dql->getResult();
            if($item) {
                $obj->setContact($item[0]);
            }else{
                $this->result['abort'] = true;
                $this->result['body'] = 'Error::El Contacto no se encontró en los Registros';
            }
            $dql = $this->getEmpresaByIdSucursal($item[0]->getSucursal()->getId());
            $item = $dql->getResult();
            if($item) {
                $obj->setEmp($this->em->getPartialReference(UsEmpresa::class, $item[0]->getEmpresa()->getId()));
            }else{
                $this->result['abort'] = true;
                $this->result['body'] = 'Error::La Empresa no se encontró en los Registros';
            }
        }
        $obj->setTitulo($publi['tit']);
        $obj->setSTitulo($publi['subt']);
        $obj->setDescr($publi['desc']);
        $obj->setCatego($publi['catego']);
        $obj->setConx($publi['conx']);
        $fotos = $obj->getFotos();
        $rota = count($fotos);
        if($rota > 0) {
            $rota = count($publi['fotos']);
            for ($f=0; $f < $rota; $f++) { 
                $fotos[] = $publi['fotos'][$f];
            }
        }else{
            $fotos = $publi['fotos'];
        }
        $obj->setFotos($fotos);
        if(array_key_exists('costo', $publi)) {
            $obj->setCosto((float) $publi['costo']);
        }
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $this->result['abort'] = false;
            $this->result['body'] = $th->getMessage(); //'Error::No se guardó la Publicación, inténtalo nuevamente por favor';
        }
        return $this->result;
    }

    /** */
    public function toggleActivatePublicacion($idPub, $action)
    {
        $dql = 'UPDATE ' . Publicaciones::class . ' pb '.
        'SET pb.isPub = :newVal '.
        'WHERE pb = :idPub';
        return $this->em->createQuery($dql)->setParameters([
            'idPub' => $idPub,
            'newVal' => ($action == 1) ? true : false
        ])->execute();
    }

    /** */
    public function getPublicacionById($idPub)
    {
        $dql = 'SELECT pb FROM ' . Publicaciones::class . ' pb '.
        'WHERE pb = :idPub';
        return $this->em->createQuery($dql)->setParameter('idPub', $idPub);
    }

    /** */
    public function getAllPubsByIdEmp($idEmp)
    {
        $dql = 'SELECT pb, partial ctc.{id, nombre}, partial suc.{id, domicilio} FROM ' . Publicaciones::class . ' pb '.
        'JOIN pb.contact ctc '.
        'JOIN ctc.sucursal suc '.
        'WHERE pb.isDelete = 0 AND pb.emp = :idEmp '.
        'ORDER BY pb.id DESC';
        return $this->em->createQuery($dql)->setParameter('idEmp', $idEmp);
    }

    /** */
    public function getAllPubsByIdUser($idUser)
    {
        $dql = 'SELECT pb, partial ctc.{id, nombre}, partial suc.{id, domicilio} FROM ' . Publicaciones::class . ' pb '.
        'JOIN pb.contact ctc '.
        'JOIN ctc.sucursal suc '.
        'WHERE pb.isDelete = 0 AND pb.user = :idUser OR pb.emp = pb.emp '.
        'ORDER BY pb.user ASC';
        return $this->em->createQuery($dql)->setParameter('idUser', $idUser);
    }

    /** */
    public function deleteImagePublicacion($idPub, $foto) {

        $dql = $this->getPublicacionById($idPub);
        $obj = $dql->getResult();
        if($obj) {
            $fotos = $obj[0]->getFotos();
            $newsFotos = [];
            $rota = count($fotos);
            for ($f=0; $f < $rota; $f++) { 
                if($fotos[$f] != $foto) {
                    $newsFotos[] = $fotos[$f];
                }
            }
            try {
                $obj[0]->setFotos($newsFotos);
                $this->em->persist($obj[0]);
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['body'] = 'ók';
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
            }
            
        }else{
            $this->result['abort'] = true;
            $this->result['body'] = 'No se encontró la foto';
        }

        return $this->result;
    }

    /** */
    public function deletePublicacion($idPub) {

        $dql = $this->getPublicacionById($idPub);
        $obj = $dql->getResult();
        if($obj) {
            try {
                $this->em->remove($obj[0]);
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['body'] = 'ok';
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
            }
            
        }else{
            $this->result['abort'] = true;
            $this->result['body'] = 'No se encontró la Publicación';
        }

        return $this->result;
    }

    /** */
    public function getContactByIdUser($idUser)
    {
        $dql = 'SELECT ct, partial suc.{id} FROM ' . UsContacts::class . ' ct '.
        'JOIN ct.sucursal suc '.
        'WHERE ct.user = :user';
        return $this->em->createQuery($dql)->setParameter('user', $idUser);
    }

    /** */
    public function getEmpresaByIdSucursal($idSuc)
    {
        $dql = 'SELECT suc, partial emp.{id} FROM ' . UsSucursales::class . ' suc '.
        'JOIN suc.empresa emp '.
        'WHERE suc.id = :idSuc';
        return $this->em->createQuery($dql)->setParameter('idSuc', $idSuc);
    }

    /** */
    public function toArray(Publicaciones $obj, $withSubString = false)
    {   
        return [
            'pb_id'     => $obj->getId(),
            'pb_titulo' => $obj->getTitulo(),
            'pb_sTitulo'=> $obj->getSTitulo(),
            'pb_descr'  => $obj->getDescr(),
            'pb_costo'  => $obj->getCosto(),
            'pb_catego' => $obj->getCatego(),
            'pb_visitas'=> $obj->getVisitas(),
            'pb_pubAt'  => $obj->getPubAt(),
            'pb_isPub'  => $obj->getIsPub(),
            'pb_user'   => $obj->getUser()->getId(),
            'ctc_nombre'=> $obj->getContact()->getNombre(),
            'pb_fotos'  => $obj->getFotos(),
            'suc_domicilio' => $obj->getContact()->getSucursal()->getDomicilio()
        ];
    }
}
