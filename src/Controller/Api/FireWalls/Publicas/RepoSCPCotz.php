<?php

namespace App\Controller\Api\FireWalls\Publicas;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\RepoMain;
use App\Entity\UsEmpresa;
use App\Services\PushNotifiers;

/**
 * Clase utilizada desde SCP-EYE, donde por el momento no se cuenta con un sistema
 * de alamacenamiento para el token server, por lo tanto deben ser publicos estos
 * endPoints.
 * 
 * @Route("api/firewalls/publicas/repo_scp_cotz/v{apiVer}/", defaults={"apiVer":"1"})
 */
class RepoSCPCotz extends AbstractFOSRestController
{
    private $em;
    private $repo;
    private $push;
    
    public function __construct(EntityManagerInterface $entityManager, PushNotifiers $push)
    {
        $this->em = $entityManager;
        $this->push = $push;
    }

    /**
     * Tomamos el repositorio adecuado para las acciones sobre los modelos, ya que se
     * estan creando diferentes Repositorios es necesario inyectar como servicio el
     * Doctrine\ORM\EntityManagerInterface
     */
    private function getRepo($class, $apiVer) {

        $this->repo = call_user_func_array(
            [$this->em->getRepository($class), 'getV'.$apiVer . 'SCPCotz'],
            [$this->em]
        );
    }
    
    /**
     * @Rest\Get("get-all-marcas/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllMarcas(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->getAllMarcas();
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-all-modelos/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllModelos(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->getAllModelos();
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-all-status/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllStatus(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->getAllStatus();
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-all-sistems/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllSistems(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->getAllSistems();
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-all-categos/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllCategos(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->getAllCategos();
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-repo-by-id/{idRepo}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getRepoById(int $apiVer, $idRepo)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getRepoById($idRepo);
        $result = $dql->getArrayResult();
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-own-by-id/{idUser}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getOwnById(int $apiVer, $idUser)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getOwnById($idUser);
        $result = $dql->getArrayResult();
        return $this->json($result);
    }
    
    /**
     * Este metodo es para adicionar a la BD los datos basicos de un proveedor desde
     * la SCP, con la finalidad de darlo de alta rapidamente y poder continuar con la
     * solicitud de cotizacion.
     * 
     * @Rest\Post("add-prov-basico/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function addProvBasico(Request $request, UserPasswordEncoderInterface $encoder, $apiVer)
    {
        $data = json_decode($request->request->get('data'), true);
        $this->getRepo(UsEmpresa::class, $apiVer);
        $result = $this->repo->addProvBasicoUser($encoder, $data);
        if(!$result['abort']) {
            $ids = ['idUser' => $result['body']];
            $result = $this->repo->addProvBasicoEmp($data);
            if(!$result['abort']) {
                $ids['emp'] = $result['body'];
                $result = $this->repo->addProvBasicoSuc($ids['emp'], $data);
                if(!$result['abort']) {
                    $ids['suc'] = $result['body'];
                    $result = $this->repo->addProvBasicoContact($ids, $data);
                    $ids['cta'] = $result['body'];
                    $result['body'] = $ids;
                }
            }
        }
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-proveedor-byid/{idProv}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getProveedorById(int $apiVer, int $idProv)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getProveedorById($idProv);
        $result = $dql->getArrayResult();
        $rota = count($result);
        if($rota > 0)  {
            for ($e=0; $e < $rota; $e++) {

                $result[$e]['sucursales'] = $this->getContactosDeSucursales($result[$e]['sucursales']);
            }
        }
        return $this->json($result);
    }

    /**
     * Metodo interno usardo en:
     * @see $this->
    */
    private function getContactosDeSucursales($sucursales)
    {
        $vueltas = count($sucursales);
        for ($s=0; $s < $vueltas; $s++) { 
            $dql = $this->repo->getAllContactosByIdSucursal($sucursales[$s]['id']);
            $contacs = $dql->getArrayResult();
            $sucursales[$s]['contacts'] = $contacs;
        }
        return $sucursales;
    }

