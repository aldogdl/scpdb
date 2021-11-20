<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\UsEmpresaTipos;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/empresas_tipos/", defaults={"apiVer":"1"})
 */
class UsEmpresaTiposController extends AbstractFOSRestController
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
     * @Rest\Get("get-all-empresa-tipos/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllEmpresaTipos($apiVer)
    {
        $this->getRepo(UsEmpresaTipos::class, $apiVer);
        $dql = $this->repo->getAllTipos();
        
        return $this->json($dql->getScalarResult());
    }

}
