<?php

namespace App\Repository\V1;

use App\Entity\LO3Ciudades;
use App\Entity\LO4Localidades;
use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;
use Doctrine\ORM\EntityManagerInterface;

class UsSucursalesEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Creamos un registro temporar con la finalidad de obtener
     * un Id unico y proseguir con el registro nuevo
     */
    public function setSucursales($data)
    {
        $emp = null;
        $locTmp = null;
        $resultados = [];

        $dql = $this->getSucursalesByIdEmpresa($data[0]['emId']);
        $empresas = $dql->getResult();
        $rotaEmp = count($empresas);
        
        $rota = count($data);
        for ($s=0; $s < $rota; $s++) {
            $isNewReg = true;
            if($rotaEmp > 0) {
                for ($h=0; $h < $rotaEmp; $h++) { 
                    if($empresas[$h]->getId() == $data[$s]['sucId']){
                        $obj = $empresas[$h];
                        $isNewReg = false;
                        break;
                    }
                }
            }
            if($isNewReg) {
                $emp = $this->em->getPartialReference(UsEmpresa::class, $data[$s]['emId']);
                $obj = new UsSucursales();
                $obj->setEmpresa($emp);
            }
            
            $locTmp = $this->determinarLocalidad($data[$s]['sucLocalidad']);
            $obj->setLocalidad($locTmp);
            $obj->setDomicilio($data[$s]['sucDomicilio']);
            $obj->setEntreAntes($data[$s]['sucEntreAntes']);
            $obj->setEntreDespues($data[$s]['sucEntreDespues']);
            $obj->setReferencias($data[$s]['sucReferencias']);
            $obj->setTelefono($data[$s]['sucTelefono']);
            $obj->setPalclas($data[$s]['sucPalclas']);
            try {
                $this->em->persist($obj);
                $this->em->flush();
                $resultados[] = ['old' => $data[$s]['sucId'], 'new' => $obj->getId()];
            } catch (\Throwable $th) {
                $this->result['abort']= true;
                $this->result['msg']  = 'Error';
                $this->result['body'] = 'Error al Guardar la Sucursal: ' . $data[$s]['sucId'];
                break;
            }
        }

        $this->result['body'] = $resultados;
        return $this->result;
    }

    /** */
    private function determinarLocalidad(array $localidad): LO4Localidades
    {
        $prefix = 'CD:';

        $dql = 'SELECT loc FROM ' . LO4Localidades::class . ' loc '.
        'WHERE loc.nombre = :nom AND loc.tipo = :tmp';
        $loc = $this->em->createQuery($dql)->setParameters([
            'nom' => $prefix.$localidad['cd_nombre'],
            'tmp'  => 'TMP'
        ])->getResult();
        if($loc) { return $loc[0]; }

        // Si no existe la localidad la creamos.
        $obj = new LO4Localidades();
        $obj->setNombre($prefix.$localidad['cd_nombre']);
        $obj->setTipo('TMP');
        $obj->setCiudad($this->em->getPartialReference(LO3Ciudades::class, $localidad['cd_id']));
        try {
            $this->em->persist($obj);
            $this->em->flush();
            $loc = $obj;
        } catch (\Throwable $th) {
            $loc = $this->em->getPartialReference(LO3Ciudades::class, 1);
        }
        return $loc;
    }

    /**
     * Optenemos la ultima empresa dada de Alta
     */
    public function getSucursalesByIdEmpresa(int $idEmp, $order = 'DESC')
    {
        $dql = 'SELECT suc, cd, partial em.{id, nombre} FROM ' . UsSucursales::class . ' suc '.
        'JOIN suc.empresa em '.
        'JOIN suc.localidad cd '.
        'WHERE suc.empresa = :idEm '.
        'ORDER BY suc.id ' .$order;
        return $this->em->createQuery($dql)->setParameter('idEm', $idEmp);
    }

    /**
     * Optenemos la ultima Sucursal por su ID
     */
    public function getSucursalesById(int $id)
    {
        $dql = 'SELECT suc FROM ' . UsSucursales::class . ' suc '.
        'WHERE suc.id = :id ';
        return $this->em->createQuery($dql)->setParameter('id', $id);
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
    public function setFachadasSucursal($emId, $fotos)
    {   
        $dql = $this->getSucursalesByIdEmpresa($emId);
        $sucursales = $dql->getResult();
        $totalFotos = 0;
        if($sucursales) {
            $rota = count($sucursales);
            $fotosRota = count($fotos);

            for ($s=0; $s < $rota; $s++) {
                $fotosIn = [];
                $fachadas = $sucursales[$s]->getFachada();
                if(strpos($fachadas, '[') !== false) {
                    $fotosIn = json_decode($fachadas, true);
                }
                if(count($fotosIn) == 0){
                    if(strpos($fachadas, '.') !== false) {
                        $fotosIn[] = $fachadas;
                    }
                }

                $sucId = $sucursales[$s]->getId();
                for ($f=0; $f < $fotosRota; $f++) { 
                    if($fotos[$f]['metas']['sucId'] == $sucId) {
                        $fotosIn[] = $fotos[$f]['filename'];
                    }
                }
                $hasFotos = count($fotosIn);
                if($hasFotos > 0) {
                    $totalFotos = $totalFotos + $hasFotos;
                    $sucursales[$s]->setFachada(json_encode($fotosIn));
                    $this->em->persist($sucursales[$s]);
                }
            }

            try {
                $this->em->flush();
                $this->result['abort']= false;
                $this->result['msg']  = 'ok';
                $this->result['body'] = $totalFotos;
            } catch (\Throwable $th) {
                $this->result['abort']= true;
                $this->result['msg']  = 'Error';
                $this->result['body'] = 'Error al Guardar las Fachadas.';
            }
        }
        return $this->result;
    }
}
