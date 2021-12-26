<?php

namespace App\Controller\Api\FireWalls\Admin\AutoparNet;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\RepoMain;
use App\Entity\RepoPzas;
use App\Services\PushNotifiers;

/**
 * @Route("api/firewalls/autoparnet/repo-post/v{apiVer}/", defaults={"apiVer":"1"})
 */
class PostRepoController extends AbstractFOSRestController
{
    private $em;
    private $repo;
 
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Tomamos el repositorio adecuado para las acciones sobre los modelos, ya que se
     * estan creando diferentes Repositorios es necesario inyectar como servicio el
     * Doctrine\ORM\EntityManagerInterface
     */
    private function getRepo($class, $apiVer) {
        $this->repo = call_user_func_array(
            [$this->em->getRepository($class), 'getV'.$apiVer . 'SCP'],
            [$this->em]
        );
    }
    
    /**
     * :: PASO 1 :: 
     * Construimos el repositorio para el auto y posteriormente el repo main.
     * 
     * @Rest\Post("build-repo-auto-main/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     * Use::FROM::app/web-talleres::CLASS::RepoRepository
    */
    public function buildRepoAutoMain(Request $req, int $apiVer)
    {
        $data = json_decode($req->request->get('data'), true);
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->crearNewRepoAuto($data);
        if(!$result['abort']) {
            $data['id_auto'] = $result['body'];
            $result = $this->repo->crearNewRepoMain($data);
            if(!$result['abort']) {
                $result['body'] = [
                    'id' => $result['body']['id'],
                    'status_id' => $result['body']['status_id'],
                    'auto_id' => $data['id_auto'],
                    'admin_id' => 0,
                    'created_at' => new \DateTime('now'),
                ];
            }
        }
        
        return $this->json($result);
    }

