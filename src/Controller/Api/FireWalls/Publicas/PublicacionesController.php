<?php

namespace App\Controller\Api\FireWalls\Publicas;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Publicaciones;
use App\Entity\Sistemas;

/**
 * @Route("api/firewalls/publicas/v{apiVer}/pubs/", defaults={"apiVer":"1"})
 */
class PublicacionesController extends AbstractFOSRestController
{
    private $em;
    private $repo;
    private $paginator;

    public function __construct(EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $this->em = $entityManager;
        $this->paginator = $paginator;
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
     * @Rest\Get("get-all-publicaciones-by-user/{idUser}/p/{page}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllPublicacionesByUser($apiVer, int $idUser, int $page)
    {
        $result = [];
        $this->getRepo(Publicaciones::class, $apiVer);
        $dql = $this->repo->getAllPubsByIdUser($idUser);
        $pagination = $this->paginator->paginate($dql, $page, 10);
        
        foreach ($pagination as $item) {
            $result[] = $this->repo->toArray($item, true);
        }
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-sistemas-auto/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getSistemasDelAuto($apiVer)
    {
        $result = [];
        $em = $this->getDoctrine()->getRepository(Sistemas::class);
        $dql = $em->getSistemas();
        $result = $dql->getScalarResult();
        return $this->json($result);
    }

}
