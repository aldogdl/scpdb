<?php

namespace App\Repository\V1\SCP;

use App\Entity\RepoAutos;
use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;
use App\Entity\RepoPzas;
use App\Entity\SisCategos;
use App\Entity\Sistemas;
use App\Entity\StatusTypes;
use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use Doctrine\ORM\EntityManagerInterface;

class RepoEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** from::cp */
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

    /** from::cp */
    public function getContactosByIdUser($id)
    {
        $dql = 'SELECT ct, partial suc.{id} FROM ' . UsContacts::class . ' ct ' .
        'JOIN ct.sucursal suc ' .
        'WHERE ct.user = :id ' .
        'ORDER BY ct.nombre ASC';

        return $this->em->createQuery($dql)->setParameter('id', $id);
    }

    /** from::cp */
    public function updateRepo(array $data)
    {
        $idAdmin = $data['id_admin'];
        $repo= $this->em->find(RepoMain::class, $data['repo_id']);
        $pza = $this->em->find(RepoPzas::class, $data['pzas_id']);
        $stt = $this->em->find(StatusTypes::class, 11);

        if($pza) {
            $pza->setCant($data['pzas_id']);
            $pza->setPieza(strtoupper($data['pzas_pieza']));
            $pza->setLugar($data['pzas_lugar']);
            $pza->setLado($data['pzas_lado']);
            $pza->setPosicion($data['pzas_posicion']);
            $pza->setNotas($data['pzas_notas']);
            $pza->setFotos($data['pzas_fotos']);
            $pza->setPrecioLess($data['pzas_precioLess']);
            $pza->setStatus($stt);
            $this->em->persist($pza);
        }

        $data = $data['info'][0];
        $inf = $this->em->find(RepoPzaInfo::class, $data['info_id']);
        if($inf) {
            $inf->setStatus($stt);
            $inf->setCaracteristicas($data['info_caracteristicas']);
            $inf->setDetalles($data['info_detalles']);
            $inf->setDetalles($data['info_detalles']);
            $inf->setPrecio($data['info_precio']);
            $inf->setCosto($data['info_costo']);
            $inf->setComision($data['info_comision']);
            $inf->setFotos($data['info_fotos']);
            $inf->setSistema($this->em->getPartialReference(Sistemas::class, $data['info_sistema']['id']));
            $inf->setSisCat($this->em->getPartialReference(SisCategos::class, $data['info_catego']['id']));
            $this->em->persist($pza);
        }

        if($repo) {
            $repo->setStatus($stt);
            $repo->setAdmin($this->em->getPartialReference(UsAdmin::class, $idAdmin));
            $this->em->persist($pza);
        }

        try {
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body'] = 'ok';
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['body'] = 'Error, No se guardo el Repositorio';
        }

        return $this->result;
    }

    /**
     * Metodos analizados e integrados al nuevo Sistema Central de Procesamiento (SCP)
    */


    /**
     *  @see AutoparNet/RepoController
    */
    public function getRepoByIdsMainFull(array $idRepo) {

        $dql = 'SELECT repo, partial st.{id, nombre, slug}, a, partial mk.{id}, partial md.{id} FROM ' . RepoMain::class . ' repo '.        
        'join repo.auto a '.
        'join repo.status st '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.id IN (:ids)';

        return $this->em->createQuery($dql)->setParameter('ids', $idRepo);
    }

    /**
     *  @see AutoparNet/RepoController
    */
    public function getRepoMainAndPiezasByIdMain(array $idRepo) {

        $dql = 'SELECT repo, partial st.{id, nombre, slug}, '.
        'a, partial mk.{id}, partial md.{id}, pzas FROM ' . RepoMain::class . ' repo '.        
        'join repo.auto a '.
        'join repo.status st '.
        'join a.marca mk '.
        'join a.modelo md '.
        'left join repo.pzas pzas '.
        'WHERE repo.id IN (:ids)';

        return $this->em->createQuery($dql)->setParameter('ids', $idRepo);
    }

    /**
     * @see AutoparNet/RepoController
     * @see SCP-EYE
    */
    public function getReposAllEnProceso() {

        $dql = 'SELECT partial repo.{id, createdAt}, partial pzas.{id}, partial resp.{id} FROM ' . RepoMain::class . ' repo '.        
        'join repo.pzas pzas '.
        'left join repo.pzaInfo resp '.
        'ORDER BY repo.id DESC';

        return $this->em->createQuery($dql);
    }

    /**
     *  @see AutoparNet/RepoController
    */
    public function getReposEnProcesoByIdUserFull($idUser) {

        $dql = 'SELECT repo, partial ad.{id}, partial st.{id, nombre, slug}, a, partial mk.{id}, partial md.{id} FROM ' . RepoMain::class . ' repo '.        
        'join repo.auto a '.
        'left join repo.admin ad '.
        'join repo.status st WITH st.id IN (:idSts) '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.own = :idUser '.
        'ORDER BY repo.id DESC';

        return $this->em->createQuery($dql)->setParameters([
            'idSts' => [1,2,3,4,5],
            'idUser' => $idUser
        ]);
    }

    /**
     *  @see AutoparNet/RepoController
    */
    public function getAllPiezasByIdRepo($idRepo) {

        $dql = 'SELECT pzas, partial st.{id} FROM ' . RepoPzas::class .' pzas '.
        'JOIN pzas.status st '.
        'WHERE pzas.repo = :idRepo';
        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
    }

    /**
     *  @see AutoparNet/RepoController
    */
    public function getInfoByIdPiezas($idPieza) {

        $dql = 'SELECT info, sis, cat, partial pza.{id}, partial st.{id} FROM ' . RepoPzaInfo::class .' info '.
        'JOIN info.pzas pza '.
        'JOIN info.status st '.
        'LEFT JOIN info.sistema sis '.
        'LEFT JOIN info.sisCat cat '.
        'WHERE info.pzas = :idpzas';
        return $this->em->createQuery($dql)->setParameter('idpzas', $idPieza);
    }

    /**
     *  @see AutoparNet/RepoController
    */
    public function markComoDeleteRepoMain($idMain) {

        $dql = 'UPDATE ' . RepoMain::class . ' repo '.
        'SET repo.status = :cancel '.
        'WHERE repo.id = :idMain';

        $res = $this->em->createQuery($dql)->setParameters([
            'idMain' => $idMain,
            'cancel' => $this->determinarStatusBy('cot', 'cancel_user')
        ])->execute();
        return $this->result;
    }

    /**
     * @see AutoparNet/GetRepoController
    */
    public function getReposAutosByIds($idRepo) {
        
        $ids = explode('-', $idRepo);
        $dql = 'SELECT auto, partial mrk.{id}, partial md.{id} FROM ' . RepoAutos::class .' auto '.
        'JOIN auto.marca mrk '.
        'JOIN auto.modelo md '.
        'WHERE auto.id IN (:ids)';
        return $this->em->createQuery($dql)->setParameter('ids', $ids);
    }

    /**
     * @see AutoparNet/RepoController
    */
    public function crearNewRepoAuto($data)
    {
        $dql = $this->getRepoAutoByFields($data);
        $has = $dql->getResult();
        if($has) {
            $obj = $has[0];
            $cant = $obj->getCantReq();
            $cant = $cant +1;
        }else{
            $obj = new RepoAutos();
            $obj->setMarca($this->em->getPartialReference(AO1Marcas::class, $data['id_marca']));
            $obj->setModelo($this->em->getPartialReference(AO2Modelos::class, $data['id_modelo']));
            $obj->setAnio($data['anio']);
            $obj->setIsNac($data['is_nacional']);
            $cant = 1;
        }

        $obj->setCantReq($cant);

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = $obj->getId();
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'error';
            $this->result['body'] = 'Error al Guardar el Auto en el repositorio';
        }
        return $this->result;
    }

    /**
     * @see AutoparNet/RepoController
    */
    public function crearNewRepoMain($data)
    {
        $obj = null;
        if($data['id'] != 0){
            $dql = $this->getRepoMainById($data['id']);
            $has = $dql->getResult();
            if($has){
                $obj = $has[0];
            }
        }
        if($obj == null) {
            $obj = new RepoMain();
        }
        $obj->setRegType($data['reg_type']);
        $obj->setAuto($this->em->getPartialReference(RepoAutos::class, $data['id_auto']));
        $obj->setVia($data['via']);
        $obj->setOwn($data['own']);
        $status = $this->determinarStatusBy($data['reg_type'], 'incompleta');
        $obj->setStatus( $status );

        try {
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = [
                'id' => $obj->getId(),
                'status_id' => $status->getId()
            ];
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'error';
            $this->result['body'] = 'Error al Guardar el Repositorio';
        }
        return $this->result;
    }

    /** 
     * @see AutoparNet/RepoController
     */
    public function crearRepoPizaForCotizar($pieza)
    {
        $repo= $this->em->find(RepoMain::class, $pieza['repo']);
        $stt = $this->em->find(StatusTypes::class, 2);
        $pza = null;

        if($pieza['id'] != 0) {
            $pza = $this->em->find(RepoPzas::class, $pieza['id']);
        }
        if(!$pza) { $pza = new RepoPzas(); }

        $repo->setStatus($stt);
        $this->em->persist($repo);

        $pza->setRepo($repo);
        $pza->setCant($pieza['cant']);
        $pza->setIdTmp($pieza['idTmp']);
        $pza->setPieza(strtoupper($pieza['pieza']));
        $pza->setLugar($pieza['ubik']);
        $pza->setLado('0');
        $pza->setPosicion($pieza['posicion']);
        $pza->setNotas($pieza['notas']);
        if(count($pza->getFotos()) == 0) {
            $pza->setFotos($pza->getFotos());
        }else{
            $pza->setFotos([]);
        }
        if($pza->getPrecioLess() <= 0) {
            $pza->setPrecioLess(0);
        }
        $pza->setStatus($stt);
        $this->em->persist($pza);

        try {
            $this->em->flush();
            $this->result['body'] = [
                'id' => $pza->getId(),
                'status_id' => $stt->getId()
            ];
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'error';
            $this->result['body'] = 'Error al Guardar la Pieza';
        }

        return $this->result;
    }

    /** */
    public function setRepoPedido($idsInfo)
    {
        $rota = count($idsInfo);
        for ($i=0; $i < $rota; $i++) { 
            $this->changeStatusRepoPzaInfo($idsInfo[$i]['info'], 6);
            $this->changeStatusRepoPza($idsInfo[$i]['pza'], 6);
            $this->changeStatusRepoMain($idsInfo[$i]['main'], 6);
        }
        $this->result = ['abort' => false, 'body' =>'ok'];
        return $this->result;
    }

    /** 
     * @see AutoparNet/RepoController
     */
    public function updateFotoDePieza($idPieza, array $ListFotos)
    {
        $pza = $this->em->find(RepoPzas::class, $idPieza);
        if($pza) {
            $pza->setFotos($ListFotos);
            $this->em->persist($pza);
            try {
                $this->em->flush();
                $this->result['body'] = $pza->getRepo()->getId();
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
                $this->result['msg'] = 'Error al Guardar la Pieza';
                $this->result['body'] = 0;
            }
        }else{
            $this->result['abort'] = true;
            $this->result['msg'] = 'No se encontró la Pieza con ID '. $idPieza;
            $this->result['body'] = 0;
        }
        
        return $this->result;
    }

    /** */
    public function getRepoPiezaInfoByIdRepoMain($idRepo) {

        $dql = 'SELECT info, partial own.{id, nombre, celular}, '.
        'partial suc.{id}, partial emp.{id, nombre}, '.
        'partial pza.{id} FROM ' . RepoPzaInfo::class . ' info '.
        'JOIN info.own own '.
        'JOIN own.sucursal suc '.
        'JOIN suc.empresa emp '.
        'JOIN info.pzas pza '.
        'WHERE info.repo = :idRepo '.
        'ORDER BY info.precio ASC';

        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
    }

    /** 
     * Use::FROM::Interno
     * Obtenemos el auto por medio de todos sus campo,
     */
    public function getRepoAutoByFields($auto)
    {

        $dql = 'SELECT a FROM ' . RepoAutos::class . ' a '.
        'WHERE a.marca = :idMarca AND a.modelo = :idModelo AND a.anio = :anio AND a.isNac = :nac';

        return $this->em->createQuery($dql)->setParameters([
            'idMarca' => $auto['id_marca'],
            'idModelo' => $auto['id_modelo'],
            'anio' => $auto['anio'],
            'nac' => $auto['is_nacional']
        ]);
    }
    
    /**
     * Use::FROM::Interno
    */
    public function determinarStatusBy($tipo, $slug)
    {

        $dql = 'SELECT st FROM ' . StatusTypes::class . ' st '.
        'WHERE st.tipo = :tipo AND st.slug = :slug';

        $result = $this->em->createQuery($dql)->setParameters([
            'tipo' => $tipo, 'slug' => $slug
        ])->getResult();

        if($result) {
            return $result[0];
        }else{
            return $this->em->getPartialReference(StatusTypes::class, 1);
        }
    }

    /**
     * Use::FROM::Interno
    */
    public function getRepoMainById($id)
    {
        $dql = 'SELECT repo FROM ' . RepoMain::class . ' repo '.
        'WHERE repo.id = :id';

        return $this->em->createQuery($dql)->setParameter('id', $id);
    }

    ///
    public function changeStatusRepoMain($idMain, $idStatus) {

        $dql = 'UPDATE ' . RepoMain::class . ' repo ' .
        'SET repo.status = :newStatus '.
        'WHERE repo.id = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idMain,
        ])->execute();
    }

    ///
    public function changeStatusRepoPza($idPza, $idStatus) {

        $dql = 'UPDATE ' . RepoPzas::class . ' pza ' .
        'SET pza.status = :newStatus '.
        'WHERE pza.id = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idPza,
        ])->execute();
    }

    ///
    public function changeStatusRepoPzaInfo($idInfo, $idStatus) {

        $dql = 'UPDATE ' . RepoPzaInfo::class . ' info ' .
        'SET info.status = :newStatus '.
        'WHERE info.id = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idInfo,
        ])->execute();
    }

    ///
    public function changeStatusRepoPzaByIdRepo($idMain, $idStatus) {

        $dql = 'UPDATE ' . RepoPzas::class . ' pza ' .
        'SET pza.status = :newStatus '.
        'WHERE pza.repo = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idMain,
        ])->execute();
    }
    
}
