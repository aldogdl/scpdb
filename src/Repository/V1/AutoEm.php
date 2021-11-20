<?php

namespace App\Repository\V1;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\Auto;
use Doctrine\ORM\EntityManagerInterface;

class AutoEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * 
     */
    public function setOrGetRegAuto($data)
    {
        $dql = $this->getAutoByFields($data);
        $has = $dql->getResult();
        if($has) {
            $obj = $has[0];
            return ['abort' => false, 'msg' => 'get', 'body' => $obj[0]->getId()];
        }
        
        $obj = new Auto();
        $obj->setMarca($this->em->getPartialReference(AO1Marcas::class, $data['id_marca']));
        $obj->setModelo($this->em->getPartialReference(AO2Modelos::class, $data['id_modelo']));
        $obj->setAnio($data['anio']);
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = $obj->getId();
            $this->result['msg'] = 'post';
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'error';
            $this->result['body'] = 'Error al Guardar Registro del Auto';
        }
        return $this->result;
    }

    /** */
    public function getAutoByFields($auto)
    {

        $dql = 'SELECT a FROM ' . Auto::class . ' a '.
        'WHERE a.marca = :idMarca AND a.modelo = :idModelo AND a.anio = :anio';

        return $this->em->createQuery($dql)->setParameters([
            'idMarca' => $auto['id_marca'], 'idModelo' => $auto['id_modelo'], 'anio' => $auto['anio']
        ]);
    }

}
