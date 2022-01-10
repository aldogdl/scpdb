<?php

namespace App\Repository\V1\SCP;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;
use App\Entity\RepoPzas;
use App\Entity\SisCategos;
use App\Entity\Sistemas;
use App\Entity\StatusTypes;
use App\Entity\UsContacts;
use App\Repository\V1\SCP\RepoEm;
use Doctrine\ORM\EntityManagerInterface;

class RepoEmCotz extends RepoEm
{

    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /** */
    public function getAllMarcas()
    {
        $dql = 'SELECT mk FROM ' . AO1Marcas::class . ' mk ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllSistems()
    {
        $dql = 'SELECT sis FROM ' . Sistemas::class . ' sis ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllCategos()
    {
        $dql = 'SELECT cat FROM ' . SisCategos::class . ' cat ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllModelos()
    {
        $dql = 'SELECT md, partial mk.{id} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mk '.
        'ORDER BY mk.id ASC';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllStatus()
    {
        $dql = 'SELECT st FROM ' . StatusTypes::class . ' st ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getRepoById(int $idRepo)
    {
        $idStatus = 3;
        $dql = $this->getRepoMainAndPiezasByIdMain([$idRepo]);
        $this->changeStatusRepoPzaByIdRepo($idRepo, $idStatus);
        $this->changeStatusRepoMain($idRepo, $idStatus);
        return $dql;
    }

    /** */
    public function updateFotoDeRespuesta($idInfo, $fotos)
    {
        $dql = $this->getRepoInfoById($idInfo);
        $has = $dql->execute();
        if($has) {
            $obj = $has[0];
            $obj->setFotos($fotos);
            try {
                $this->em->persist($obj);
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['body']  = $obj->getId();
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
                $this->result['body']  = 'Error al guardar las Fotos.';
            }
        } 
        return $this->result;
    }

    /** */
    public function saveDataRespuesta($resp)
    {
        $obj = null;
        $idStatus = 4;

        if(array_key_exists('id_info', $resp)) {
            // Buscamos el registro
            $dql = $this->getRepoInfoById($resp['id_info']);
            $has = $dql->execute(); 
            if($has) {
                $obj = $has[0];
            } 
        }

        if($obj == null) {
            $obj = new RepoPzaInfo();
            $obj->setRepo($this->em->getPartialReference(RepoMain::class, $resp['id_main']));
            $obj->setPzas($this->em->getPartialReference(RepoPzas::class, $resp['idPz']));
            $obj->setStatus($this->em->getPartialReference(StatusTypes::class, $idStatus));
            $obj->setOwn($this->em->getPartialReference(UsContacts::class, $resp['idCt']));
            $obj->setIdTmpPza($resp['idTm']);
        }

        $obj->setCaracteristicas($resp['carac']);
        $obj->setDetalles($resp['deta']);
        $obj->setCosto($resp['costo']);
        $obj->setPrecio($resp['precio']);
        $comi = (float) $resp['precio'] - (float) $resp['costo'];
        $obj->setComision($comi);
        $obj->setSistema($this->em->getPartialReference(Sistemas::class, $resp['sistem']));
        $obj->setSisCat($this->em->getPartialReference(SisCategos::class, $resp['catego']));

        try {

            $this->em->persist($obj);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body']  = [
                'id_info' => $obj->getId(),
                'status'  => $idStatus
            ];
            $this->changeStatusRepoPza($resp['idPz'], $idStatus);
            $this->changeStatusRepoMain($resp['id_main'], $idStatus);

        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['body']  = 'Error al guardar la Respuesta.';
        }

        return $this->result;
    }

    /** */
    public function updateDataFileCluste(array $content): array
    {
        $dql = $this->getRepoPiezaInfoByIdRepoMain($content['id_main']);
        $resp = $dql->getScalarResult();

        $rota = count($resp);
        $cantResp = [];
        $vueltas = count($content['provs']);

        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) {
                $r = [
                    'pza_id' => $resp[$i]['pza_id'],
                    'inf_id' => $resp[$i]['info_id'],
                    'pza_nm' => $resp[$i]['pza_pieza'],
                    'costo'  => $resp[$i]['info_costo'],
                    'status' => [
                        'id' => $resp[$i]['st_id'],
                        'nombre' => $resp[$i]['st_nombre'],
                        'slug' => $resp[$i]['st_slug'],
                    ]
                ];
                if(!in_array($resp[$i]['pza_id'], $cantResp)) {
                    $cantResp[] = $resp[$i]['pza_id'];
                }
                for ($p=0; $p < $vueltas; $p++) { 
                    if($content['provs'][$p]['id'] == $resp[$i]['own_id']) {
                        $cicle = count($content['provs'][$p]['reps']);
                        $existe = false;
                        for ($a=0; $a < $cicle; $a++) { 
                            if($content['provs'][$p]['reps'][$a]['inf_id'] == $r['inf_id']) {
                                $existe = true;
                                $content['provs'][$p]['reps'][$a] = $r;
                                break;
                            }
                        }
                        if(!$existe){
                            $content['provs'][$p]['reps'][] = $r;
                        }
                        break;
                    }
                }
            }
        }

        $content['cant_res'] = (string) count($cantResp);
        return $content;
    }

    /**
     * A las respuestas con status 4 y a las piezas que tengan dichas respuestas,
     * les cambiamos el status a 5, a las otras piezas que no tengan respuestas les
     * dejamos su status intacto.
     */
    public function cStatusToRespToSendPerPza($idMain)
    {
        // Status 4 => piezas con respuestas creadas pero no enviadas
        // Status 5 => Respuestas enviadas al cliente
        $idStatus = 5;

        $status = $this->em->getPartialReference(StatusTypes::class, $idStatus);
        $dql = 'SELECT main, pzs, inf, sts FROM ' . RepoMain::class . ' main '.
        'JOIN main.pzas pzs '.
        'JOIN main.status sts '.
        'LEFT JOIN pzs.info inf '.
        'WHERE main.id = :idMain';

        $result = $this->em->createQuery($dql)->setParameter('idMain', $idMain)->execute();
        $cantPiezas = 0;
        $cantRespondidas = 0;
        if($result) {
            $repo = $result[0];
            $result = null;
            $cantPiezas = count($repo->getPzas());

            $idsPiezasEditar = [];
            for ($i=0; $i < $cantPiezas; $i++) {
                $cantResp = count($repo->getPzas()[$i]->getInfo());
                if($cantResp > 0) {
                    $idsAEditar = [];
                    $cantRespondidas = $cantRespondidas +1;
                    for ($r=0; $r < $cantResp; $r++) { 
                        $idsAEditar[] = $repo->getPzas()[$i]->getInfo()[$r]->getId();
                    }

                    if(count($idsAEditar) > 0) {
                        $dql = 'UPDATE ' . RepoPzaInfo::class . ' infs ' .
                        'SET infs.status = :newStatus '.
                        'WHERE infs.id IN (:ids)';
                        $this->em->createQuery($dql)->setParameters([
                            'newStatus' => $status, 'ids' => $idsAEditar
                        ])->execute();
                    }
                    $idsPiezasEditar[] = $repo->getPzas()[$i]->getId();
                }
            }

            if($idsPiezasEditar) {
                $dql = 'UPDATE ' . RepoPzas::class . ' pzas ' .
                'SET pzas.status = :newStatus '.
                'WHERE pzas.id IN (:ids)';
                $this->em->createQuery($dql)->setParameters([
                    'newStatus' => $status, 'ids' => $idsPiezasEditar
                ])->execute();
            }
            if($cantPiezas == $cantRespondidas) {
                $repo->setStatus($status);
                $this->em->persist($repo);
            }
            $this->em->flush();
        }
    }

    ///
    public function getRepoInfoById($idInfo) {

        $dql = 'SELECT inf FROM ' . RepoPzaInfo::class . ' inf ' .
        'WHERE inf.id = :idInfo';
        return $this->em->createQuery($dql)->setParameter('idInfo', $idInfo);
    }
}