<?php

namespace App\Controller\Api\FireWalls\Publicas;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


use App\Entity\AO2Modelos;

/**
 * @Route("api/firewalls/publicas/v{apiVer}/", defaults={"apiVer":"1"})
 */
class AO2ModelosController extends AbstractFOSRestController
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
     * @Rest\Get("get-modelos-by-ids-marca/{idsMarca}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     * Use::FROM::app/web-talleres::CLASS::AutomovilRepository
    */
    public function getModelosByIdsMarcas($apiVer, $idsMarca)
    {
        $this->getRepo(AO2Modelos::class, $apiVer);
        $dql = $this->repo->getModelosByIdsMarcas($idsMarca);
        $result = $dql->getScalarResult();
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-modelos-by-marca/{idMarca}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     * Use::FROM::app/web-talleres::CLASS::AutomovilRepository
    */
    public function getModelosByIdMarca($apiVer, $idMarca)
    {
        $this->getRepo(AO2Modelos::class, $apiVer);
        $dql = $this->repo->getModelosByIdMarca($idMarca);
        $result = $dql->getScalarResult();
        return $this->json($result);
    }
    
}
