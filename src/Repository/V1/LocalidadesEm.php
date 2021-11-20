<?php

namespace App\Repository\V1;

use App\Entity\LO1Paises;
use App\Entity\LO2Estados;
use App\Entity\LO3Ciudades;
use App\Entity\LO4Localidades;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LocalidadesEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Tomamos todas las localidades junto a sus demás entidades para
     * almacenarlas en C3PIO
     */
    public function getAllLocalidades()
    {
        $localidades = [];
        $dql = 'SELECT pis FROM ' . LO1Paises::class . ' pis ' .
        'ORDER BY pis.id ASC';
        $locs = $this->em->createQuery($dql)->getArrayResult();
        if($locs) {

            $paises = count($locs);
            for ($i=0; $i < $paises; $i++) {

                $localidades[$i] = $locs[$i];

                $dql = 'SELECT edo FROM ' . LO2Estados::class . ' edo ' .
                'WHERE edo.pais = :pais '.
                'ORDER BY edo.nombre ASC';
                $localidades[$i]['edos'] = $this->em->createQuery($dql)->setParameter('pais', $localidades[$i]['id'])->getArrayResult();
                
                $estados = count($localidades[$i]['edos']);
                for ($e=0; $e < $estados; $e++) { 
                    
                    $dql = 'SELECT cd FROM ' . LO3Ciudades::class . ' cd ' .
                    'WHERE cd.estado = :edo '.
                    'ORDER BY cd.nombre ASC';
                    $localidades[$i]['edos'][$e]['cds'] = $this->em->createQuery($dql)->setParameter('edo', $localidades[$i]['edos'][$e]['id'])->getArrayResult();

                    $ciudades = count($localidades[$i]['edos'][$e]['cds']);
                    for ($c=0; $c < $ciudades; $c++) { 
                        
                        $dql = 'SELECT lc FROM ' . LO4Localidades::class . ' lc ' .
                        'WHERE lc.ciudad = :cd '.
                        'ORDER BY lc.nombre ASC';
                        $localidades[$i]['edos'][$e]['cds'][$c]['loc'] = $this->em->createQuery($dql)->setParameter('cd', $localidades[$i]['edos'][$e]['cds'][$c]['id'])->getArrayResult();
                    }
                }
            }
        }
        return $localidades;
    }

    /**
     * Tomamos todas ciudades para los AVOS de la app
     */
    public function getAllCiudades()
    {
        $localidades = [];
        $dql = 'SELECT cd FROM ' . LO3Ciudades::class . ' cd ' .
        'ORDER BY cd.nombre ASC';
        return $this->em->createQuery($dql);
    }

    /**
     * Tomamos todas ciudades para los AVOS de la app
     */
    public function setNewLocalidad($loc)
    {
        $result = ['abort' => false, 'body' => $loc];
        $params = [];
        $params['nombre'] = $loc['nombre'];
        switch ($loc['elemento']) {
            case 'pais':
                $result = $this->setNewPais($params);
                break;
            case 'estado':
                $params['idPais'] = $loc['idPais'];
                $result = $this->setNewEstado($params);
                break;
            case 'ciudad':
                $params['idEdo'] = $loc['idEdo'];
                $result = $this->setNewCiudad($params);
                break;
            case 'colonia':
                $params['idCd'] = $loc['idCd'];
                $params['tipo'] = $loc['tipo'];
                $result = $this->setNewColonia($params);
                break;
            default:
                $msg = 'ALERTA!!!, No se encontré el metodo ' . $loc['elemento'];
                $result = ['abort' => true, 'body' => $msg];
                break;
        }
        return $result;
    }

    /** */
    public function setNewPais(array $params, bool $isEdit = false): array {

        $result = ['abort' => false, 'body' => ''];

        $dql = $this->searchLocByNombre(['nombre' => $params['nombre']], 'pais');
        $hasData = $dql->getResult();
        if($isEdit) {
            if($hasData) {
                $obj = $hasData[0];
            }else{
                $result = ['abort' => true, 'body' => 'El Páis ' . $params['nombre'] . ', no existe'];
                return $result;
            }
        }else{
            if(!$hasData) {
                $obj =new LO1Paises();
            }else{
                $result = ['abort' => true, 'body' => 'El Páis ' . $params['nombre'] . ', ya existe'];
                return $result;
            }
        }
        $obj->setNombre($params['nombre']);
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $result = ['abort' => true, 'body' => 'ERROR!! ' . $th->getMessage()];
        }
        return $result;
    }

    /** */
    public function setNewEstado(array $params, bool $isEdit = false) {

        $result = ['abort' => false, 'body' => ''];

        $dql = $this->searchLocByNombre(
            ['nombre' => $params['nombre'], 'idPais' => $params['idPais']],
            'estado'
        );
        $hasData = $dql->getResult();
        if($isEdit) {
            if($hasData) {
                $obj = $hasData[0];
            }else{
                $result = ['abort' => true, 'body' => 'El Estado ' . $params['nombre'] . ', no existe'];
                return $result;
            }
        }else{
            if(!$hasData) {
                $obj =new LO2Estados();
            }else{
                $result = ['abort' => true, 'body' => 'El Estado ' . $params['nombre'] . ', ya existe'];
                return $result;
            }
        }

        $obj->setNombre($params['nombre']);
        $obj->setPais($this->em->getPartialReference(LO1Paises::class, $params['idPais']));

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $result = ['abort' => true, 'body' => 'ERROR!! ' . $th->getMessage()];
        }
        return $result;
    }

    /** */
    public function setNewCiudad(array $params, bool $isEdit = false) {

        $result = ['abort' => false, 'body' => ''];

        $dql = $this->searchLocByNombre(
            ['nombre' => $params['nombre'], 'idEdo' => $params['idEdo']],
            'ciudad'
        );
        $hasData = $dql->getResult();
        if($isEdit) {
            if($hasData) {
                $obj = $hasData[0];
            }else{
                $result = ['abort' => true, 'body' => 'La Ciudad ' . $params['nombre'] . ', no existe'];
                return $result;
            }
        }else{
            if(!$hasData) {
                $obj =new LO3Ciudades();
            }else{
                $result = ['abort' => true, 'body' => 'La Ciudad ' . $params['nombre'] . ', ya existe'];
                return $result;
            }
        }

        $obj->setNombre($params['nombre']);
        $obj->setEstado($this->em->getPartialReference(LO2Estados::class, $params['idEdo']));

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $result = ['abort' => true, 'body' => 'ERROR!! ' . $th->getMessage()];
        }
        return $result;
    }

    /** */
    public function setNewColonia(array $params, bool $isEdit = false) {

        $result = ['abort' => false, 'body' => ''];

        $dql = $this->searchLocByNombre(
            ['nombre' => $params['nombre'], 'idCd' => $params['idCd']],
            'colonia'
        );
        $hasData = $dql->getResult();
        if($isEdit) {
            if($hasData) {
                $obj = $hasData[0];
            }else{
                $result = ['abort' => true, 'body' => 'La Localidad ' . $params['nombre'] . ', no existe'];
                return $result;
            }
        }else{
            if(!$hasData) {
                $obj =new LO4Localidades();
            }else{
                $result = ['abort' => true, 'body' => 'La Localidad ' . $params['nombre'] . ', ya existe'];
                return $result;
            }
        }

        $obj->setNombre($params['nombre']);
        $obj->setTipo($params['tipo']);
        $obj->setCiudad($this->em->getPartialReference(LO3Ciudades::class, $params['idCd']));

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $result = ['abort' => true, 'body' => 'ERROR!! ' . $th->getMessage()];
        }
        return $result;
    }

    /** */
    public function searchLocByNombre(array $data, String $tipo) {

        $dqlPrefix = 'SELECT l FROM ';
        $dqlSufix  = ' l WHERE l.nombre = :nombre';
        $dql = '';
        $params = ['nombre' => $data['nombre']];
        switch ($tipo) {
            case 'pais'   : $dql = $dqlPrefix . LO1Paises::class . $dqlSufix; break;
            case 'estado' :
                $dql = $dqlPrefix . LO2Estados::class . $dqlSufix;
                $dql = $dql . ' AND l.pais = :idPais';
                $params['idPais'] = $data['idPais'];
            break;
            case 'ciudad' :
                $dql = $dqlPrefix . LO3Ciudades::class . $dqlSufix;
                $dql = $dql . ' AND l.estado = :idEdo';
                $params['idEdo'] = $data['idEdo'];
            break;
            case 'colonia':
                $dql = $dqlPrefix . LO4Localidades::class . $dqlSufix;
                $dql = $dql . ' AND l.ciudad = :idCd';
                $params['idCd'] = $data['idCd'];
            break;
        }
        return $this->em->createQuery($dql)->setParameters($params);
    }
}
