<?php

namespace App\Repository\V1;

use App\Entity\AO3Tipos;
use Doctrine\ORM\EntityManagerInterface;

class AO3TiposEm
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
    public function saveDataFromArray($dataArray): array
    {
        $rota = count($dataArray);
        if($rota == 0) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! Sin Datos a Guardar.';
        }
        for ($i=0; $i < $rota; $i++) { 
            $tipo = new AO3Tipos();
            $tipo->setTipo($dataArray[$i]['nombre']);
            $tipo->setAvatar($dataArray[$i]['img'] . '.' . $dataArray[$i]['ext']);
            $this->em->persist($tipo);
        }

        try {
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = 'La Tabla Tipos fué Hidratada satisfactoriamente';
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        
        return $this->result;
    }

    /**
     * Retormanos todos los tipos existentes
     */
    public function getAllTipos($data)
    {
        $dql = 'SELECT t FROM ' . AO3Tipos::class . ' t '.
        'ORDER BY t.tipo ASC';
        
        return $this->em->createQuery($dql);
    }

    /**
     * hi
     */
    public function editarFromId($data): array
    {
        if(array_key_exists('id', $data)) {
            $tipo = $this->em->find(AO3Tipos::class, $data['id']);
        }else{
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! No se indico el Tipo requerido.';
            return $this->result;
        }

        $tipo->setTipo($data['nombre']);
        $tipo->setAvatar($data['img'] . '.' . $data['ext']);
        try {
            $this->em->persist($tipo);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = sprintf('El Tipos %s se Edito satisfactoriamente', $data['nombre']);
        } catch (\Exception $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'ERROR!! ' . $th->getMessage();
        }
        
        return $this->result;
    }
}