    /**
     * @Rest\Get("get-all-proveedores/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllProveedores(int $apiVer)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getAllProveedores();
        $result = $dql->getArrayResult();
        $rota = count($result);
        if($rota > 0)  {
            for ($e=0; $e < $rota; $e++) {

                $vueltas = count($result[$e]['sucursales']);
                for ($s=0; $s < $vueltas; $s++) { 
                    $dql = $this->repo->getAllContactosByIdSucursal($result[$e]['sucursales'][$s]['id']);
                    $contacs = $dql->getArrayResult();
                    $result[$e]['sucursales'][$s]['contacts'] = $contacs;
                }
            }
        }
        return $this->json($result);
    }

    /**
     * @Rest\Post("save-data-respuesta/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveDataRespuesta(Request $req, int $apiVer)
    {
        $data = json_decode($req->request->get('data'), true);

        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->saveDataRespuesta($data);
        // Notificar a EYE del cambio de status

        // Notificar al cliente del cambio de status
        return $this->json($result);
    }

    /**
     * Usado para subir las imagenes que son parte de las respuestas echas por los proveedores
     * este metodo es usado desde la SCP por nosotros mismo.
     *      
     * @Rest\Post("save-foto-respuesta/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function saveFotoRespuesta(Request $req, int $apiVer)
    {
        $mover = true;
        $todasExistentes = [];
        $result = ['abort' => false, 'msg' => 'fotos', 'body' => []];
        $this->getRepo(RepoMain::class, $apiVer);

        $params = json_decode($req->request->get('data'), true);

        if(array_key_exists('metas', $params)) {

            $uriServer = $this->getParameter('cotizadas');
            $uriServer = str_replace('_repomain_', $params['metas']['id_main'], $uriServer);
            $uriServer = str_replace('_idinfo_', $params['metas']['id_info'], $uriServer);

            // Primeramente revisamos si ya hay fotos compartidas.
            if(is_dir($uriServer)) {
                $finder = new Finder();
                $finder->files()->in($uriServer);
                if ($finder->hasResults()) {
                    foreach ($finder as $file) {
                        $todasExistentes[] = $file->getRelativePathname();
                    }
                }
            }

            if(count($todasExistentes) < 4) {
                $todasExistentes[] = $params['filename'];
                // Guardamos el nombre de la foto en la BD de la pieza.
                $result = $this->repo->updateFotoDeRespuesta($params['metas']['id_info'], $todasExistentes);
            }else{
                $mover = false;
            }

            if($mover) {
                if($params['metas']['id_main'] != 0) {
                    
                    if(!is_dir($uriServer)) {
                        mkdir($uriServer, 0777, true);
                    }
        
                    $saveTo = realpath($uriServer);
                    if($saveTo !== false) {
                        $foto = $req->files->get($params['campo']);
                        $foto->move($saveTo, $params['filename']);
                        $result = [
                            'abort' => false, 'msg' => 'ok', 'body' => $todasExistentes
                        ];
                    }
                }else{
                    $result = [
                        'abort' => true, 'msg' => 'err', 'body' => $result['msg']
                    ];
                }
            }else{
                $result = [
                    'abort' => false, 'msg' => 'ok', 'body' => 'noSave'
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
     * Esta prueba se realiza desde SCP-EYE para ver si el sistema tiene servicio push
     * 
     * @Rest\Get("test-push-scp-eye/{tokenPush}/")
    */
    public function testPushScpEye($tokenPush)
    {
        $result = $this->push->sendPushTo($tokenPush, 'pcom', []);
        return $this->json($result);
    }

    /**
     * Guardamos el token push de la sesion abirta del SCP-EYE
     * 
     * @Rest\Get("send-token-push-to-server/{tokenPush}/")
    */
    public function sendTokenPushToServer($tokenPush)
    {
        $pathTokens = $this->getParameter('empTkWorker');
        $partes = explode('::', $tokenPush);
        if(!is_dir($pathTokens)) {
            mkdir($pathTokens, 777);
        }
        $leng = file_put_contents($pathTokens . $partes[0] . '.txt', $partes[1]);
        return $this->json([
            'abort' => false, 'msg' => 'ok', 'body' => $leng
        ]);
    }

    /**
     * @Rest\Get("get-resp-cots-by/{idPieza}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idPieza", requirements="\s+", description="la pieza cotizada")
    */
    public function getRespCotsBy(int $apiVer, $idPieza)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getRespCotsBy($idPieza);
        $result = $dql->getScalarResult();
        return $this->json($result);
    }

}
