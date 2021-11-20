<?php

namespace App\Controller\Api\FireWalls\Admin\AutoparNet;

use App\Entity\RepoMain;
use App\Entity\UsContacts;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;
use App\Services\PushNotifiers;

/**
 * @Route("api/firewalls/autoparnet/v{apiVer}/empresas/", defaults={"apiVer":"1"})
 */
class EmpresasController extends AbstractFOSRestController
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
  private function getRepo($class, $apiVer, $tipoRepo = 'normal')
  {
    if($tipoRepo == 'normal') {
      $this->repo = call_user_func_array([$this->em->getRepository($class), 'getV'.$apiVer], [$this->em]);
    }else{
      $this->repo = call_user_func_array([$this->em->getRepository($class), 'getV'.$apiVer . 'CpanelWeb'], [$this->em]);
    }
  }
  
  /**
   * @Rest\Get("get-id-empresa-by-id-suc/{idSuc}/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function getIdEmpresaByIdSuc(int $apiVer, $idSuc)
  {
    $result = ['abort' => false, 'body' => '0'];
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $dql = $this->repo->getIdEmpresaByIdSuc($idSuc);
    $res = $dql->getScalarResult();
    if($res){
      $result['body'] = $res[0]['em_id'];
    }else{
      $result['abort'] = true;
    }
    return $this->json($result);
  }
  
  /**
   * @Rest\Get("get-last-empresas/{idEmp}")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function getLastEmpresas(int $apiVer, int $idEmp = 0)
  {
    $this->getRepo(UsEmpresa::class, $apiVer, 'cpanel');
    $dql = $this->repo->getLastEmpresas($idEmp);
    $result = $dql->getScalarResult();
    return $this->json($result);
  }
  
  /**
   * @Rest\Get("get-sucursales-empresa/{idEmp}/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
   * @Rest\RequestParam(name="idEmp", requirements="\d+", description="El identificador de la empresa")
  */
  public function getAllSucursalesByIdEmpresa(int $apiVer, $idEmp)
  {
    $this->getRepo(UsEmpresa::class, $apiVer, 'cpanel');
    $dql = $this->repo->getAllSucursalesByIdEmpresa($idEmp);
    $result = $dql->getScalarResult();
    return $this->json($result);
  }

  /**
   * Temporalmente esta no se usa, ya que en este metodo se esperaba como parametros
   * los contactos a los cuales se enviará el push, sin embargo por el momento se
   * decidió envar a todos, e ir armando el avatar de cada socio, es decir, cada ves
   * que el socio responda a una cotización, se almacenará en otra tabla las piezas que
   * realmente maneja para con el tiempo filtrar verdaderamente a los que si o no
   * manejan cada autoparte.
   * 
   * @Rest\Post("send-notificacion-para-cotizar-tmp/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function sendNotificacionParaCotizarTMP(Request $req, int $apiVer, PushNotifiers $push)
  {
    $this->getRepo(UsEmpresa::class, $apiVer, 'cpanel');
    $data = $req->request->get('data');
    $partes = explode('::', $data);
    $dql = $this->repo->getContactsTokenPushByIdRepo($partes[0]);
    $result = $dql->getScalarResult();
    if($result) {
      $token = [];
      $rota = count($result);
      for ($i=0; $i < $rota; $i++) { 
        if($result[$i]['cts_notifiKey'] != '0') {
          $token[] = $result[$i]['cts_notifiKey'];
        }
      }
    }

    $this->getRepo(RepoMain::class, $apiVer, 'cpanel');
    $data = $this->repo->createDataForPushCot($partes[1]);

    $push->sendPushForCotizar($token, $data);
    return $this->json(['abort' => false, 'body' => count($token) . ' Socios Notificados.']);
  }

  /**
   * @Rest\Get("get-data-sucursal-by-id/{idSuc}/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
   * @Rest\RequestParam(name="idSuc", requirements="\d+", description="El identificador de la empresa")
  */
  public function getDataSucursalById(int $apiVer, $idSuc)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $dql = $this->repo->getDataSucursalById($idSuc);
    $result['sucursal'] = $dql->getScalarResult();
    $dql = $this->repo->getContactosByIdSucursal($idSuc);
    $result['contactos'] = $dql->getScalarResult();
    return $this->json([$result]);
  }

  /**
   * @Rest\Get("get-more-data-empresa/{idEmp}/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
   * @Rest\RequestParam(name="idEmp", requirements="\d+", description="El identificador de la empresa")
  */
  public function getMoreDataEmpresa(int $apiVer, $idEmp)
  {
    $this->getRepo(UsEmpresa::class, $apiVer, 'cpanel');
    $dql = $this->repo->getMoreDataEmpresa($idEmp);
    $result = $dql->getScalarResult();
    return $this->json($result);
  }

  /**
   * @Rest\Get("get-data-build-perpza/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function getDataBuildPerpza(Request $req, int $apiVer)
  {
    
    $this->getRepo(UsContacts::class, $apiVer, 'cpanel');
    $dql = $this->repo->getDataBuildPerpza();
    $result = $dql->getScalarResult();
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-empresa/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataEmpresa(Request $req, int $apiVer)
  {
    $this->getRepo(UsEmpresa::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    $result = $this->repo->updateDataEmpresa($data);
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-sucursal/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataSucursal(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    
    $result = $this->repo->updateDataSucursal($data);
    return $this->json($result);
  }
  
  /**
   * @Rest\Post("update-data-localidad/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataLocalidad(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    
    $result = $this->repo->updateDataLocalidad($data);
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-contactos/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataContactos(Request $req, int $apiVer)
  {
    $this->getRepo(UsContacts::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    $result = $this->repo->updateDataContactos($data);
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-fachada/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataFachada(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);

    $result = $this->repo->updateDataFachada($data);
    if(!$result['abort']) {
      $delete = $result['body'];
      $result['body'] = [];

      if(array_key_exists('images', $delete)) {
        $rota = count($delete['images']);
        $pathImg = $this->getParameter('empFacha');
        for ($i=0; $i < $rota; $i++) {
          $path = realpath($pathImg . $delete['idEmp'] . '/' . $delete['images'][$i]);
          if($path !== false) {
            unlink($path);
          }
        }
      }
    }
    
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-logotipo/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataLogotipo(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    $result = $this->repo->updateDataLogotipo($data);
    if(!$result['abort']) {
      $delete = $result['body'];
      $result['body'] = [];
      $rota = count($delete);
      $pathImg = $this->getParameter('empLogo');
      for ($i=0; $i < $rota; $i++) {
        $path = realpath($pathImg . $data['idEmp'] . '/' . $delete[$i]);
        if($path !== false) {
          unlink($path);
        }
      }
    }
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-palclas/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataPalclas(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    $result = $this->repo->updateDataPalclas($data);
    return $this->json($result);
  }

  /**
   * @Rest\Post("update-data-marcas/")
   * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
  */
  public function updateDataMarcas(Request $req, int $apiVer)
  {
    $this->getRepo(UsSucursales::class, $apiVer, 'cpanel');
    $data = json_decode($req->request->get('data'), true);
    $result = $this->repo->updateDataMarcas($data);
    return $this->json($result);
  }


}