    /**
     * :: PASO 2 ::
     * Guardamos las piezas que estan enviando los clientes para ser cotizadas
     * 
     * @Rest\Post("save-repo-piezas-for-cot/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveRepoPiezasForCot(Request $req, int $apiVer)
    {
        $this->getRepo(RepoPzas::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->crearRepoPizaForCotizar($data);

        return $this->json($result);
    }

    /**
     * @Rest\Post("save-repo-piezas/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveRepoPiezas(Request $req, int $apiVer)
    {
        $this->getRepo(RepoPzas::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        if(array_key_exists('isXcot', $data['piezas'][0])) {
            $result = ['abort' => false];
        }else{
            $result = $this->repo->saveRepoPiezas($data['piezas']);
        }

        if(!$result['abort']) {
            $idRepo = $data['piezas'][0]['id_repo'];
            if(array_key_exists('isXcot', $data['piezas'][0])) {
                $result = $this->repo->saveRepoPiezaInfoXcot($data['piezas'][0]['info']);
                if(!$result['abort']) {
                    $result['body'] = $this->repo->getIdsPiezasInfoFromXcot($idRepo);
                }
            }else{
                $data = $this->repo->getIdsPiezas($data['piezas']);
                
                $result = $this->repo->saveRepoPiezaInfo($data);
                if(!$result['abort']) {
                    $result['body'] = $this->repo->getIdsPiezasInfo($idRepo);
                }
            }
        }

        return $this->json($result);
    }

    /**
     * @Rest\Post("save-foto-to/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveFotoTo(Request $req, int $apiVer)
    {
        // p1 cambio de metodo para subir fotos para solicitud
        $result = ['abort' => false, 'msg' => 'fotos', 'body' => []];
        $this->getRepo(RepoPzas::class, $apiVer);

        $params = json_decode($req->request->get('data'), true);
        file_put_contents('sabe.json', json_encode($params));

        if(array_key_exists('metas', $params)) {

            $pieza = $this->repo->getPiezaById($params['metas']['id_pza']);
            if($pieza) {

                $hoy = new \DateTime('now');
                $fotosCurrent = $pieza->getFotos();

                if(array_key_exists('uped', $params['metas'])) {
                    if($params['metas']['uped'] != '0') {

                        $uriServer = $this->getParameter('toCotizarSh');
                        $uriServer = str_replace('_repomain_', $params['metas']['id_main'], $uriServer);
                        $partes = explode('.', $params['metas']['uped']);
                        $fotoUp = $hoy->getTimestamp() . '.' . $partes[1];
                        if(count($fotosCurrent) < 4) {
                            if(is_file($uriServer.'/'.$params['metas']['uped'])) {

                                $fotosCurrent[] = $fotoUp;
                                $pieza->setFotos($fotosCurrent);
                                $this->em->persist($pieza);
                                try {
                                    $this->em->flush();
                                    $uriServerUp = $this->getParameter('toCotizar');
                                    $uriServerUp = str_replace('_repomain_', $params['metas']['id_main'], $uriServerUp);
    
                                    $saveTo = realpath($uriServerUp);
                                    if($saveTo !== false) {
                                        rename($uriServer.'/'.$params['metas']['uped'], $saveTo.'/'.$fotoUp);
                                        return $this->json([
                                            'abort' => false, 'msg' => 'ok', 'body' => $fotosCurrent
                                        ]);
                                    }
                                } catch (\Throwable $th) {
                                    $result = [
                                        'abort' => false, 'msg' => 'ok', 'body' => 'No se pudo mover la foto indicada'
                                    ];
                                }
                            }
                        }
                    }
                }else{

                    $uriServer = $this->getParameter('toCotizar');
                    $uriServer = str_replace('_repomain_', $params['metas']['id_main'], $uriServer);
                    if(!is_dir($uriServer)) {
                        mkdir($uriServer, 0777, true);
                    }
                    $partes = explode('.', $params['filename']);
                    $fotoUp = $params['filename'];
                    if(in_array($fotoUp, $fotosCurrent)) {
                        $fotoUp = $hoy->getTimestamp() . '.' . $partes[1];
                    }
                    if(count($fotosCurrent) < 4) {
                        $fotosCurrent[] = $fotoUp;
                        $pieza->setFotos($fotosCurrent);
                        $this->em->persist($pieza);
                        try {
                            $this->em->flush();
                            $saveTo = realpath($uriServer);
                            if($saveTo !== false) {
                                $foto = $req->files->get($params['campo']);
                                $foto->move($saveTo, $fotoUp);
                                return $this->json([
                                    'abort' => false, 'msg' => 'ok', 'body' => $fotosCurrent
                                ]);
                            }
                        } catch (\Throwable $th) {
                            $result = [
                                'abort' => true, 'msg' => 'ok', 'body' => 'No se pudo guardar la foto indicada'
                            ];
                        }
                    }
                }

            }else{
                $result = [
                    'abort' => true, 'msg' => 'ok', 'body' => 'No se encontró la pieza con el ID: ' . $params['metas']['id_pza']
                ];
            }
        }else{
            $result = [
                'abort' => true, 'msg' => 'err', 'body' => 'No se enviaron datos METAS'
            ];
        }
        
        return $this->json($result);
    }

    /**
     * @Rest\Post("save-foto-to-share/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveFotoToShare(Request $req, int $apiVer)
    {
        $result = ['abort' => false, 'msg' => 'fotos', 'body' => []];

        $params = json_decode($req->request->get('data'), true);
        if(array_key_exists('metas', $params)) {

            $uriServer = $this->getParameter($params['metas']['uriServer']);
            if($params['metas']['id_main'] != 0) {
                if(strpos($uriServer, '_repomain_') !== false) {
                    $uriServer = str_replace('_repomain_', $params['metas']['id_main'], $uriServer);
                    if(!is_dir($uriServer)) {
                        mkdir($uriServer, 0777, true);
                    }
                    $saveTo = realpath($uriServer);
                    if($saveTo !== false) {
                        $foto = $req->files->get($params['campo']);
                        $foto->move($saveTo, $params['metas']['idTmpPza'].'-'.$params['filename']);
                        $result = [
                            'abort' => false, 'msg' => 'ok', 'body' => $params['filename']
                        ];
                    }
                }
            }else{
                $result = [
                    'abort' => true, 'msg' => 'err', 'body' => 'Sin Identificación de Cotización'
                ];
            }

        }else{
            $result = [
                'abort' => true, 'msg' => 'err', 'body' => 'No se enviaron datos Datos METAS'
            ];
        }
        return $this->json($result);
    }

    /**
     * @Rest\Post("save-foto-to-share-for-respctz/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveFotoToShareForRespCtz(Request $req, int $apiVer)
    {
        $result = ['abort' => false, 'msg' => 'fotos', 'body' => []];

        $params = json_decode($req->request->get('data'), true);
        if(array_key_exists('metas', $params)) {

            $uriServer = $this->getParameter('sharedCtz');
            if($params['metas']['id_main'] != 0) {
                if(strpos($uriServer, '_idPza_') !== false) {
                    $uriServer = str_replace('_idPza_', $params['metas']['id_pza'], $uriServer);
                    if(!is_dir($uriServer)) {
                        mkdir($uriServer, 0777, true);
                    }else{
                        if(array_key_exists('cls', $params['metas'])) {
                            $finder = new Finder();
                            $finder->files()->in($uriServer);
                            if ($finder->hasResults()) {
                                foreach ($finder as $file) {
                                    unlink($file->getRealPath());
                                }
                            }
                        }
                    }
                }
    
                $saveTo = realpath($uriServer);
                if($saveTo !== false) {
                    $foto = $req->files->get($params['campo']);
                    $foto->move($saveTo, $params['filename']);
                    $result = [
                        'abort' => false, 'msg' => 'ok', 'body' => $params['filename']
                    ];
                }
            }else{
                $result = [
                    'abort' => true, 'msg' => 'err', 'body' => 'Sin Identificación de Cotización'
                ];
            }

        }else{
            $result = [
                'abort' => true, 'msg' => 'err', 'body' => 'No se enviaron datos Datos METAS'
            ];
        }
        return $this->json($result);
    }

    /**
     * @Rest\Post("save-repo-pedido/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveRepoPedido(Request $req, int $apiVer)
    {
        $this->getRepo(RepoPzas::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->setRepoPedido($data['data']);
        return $this->json($result);
    }

    /**
     * En revision
     * 
     * @Rest\Post("update-repo/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function updateRepo(Request $req, int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->updateRepo($data);
        return $this->json($result);
    }
}