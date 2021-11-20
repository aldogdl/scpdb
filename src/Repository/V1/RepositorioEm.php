<?php

namespace App\Repository\V1;

use Symfony\Component\Finder\Finder;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\RepoAutos;
use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;
use App\Entity\RepoPzas;
use App\Entity\StatusTypes;
use App\Entity\UsContacts;
use App\Service\UtilString;
use Doctrine\ORM\EntityManagerInterface;

class RepositorioEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** Obtenemos el auto por medio de todos sus campo */
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

    /** */
    public function getRepoAutoById($idAuto)
    {
        $dql = 'SELECT a FROM ' . RepoAutos::class . ' a '.
        'WHERE a.id = :id';
        return $this->em->createQuery($dql)->setParameter('id', $idAuto);
    }

    /** */
    public function getRepoCompletoAutoById($idAuto)
    {
        $dql = 'SELECT a, mk, md FROM ' . RepoAutos::class . ' a '.
        'JOIN a.marca mk '.
        'JOIN a.modelo md '.
        'WHERE a.id = :id';
        return $this->em->createQuery($dql)->setParameter('id', $idAuto);
    }

    /** */
    public function sumarCantRepoAuto($idAuto)
    {
        $dql = $this->getRepoAutoById($idAuto);
        $has = $dql->getResult();
        if($has) {
            $obj = $has[0];
            $cant = $obj->getCantReq();
            $cant = $cant +1;
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
        }
        return $this->result;
    }

    /** */
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

    /** */
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

    /** */
    public function getAllRepoMainParaCotizar($tipo) {

        $dql = 'SELECT partial repo.{id, createdAt, own}, '.
        'partial a.{id, anio, isNac}, '.
        'partial sts.{id, nombre, slug}, '.
        'partial mk.{id, nombre}, partial md.{id, nombre} '.
        ' FROM ' . RepoMain::class . ' repo '.
        'join repo.auto a '.
        'join repo.status sts '.
        'join a.marca mk '.
        'join a.modelo md '.
        'WHERE repo.regType = :tipo';

        return $this->em->createQuery($dql)->setParameter('tipo', $tipo);
    }

    /** */
    public function getRepoParaCotizar($idsRepo = 0) {

        $dql = 'SELECT partial repo.{id, createdAt, own}, '.
        'partial a.{id, anio, isNac}, '.
        'partial sts.{id, nombre, slug}, '.
        'partial mk.{id, nombre, logo}, '.
        'partial md.{id, nombre} '.
        ' FROM ' . RepoMain::class . ' repo '.
        'join repo.auto a '.
        'join repo.status sts '.
        'join a.marca mk '.
        'join a.modelo md ';

        if($idsRepo != 0) {
            $dql .= 'WHERE repo.id IN (:idRepo)';
            return $this->em->createQuery($dql)->setParameter('idRepo', $idsRepo);
        }else{
            $dql .= 'WHERE repo.status NOT IN (:status) AND repo.regType = :tipo';
            return $this->em->createQuery($dql)->setParameters([
                'status' => [1,2,3,4,5,6,7,12,15,16],
                'tipo' => 'cot',
            ]);
        }
    }

    /** */
    public function getRepoMainById($id) {

        $dql = 'SELECT repo FROM ' . RepoMain::class . ' repo '.
        'WHERE repo.id = :id';

        return $this->em->createQuery($dql)->setParameter('id', $id);
    }
    
    /** */
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

    /** */
    public function saveRepoPiezas($data)
    {
        $dql = $this->getRepoPiezasByIdRepoMain($data[0]['id_repo']);
        $has = $dql->getResult();
        $vueltas = count($has);
        $rota = count($data);

        for ($d=0; $d < $rota; $d++) {

            $obj = null;
            if($vueltas > 0) {
                // Buscando la nueva pieza entre las encontradas.
                for ($h=0; $h < $vueltas; $h++) { 
                    if($has[$h]->getIdTmp() == $data[$d]['id']) {
                        $obj = $has[$h];
                        break;
                    }
                }
            }

            if($obj == null) {
                $obj = new RepoPzas();
                $obj->setRepo($this->em->getPartialReference(RepoMain::class, $data[$d]['id_repo']));
                $obj->setIdTmp($data[$d]['id']);
                // Utilizamos el Status de tipo 'cot', ya que es el mismo para todos en este proceso
                $status = $this->determinarStatusBy('cot', 'analisis');
                $obj->setStatus($status);
            }

            $obj->setCant($data[$d]['cant']);
            $obj->setPieza($data[$d]['pieza']);
            $obj->setLugar($data[$d]['lugar']);
            $obj->setLado($data[$d]['lado']);
            $obj->setPosicion($data[$d]['posicion']);
            $obj->setNotas($data[$d]['notas']);
            $obj->setFotos($data[$d]['fotos']);
            
            $this->em->persist($obj);
        }

        try {
            $this->em->flush();
            $this->result['msg'] = 'saved';
        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['msg'] = 'error';
            $this->result['body'] = 'Error al Guardar el Repositorio Principal';
        }

        return $this->result;
    }

    /**
     * Despues de Guardar las piezas en la BD, aqui podemos recuperar los
     * id nuevos para cada pieza y poderlos guardar en las BD de info y 
     * en el dispositivo movil del usuario
     */
    public function getIdsPiezas($data)
    {
        $cleanData = [];

        $dql = $this->getRepoPiezasByIdRepoMain($data[0]['id_repo']);
        $has = $dql->getResult();
        $vueltas = count($has);
        $rota = count($data);

        for ($d=0; $d < $rota; $d++) {
            if($vueltas > 0) {
                // Buscando la nueva pieza entre las encontradas.
                for ($h=0; $h < $vueltas; $h++) { 
                    if($has[$h]->getIdTmp() == $data[$d]['id']) {
                        $hasInfo = [];
                        if(array_key_exists('info', $data[$d])) {
                            $hasInfo = ( array_key_exists('id', $data[$d]['info'][0]) ) ? $data[$d]['info'][0] : [];
                        }
                        $cleanData[] = [
                            'id'     => $has[$h]->getId(),
                            'id_tmp' => $data[$d]['id'],
                            'id_repo'=> $data[$d]['id_repo'],
                            'status' => $has[$h]->getStatus()->getId(),
                            'info'   => $hasInfo
                        ];
                        break;
                    }
                }
            }
        }

        return $cleanData;
    }

    /** */
    public function saveRepoPiezaInfo($data)
    {
        $rota = count($data);
        if($rota == 0){ return $this->result; }

        if(array_key_exists('info', $data[0])) {
            if(array_key_exists('id', $data[0]['info'])) {

                $dql = $this->getRepoPiezaInfoByIdRepoMain($data[0]['info']['id_repo']);
                $has = $dql->getResult();
                $vueltas = count($has);

                for ($i=0; $i < $rota; $i++) {

                    $obj = null;
                    if($vueltas > 0) {
                        for ($p=0; $p < $vueltas; $p++) { 
                            if($data[$i]['id_tmp'] == $has[$p]->getIdTmpPza()) {

                                // el key take es colocado para evitar duplicidad.
                                if(!array_key_exists('take', $data[$i])){
                                    $obj = $has[$p];
                                    $data[$i]['take'] =  true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if($obj == null) {
                        $obj = new RepoPzaInfo();
                        $obj->setRepo(
                            $this->em->getPartialReference(RepoMain::class, $data[$i]['info']['id_repo'])
                        );
                        $obj->setPzas(
                            $this->em->getPartialReference(RepoPzas::class, $data[$i]['id'])
                        );
                        $obj->setOwn( $this->getContactByIdUser($data[$i]['info']['own']) );
                        $obj->setIdTmpPza($data[$i]['id_tmp']);
                    }

                    $obj->setCaracteristicas($data[$i]['info']['caracteristicas']);
                    $obj->setDetalles($data[$i]['info']['detalles']);
                    $obj->setPrecio($data[$i]['info']['precio']);
                    $obj->setCosto($data[$i]['info']['costo']);
                    $obj->setComision($data[$i]['info']['comision']);
                    $obj->setFotos($data[$i]['info']['fotos']);
                    $obj->setStatus(
                        $this->em->getPartialReference(StatusTypes::class, $data[$i]['status'])
                    );
                    $this->em->persist($obj);
                }

                try {
                    $this->em->flush();
                    $this->result['msg'] = 'saved';
                    $this->result['body'] = [];
                } catch (\Throwable $th) {
                    $this->result['abort'] = true;
                    $this->result['msg'] = 'error';
                    $this->result['body'] = $th->getMessage();// 'Error al Guardar el Repositorio Principal';
                }
        
            }
        }

        return $this->result;
    }

    /** */
    public function saveRepoPiezaInfoXcot($data)
    {
        $rota = count($data);
        if($rota == 0){ return $this->result; }
        $utils = new UtilString();
        $nombreContac = '';
        $nombreEmpresa = '';
        $isInitFiltro = false;

        for ($x=0; $x < $rota; $x++) { 
            if(array_key_exists('id_repo', $data[$x])) {

                $dql = $this->getRepoPiezaInfoByIdRepoMain($data[$x]['id_repo']);
                $has = $dql->getResult();
                $vueltas = count($has);
                $obj = null;
                if($vueltas > 0) {
                    for ($i=0; $i < $vueltas; $i++) { 
                        if($has[$i]->getId() == $data[$x]['id']) {
                            $obj = $has[$i];
                            break;
                        }
                    }
                    if($obj == null) {
                        for ($i=0; $i < $vueltas; $i++) { 
                            if($has[$i]->getIdTmpPza() == $data[$x]['id']) {
                                $obj = $has[$i];
                                break;
                            }
                        }
                    }
                    if($obj != null) {
                        $nombreContac = $obj->getOwn()->getNombre();
                        $nombreEmpresa = $obj->getOwn()->getSucursal()->getEmpresa()->getNombre();
                    }
                }

                if($obj == null) {
                    $obj = new RepoPzaInfo();
                    $obj->setRepo(
                        $this->em->getPartialReference(RepoMain::class, $data[$x]['id_repo'])
                    );
                    $obj->setPzas(
                        $this->em->getPartialReference(RepoPzas::class, $data[$x]['id_pieza'])
                    );
                    $contac = $this->getContactByIdUser($data[$x]['own']);
                    $obj->setOwn( $contac );
                    $obj->setIdTmpPza($data[$x]['id']);
                    $nombreContac = $contac->getNombre();
                    $nombreEmpresa= $contac->getSucursal()->getEmpresa()->getNombre();
                    $contac = null;
                }
                
                if(!$isInitFiltro) {
                    $isInitFiltro = true;
                    $utils->initConfigForQuitarProhividas(
                        array_merge(explode(' ', $nombreEmpresa), explode(' ', $nombreContac))
                    );
                }
                if(strlen($data[$x]['caracteristicas']) > 0) {
                    $txtLong = $utils->quitarPalabrasProhividas($data[$x]['caracteristicas']);
                }else{
                    $txtLong = '0';
                }
                $obj->setCaracteristicas($txtLong);

                if(strlen($data[$x]['detalles']) > 0) {
                    $txtLong = $utils->quitarPalabrasProhividas($data[$x]['detalles']);
                }else{
                    $txtLong = '0';
                }
                $obj->setDetalles($txtLong);
                $obj->setPrecio($data[$x]['precio']);
                $obj->setCosto($data[$x]['costo']);
                $obj->setComision($data[$x]['comision']);
                $obj->setFotos($data[$x]['fotos']);
                $obj->setStatus(
                    $this->em->getPartialReference(StatusTypes::class, 10)
                );
                $this->em->persist($obj);
            }
            
            try {
                $this->em->flush();
                $this->result['msg'] = 'saved';
                $this->result['body'] = [];
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
                $this->result['msg'] = 'error';
                $this->result['body'] = $th->getMessage();// 'Error al Guardar el Repositorio Principal';
            }
    
        }
        return $this->result;
    }

    /** */
    public function getContactByIdUser($idUser) {
        $dql = 'SELECT ct FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.user = :idUser';
        $contact = $this->em->createQuery($dql)->setParameter('idUser', $idUser)->getResult();
        if($contact) {
            return $contact[0];
        }
        return null;
    }

    /** */
    public function getKeyPushContactByIdRepo($idRepo)
    {
        $dql = 'SELECT partial repo.{id, own}, partial ct.{id, notifiKey} FROM ' . RepoMain::class . ' repo '.
        'JOIN '.UsContacts::class.' ct WITH ct.user = repo.own '.
        'WHERE repo.id = :idRepo';
        $contact = $this->em->createQuery($dql)->setParameter('idRepo', $idRepo)->getScalarResult();
        if($contact) {
            return $contact[0];
        }
        return null;
    }

    /** */
    public function checkStatusAndRecoveryDatos($data)
    {
        $vueltas = count($data);
        if($vueltas > 0) {

            $idRepo = $data[0]['id_repo'];
            $dql = $this->getRepoPiezasByIdRepoMain($idRepo);
            $has = $dql->getScalarResult();
            $rota = count($has);
            if($rota > 0) {
                $idsForStatus = [];
                // Primero buscamos por ID temporal
                for ($i=0; $i < $rota; $i++) { 
                    for ($v=0; $v < $vueltas; $v++) { 
                        if($has[$i]['pzas_idTmp'] == $data[$v]['id_tmp']) {
                            if(!array_key_exists('sts', $data[$v])) {
                                $idsForStatus[$has[$i]['pzas_id']] = $has[$i]['pzas_id'];
                                $data[$v]['id'] = $has[$i]['pzas_id'];
                                $data[$v]['sts']= 0;
                            }
                        }
                    }
                }
                
                // Reforzamos buscando por ID normal
                for ($i=0; $i < $rota; $i++) { 
                    for ($v=0; $v < $vueltas; $v++) {
                        if($has[$i]['pzas_id'] == $data[$v]['id']) {
                            if(!array_key_exists('sts', $data[$v])) {
                                $idsForStatus[$data[$v]['id']] = $data[$v]['id'];
                                $data[$v]['id'] = $has[$i]['pzas_id'];
                                $data[$v]['sts']= 0;
                            }
                        }
                    }
                }
                sort($idsForStatus);
            }
            $dql = 'SELECT partial info.{id, pzas, status}, partial pzas.{id}, partial sts.{id} FROM ' . RepoPzaInfo::class . ' info '.
            'JOIN info.pzas pzas '.
            'JOIN info.status sts '.
            'WHERE info.pzas IN (:pzas) '.
            'GROUP BY info.pzas';
            $contact = $this->em->createQuery($dql)->setParameter('pzas', $idsForStatus)->getScalarResult();
            
            if($contact) {
                for ($i=0; $i < $vueltas; $i++) { 
                    $index = array_search($data[$i]['id'], array_column($contact, 'pzas_id'));
                    if($index !== false) {
                        $data[$i]['sts'] = $contact[$index]['sts_id'];
                    }
                }
                return $data;
            }
        }
        return null;
    }

    /** */
    public function getRespCotsBy($idPieza)
    {
        $dql = 'SELECT info, partial own.{id}, partial pzs.{id, idTmp, status}, partial sts.{id, slug} FROM ' . RepoPzaInfo::class . ' info '.
        'JOIN info.pzas pzs '.
        'JOIN info.own own '.
        'JOIN pzs.status sts '.
        'WHERE info.pzas = :idPieza';
        return $this->em->createQuery($dql)->setParameter('idPieza', $idPieza);        
    }

    /** */
    public function getIdsPiezasInfo($idRepo) {

        $dql = $this->getRepoPiezaInfoByIdRepoMain($idRepo);
        $infos = $dql->getResult();
        $rota = count($infos);
        $data = [];
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $data[] = [
                    'id_info' => $infos[$i]->getId(),
                    'id_repo' => $idRepo,
                    'id_tmp_pza' => $infos[$i]->getIdTmpPza(),
                    'id_pza' => $infos[$i]->getPzas()->getId(),
                    'status' => $infos[$i]->getStatus()->getId()
                ];
            }
        }
        return $data;
    }

    /** */
    public function getIdsPiezasInfoFromXcot($idRepo) {

        $dql = $this->getRepoPiezaInfoByIdRepoMain($idRepo);
        $infos = $dql->getResult();
        $rota = count($infos);
        $data = [];
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $data[] = [
                    'id'     => $infos[$i]->getId(),
                    'id_repo'=> $infos[$i]->getRepo()->getId(),
                    'id_tmp' => $infos[$i]->getIdTmpPza(),
                    'status' => $infos[$i]->getStatus()->getId(),
                ];
            }
        }
        return $data;
    }

    /**
     * Obtenemos solo los nombres de las piezas.
    */
    public function getOnlyNamePiezasByIdRepoMain($idRepo) {

        $dql = 'SELECT partial pza.{id, pieza} FROM ' . RepoPzas::class . ' pza '.
        'WHERE pza.repo = :idRepo';

        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
    }

    /** */
    public function getRepoPiezasByIdRepoMain($idRepo) {

        $dql = 'SELECT pzas FROM ' . RepoPzas::class . ' pzas '.
        'WHERE pzas.repo = :idRepo';

        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
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
        'WHERE info.repo = :idRepo';

        return $this->em->createQuery($dql)->setParameter('idRepo', $idRepo);
    }

    /** */
    public function getRepoInfoContestadosBY($idUserProv, $idRepos) {

        $dql = 'SELECT partial info.{id, repo, own}, partial repo.{id}, partial own.{id, user} '.
        'FROM ' . RepoPzaInfo::class . ' info '.
        'JOIN info.own own '.
        'JOIN info.repo repo '.
        'WHERE info.repo IN (:idRepos) AND own.user = :idUser '.
        'GROUP BY info.repo';

        return $this->em->createQuery($dql)->setParameters([
            'idRepos' => $idRepos, 'idUser' => $idUserProv
        ]);
    }

    /** */
    public function updateStatusRepositorio($idRepo, $newStatus) {

        $obj = $this->em->find(RepoMain::class, $idRepo);
        if($obj) {
            $obj->setStatus($newStatus);
            $this->em->persist($obj);
            $this->em->flush();
            $this->result['body'] = 'Nuevo Status ' . $newStatus->getId();
        }else{
            $this->result['body'] = 'No encontro el REPO ' . $idRepo;
        }

        return $this->result;
    }

    /** */
    public function checarFotosInServer($path, $fotos)
    {
        $finder = new Finder();
        $path = realpath($path);
        if(strlen($path) < 10) { return $fotos; }
        
        $finder->files()->in($path);

        $fotosInServer = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $fotosInServer[] = $file->getRelativePathname();
            }
        }

        $fVueltas = count($fotos);
        $noServer = [];
        for ($i=0; $i < $fVueltas; $i++) { 
            if(!in_array($fotos[$i], $fotosInServer)) {
                $noServer[] = $fotos[$i];
            }
        }
        return $noServer;
    }


}
