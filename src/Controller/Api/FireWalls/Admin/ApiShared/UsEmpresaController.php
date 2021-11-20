<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use App\Entity\LO3Ciudades;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\UsEmpresa;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/empresa/", defaults={"apiVer":"1"})
 */
class UsEmpresaController extends AbstractFOSRestController
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
     * @Rest\Get("get-all-ciudades/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllCiudades($apiVer)
    {
        $this->getRepo(LO3Ciudades::class, $apiVer);
        $dql = $this->repo->getAllCiudades();
        $result = $dql->getScalarResult();
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-last-empresas/{cant}/{idAvo}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getLastEmpresasByAVO($apiVer, $cant, $idAvo)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getLastEmpresasByAVO($idAvo, $cant);
        $result = $dql->getScalarResult();
        return $this->json($result);
    }

    /**
     * @Rest\Get("search-empresas-by/{criterio}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function searchEmpresasBy($apiVer, $criterio)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $result = $this->repo->searchEmpresasBy($criterio);
        return $this->json($result);
    }
    
    /**
     * @Rest\Get("get-empresa-by-id/{idEm}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getEmpresaById($apiVer, $idEm)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getEmpresaById($idEm);
        return $this->json($dql->getScalarResult());
    }
    
    /**
     * @Rest\Post("set-empresa/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setEmpresa(Request $req, $apiVer)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->setEmpresa($data);
        return $this->json($result);
    }
    
    /**
     * @Rest\Post("set-sub-dominio-empresa/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setSubDominioEmpresa(Request $req, $apiVer)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->setSubDominioEmpresa($data);
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-all-empresas/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllEmpresas($apiVer)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $dql = $this->repo->getAllTipos();
        return $this->json($dql->getScalarResult());
    }

    /**
     * @Rest\Post("set-logos-empresa/{emId}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setLogosEmpresa(Request $req, $apiVer, $emId)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);
        $pathAltaNueva = $this->getParameter('empLogo');
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

        $finder = new Finder();
        $finder->files()->in($path .'/' . $emId);
        $files = [];

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $fileNameWithExtension = $file->getRelativePathname();
                $files[] = $fileNameWithExtension;
            }
        }

        $result = $this->repo->setLogosEmpresa($emId, $files);
        return $this->json($result);
    }
}
