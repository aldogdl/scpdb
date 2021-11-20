<?php

namespace App\Repository\V1\CpanelWeb;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\RepoAutos;
use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;
use App\Entity\RepoPzas;
use App\Entity\StatusTypes;
use App\Repository\V1\RepositorioEm as V1RepositorioEm;
use Doctrine\ORM\EntityManagerInterface;

class RepositorioEm extends V1RepositorioEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function getAllPiezasByIdRepo($idRepo) {

        $dql = 'SELECT pzas FROM ' . RepoPzas::class .' pzas '.
        'WHERE pzas.repo = :idRepo';
        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
    }

    /** */
    public function getInfoByIdPiezas($idPieza) {

        $dql = 'SELECT info, sis, cat, partial pza.{id} FROM ' . RepoPzaInfo::class .' info '.
        'JOIN info.pzas pza '.
        'LEFT JOIN info.sistema sis '.
        'LEFT JOIN info.sisCat cat '.
        'WHERE info.pzas = :idpzas';
        return $this->em->createQuery($dql)->setParameter('idpzas', $idPieza);
    }

    /** */
    public function getRepoLastByType($type) {

        $dql = 'SELECT repo, st, a, mk, md FROM ' . RepoMain::class . ' repo '.        
        'join repo.auto a '.
        'join repo.status st '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.regType = :type '.
        'ORDER BY repo.id DESC';

        return $this->em->createQuery($dql)->setParameter('type', $type)->setMaxResults(1);
    }

    /** */
    public function getRepoById($idRepo) {

        $dql = 'SELECT repo, st, a, mk, md FROM ' . RepoMain::class . ' repo '.        
        'join repo.auto a '.
        'join repo.status st '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.id = :id';

        return $this->em->createQuery($dql)->setParameter('id', $idRepo);
    }

    /** */
    public function getAllRepoMainParaCotizar($tipo) {

        $dql = 'SELECT partial repo.{id, createdAt, own}, '.
        'partial pzas.{id}, '.
        'partial a.{id, anio, isNac}, '.
        'partial mk.{id, nombre, logo}, partial md.{id, nombre} '.
        ' FROM ' . RepoMain::class . ' repo '.
        'join repo.auto a '.
        'left join repo.pzas pzas '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.regType = :tipo';

        return $this->em->createQuery($dql)->setParameter('tipo', $tipo);
    }

    /** */
    public function getLightAllRepoPiezasByIdRepoMain($idRepoMain)
    {
        $dql = 'SELECT partial pza.{id, cant, pieza, lugar, lado, posicion, notas}, '.
        'partial st.{id, tipo, nombre} '.
        'FROM ' . RepoPzas::class . ' pza '.
        'join pza.status st '.
        'WHERE pza.repo = :idRepoMain';

        return $this->em->createQuery($dql)->setParameter('idRepoMain', $idRepoMain);
    }

    /** */
    public function getDataPzasForWork($idPieza) {

        $dql = 'SELECT partial pza.{id, lugar, notas, fotos}, partial repo.{id} '.
        'FROM ' . RepoPzas::class . ' pza '.
        'JOIN pza.repo repo '.
        'WHERE pza.id = :idPieza';

        return $this->em->createQuery($dql)->setParameter('idPieza', $idPieza);
    }

    /** */
    public function convertTo($dql) {

        $result = $dql->getArrayResult();
        $rota = count($result);
        $newResults = [];
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) {

                $newResults[] = [
                    'repo_id' => $result[$i]['id'],
                    'repo_createdAt' => $result[$i]['createdAt'],
                    'repo_own' => $result[$i]['own'],
                    'a_id' => $result[$i]['auto']['id'],
                    'a_anio' => $result[$i]['auto']['anio'],
                    'a_isNac' => $result[$i]['auto']['isNac'],
                    'mk_id' => $result[$i]['auto']['marca']['id'],
                    'mk_nombre' => $result[$i]['auto']['marca']['nombre'],
                    'mk_logo' => $result[$i]['auto']['marca']['logo'],
                    'md_nombre' => $result[$i]['auto']['modelo']['nombre'],
                    'pzas_cant' => count($result[$i]['pzas']),
                ];
            }
        }
        return $newResults;
    }

    /** */
    public function saveRepoFromC3pio($data)
    {
        $result = $this->updateDataAutoFromC3pio($data['auto']);
        if(!$result['abort']) {

            $idRepo = $data['main']['id'];
            $idAuto = ($result['msg'] == 'change') ? $result['body'] : 0;

            $result = $this->updateDataPiezasFromC3pio($idRepo, $data['piezas']);
            if(!$result['abort']) {
                $result = $this->updateDataMainFromC3pio($idRepo, $idAuto, $result['body']);
            }
        }
        return $result;
    }

    /** */
    public function updateDataAutoFromC3pio($data)
    {
        $result = ['abort' => false, 'msg' => 'ok', 'body' => 'ok'];
        $repoV1 = new V1RepositorioEm($this->em);
        $dql = $repoV1->getRepoCompletoAutoById($data['id']);
        $auto = $dql->getResult();
        
        if($auto) {
            $auto = $auto[0];
            $changeData = true;
            if($auto->getMarca()->getNombre() == $data['marca']) {
                if($auto->getModelo()->getNombre() == $data['modelo']) {
                    if($auto->getAnio() == $data['anio']) {
                        if($auto->getIsNac() == $data['isNac']) {
                            $changeData = false;
                        }
                    }
                }
            }
            if($changeData) {
                $autoNew = [
                    'id_marca'    => $this->getIdMarcaByNombre($data['marca']),
                    'id_modelo'   => $this->getIdModeloByNombre($data['modelo']),
                    'anio'        => $data['anio'],
                    'is_nacional' => $data['isNac'],
                ];
                $result['msg'] = 'change';
                $retorno = $repoV1->crearNewRepoAuto($autoNew);
                $result['body'] = ($retorno['abort']) ? 0 : $retorno['body'];
                $retorno = null;
            }
        }else{
            $result['abort'] = true;
            $result['body'] = 'No se encontró el dato del AUTO';
        }
        return $result;
    }

    /** */
    public function updateDataPiezasFromC3pio($idRepo, $piezas)
    { 
        $result = ['abort' => false, 'msg' => 'ok', 'body' => 'ok'];
        $repoV1 = new V1RepositorioEm($this->em);
        $status = $repoV1->determinarStatusBy('cot', 'analizada_refresh');
        
        $dql = $this->getLightAllRepoPiezasByIdRepoMain($idRepo);
        $pzas = $dql->getResult();
        if($pzas) {
            $nuevas = count($piezas);
            $server = count($pzas);
            for ($n=0; $n < $nuevas; $n++) { 
                for ($s=0; $s < $server; $s++) { 
                    if($pzas[$s]->getId() == $piezas[$n]['id']) {
                        $pzas[$s]->setCant($piezas[$n]['cant']);
                        $pzas[$s]->setPieza($piezas[$n]['pieza']);
                        $pzas[$s]->setLugar($piezas[$n]['lugar']);
                        $pzas[$s]->setLado($piezas[$n]['lado']);
                        $pzas[$s]->setPosicion($piezas[$n]['posicion']);
                        $pzas[$s]->setNotas($piezas[$n]['notas']);
                        $pzas[$s]->setStatus($status);
                        $this->em->persist($pzas[$s]);
                    }
                }
            }
            try {
                $this->em->flush();
                $result['body'] = $status;
            } catch (\Throwable $th) {
                $result['abort'] = true;
                $result['body'] = $th->getMessage();
            }
        }
        return $result;
    }

    /** */
    public function updateDataMainFromC3pio($idRepo, $idAuto, $status)
    {
        $result = ['abort' => false, 'msg' => 'ok', 'body' => 'ok'];
        $repo = $this->em->find(RepoMain::class, $idRepo);

        if($repo) {
            if($idAuto != 0) {
                $repo->setAuto($this->em->getPartialReference(RepoAutos::class, $idAuto));
            }
            $repo->setStatus($status);
            try {
                $this->em->persist($repo);
                $this->em->flush();
                $result['body'] = [
                    'idRepo' => $idRepo,
                    'idAuto' => $repo->getAuto()->getId(),
                    'status' => $repo->getStatus()->getId(),
                    'st_slug'=> $repo->getStatus()->getSlug(),
                ];
            } catch (\Throwable $th) {
                $result['abort'] = true;
                $result['body'] = $th->getMessage();
            }
        }

        return $result;
    }

    /** */
    public function getIdMarcaByNombre($nombreMarca)
    {
        $dql = 'SELECT partial m.{id} FROM ' . AO1Marcas::class . ' m '.
        'WHERE m.nombre = :nombre';
        $res = $this->em->createQuery($dql)->setParameter('nombre', $nombreMarca)->getResult();
        return ($res) ? $res[0]->getId() : 0;
    }

    /** */
    public function getIdModeloByNombre($nombreModelo)
    {
        $dql = 'SELECT partial m.{id} FROM ' . AO2Modelos::class . ' m '.
        'WHERE m.nombre = :nombre';
        $res = $this->em->createQuery($dql)->setParameter('nombre', $nombreModelo)->getResult();
        return ($res) ? $res[0]->getId() : 0;
    }

    /** */
    public function createDataForPushCot($idRepo): array
    {
        $modAnio = ' una marca de auto que manejas ';
        $mrkNac  = '';
        $logo = '0';
        $dql = $this->getRepoById($idRepo);
        $repo = $dql->getResult();
        if($repo){
            $repo  = $repo[0];
            $marca = $repo->getAuto()->getMarca()->getNombre();
            $logo  = $repo->getAuto()->getMarca()->getLogo();
            $modelo= $repo->getAuto()->getModelo()->getNombre();
            $anio  = $repo->getAuto()->getAnio();
            $isNac = ($repo->getAuto()->getIsNac()) ? 'Nacional' : 'Importado';
            $modAnio = '' . $modelo . ' - ' . $anio;
            $mrkNac = '' . $marca . ', ' . $isNac;
            $repo->setStatus(
                $this->em->getPartialReference(StatusTypes::class, 9)
            );
            $this->em->flush();
        }

        $bg = rand(1, 10);
        $bg = ($bg <= 0 || $bg > 10) ? 7 : $bg;
        return [
            'seccion' => 'xcot',
            'id'      => $idRepo,
            'bg'      => $bg.'.jpg',
            'mrk_logo'=> $logo,
            'titulo'  => $modAnio,
            'stitulo' => $mrkNac
        ];
    }
}
