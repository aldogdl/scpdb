<?php

namespace App\Repository\V1;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use Doctrine\ORM\EntityManagerInterface;

class AO2ModelosEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** 
     * @see $this->setMarcasFirstTime
     * @see publicas::AO2ModelosController::getAllMarcas
    */
    public function getAllModelos()
    {
        $dql = 'SELECT mds FROM ' . AO2Modelos::class . ' mds '.
        'ORDER BY mds.nombre ASC';
        return $this->em->createQuery($dql);
    }

    /** */
    public function getModeloByIdMarcaAndNombre($marca, $nombre)
    {
        $dql = 'SELECT mds FROM ' . AO2Modelos::class . ' mds '.
        'WHERE mds.marca = :idMarca AND mds.nombre = :modelo';
        return $this->em->createQuery($dql)->setParameters([
            'idMarca' => $marca, 'modelo' => $nombre
        ]);
    }

    /**
     * publicas::AO2ModelosController::getModelosByIdMarca
    */
    public function getModelosByIdMarca($idMarca)
    {
        $dql = 'SELECT partial md.{id, nombre}, partial mrk.{id} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mrk '.
        'WHERE md.marca = :idMarca '.
        'ORDER BY md.nombre ASC';

        return $this->em->createQuery($dql)->setParameters([
            'idMarca' => $idMarca
        ]);
    }

    /**
     * publicas::AO2ModelosController::getModelosByIdsMarca
    */
    public function getModelosByIdsMarcas($idsMarca)
    {
        $ids = explode('-', $idsMarca);
        $dql = 'SELECT partial md.{id, nombre}, partial mrk.{id} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mrk '.
        'WHERE md.marca IN (:idsMarca) '.
        'ORDER BY md.nombre ASC';

        return $this->em->createQuery($dql)->setParameter('idsMarca', $ids);
    }

    /**
     * publicas::AO2ModelosController::getModelosByIdsMarca
    */
    public function getModeloById($id)
    {
        $dql = 'SELECT partial md.{id, nombre}, partial mrk.{id, nombre, logo} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mrk '.
        'WHERE md.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $id);
    }

    /** */
    public function setNewsModelos($idMarca, $modelos)
    {
        $dql = $this->getModelosByIdMarca($idMarca);
        $mds = $dql->getScalarResult();
        $rota = count($modelos);
        $save = false;
        $laMarca = $this->em->getPartialReference(AO1Marcas::class, $idMarca);
        if(count($mds) > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $key = array_search($modelos[$i], array_column($mds, 'md_nombre'));
                if($key === false) {
                    $obj = new AO2Modelos();
                    $obj->setNombre($modelos[$i]);
                    $obj->setMarca($laMarca);
                    $this->em->persist($obj);
                    $save = true;
                }
            }
        }else{
            // Significa que la marca no cuenta con modelos.
            for ($i=0; $i < $rota; $i++) { 
                $obj = new AO2Modelos();
                $obj->setNombre($modelos[$i]);
                $obj->setMarca($laMarca);
                $this->em->persist($obj);
                $save = true;
            }
        }
        if($save) {
            try {
                $this->em->flush();
            } catch (\Throwable $th) {
                return ['abort' => true, 'body' => 'No se Guardaron los datos'];
            }
        }

        return ['abort' => false, 'body' => []];
    }
}