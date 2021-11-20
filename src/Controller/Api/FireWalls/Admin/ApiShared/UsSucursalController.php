<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\UsSucursales;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/sucursal/", defaults={"apiVer":"1"})
 */
class UsSucursalController extends AbstractFOSRestController
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
        $this->repo = call_user_func_array([$this->em->getRepository($class), 'getV'.$apiVer], [$this->em]);
    }

    /**
     * @Rest\Get("get-sucursales-by-id-emp/{idEm}")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getSucursalesByIdEmp($idEm, $apiVer)
    {
        $this->getRepo(UsSucursales::class, $apiVer);
        $dql = $this->repo->getSucursalesByIdEmpresa($idEm);
        return $this->json($dql->getScalarResult());
    }

    /**
     * @Rest\Post("set-sucursales/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setSucursales(Request $req, $apiVer)
    {
        $this->getRepo(UsSucursales::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->setSucursales($data['sucs']);
        return $this->json($result);
    }

    /**
     * @Rest\Post("set-fachadas-sucursal/{emId}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setFachadasSucursal(Request $req, $apiVer, $emId)
    {
        $this->getRepo(UsSucursales::class, $apiVer);
        $pathAltaNueva = $this->getParameter('empFacha');
        $path = realpath($pathAltaNueva);
        if(!is_dir($path .'/'. $emId)) {
            mkdir($path .'/'. $emId, 0777);
        }

        $nombreFotos = json_decode($req->request->get('data'), true);
        $rota = count($nombreFotos);
        for ($i=0; $i < $rota; $i++) { 
            $foto = $req->files->get($nombreFotos[$i]['campo']);
            $foto->move($path .'/'. $emId, $nombreFotos[$i]['filename']);
        }
        
        $result = $this->repo->setFachadasSucursal($emId, $nombreFotos);
        return $this->json($result);
    }
}
