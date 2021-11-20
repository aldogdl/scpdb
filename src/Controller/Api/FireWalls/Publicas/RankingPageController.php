<?php

namespace App\Controller\Api\FireWalls\Publicas;

use App\Entity\Publicaciones;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Resenias;
use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;

/**
 * @Route("api/firewalls/publicas/v{apiVer}/ranking-page/", defaults={"apiVer":"1"})
 */
class RankingPageController extends AbstractFOSRestController
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
     * @Rest\Post("user-is-from-emp/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function userIsFromEmpresa(Request $req, $apiVer)
    {
        $this->getRepo(UsContacts::class, $apiVer);        
        $data = json_decode($req->request->get('data'), true);
        return $this->json($this->repo->userIsFromEmpresa($data));
    }

    /**
     * @Rest\Get("get-empresa-by-slug/{slug}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getEmpresaBySlug($apiVer, $slug)
    {
        $this->getRepo(UsEmpresa::class, $apiVer);        
        
        $dql = $this->repo->getEmpresaBySlug($slug);
        $result = $dql->getScalarResult();
        if($result){
            $data = $result[0];
            $this->getRepo(UsSucursales::class, $apiVer);        
            $dql = $this->repo->getSucursalesByIdEmpresa($data['em_id'], 'ASC');
            $data['sucs'] = $dql->getScalarResult();
            return $this->json($data);
        }else{
            return $this->json([]);
        }
    }

    /**
     * @Rest\Get("get-resenias-resume-by-idemp/{idemp}/{perPage}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getReseniasActiveByIdEmp($apiVer, $idemp, $perPage)
    {
        $this->getRepo(Resenias::class, $apiVer);        
        $contar = $this->repo->getReseniasCountByIdEmp($idemp);
        $dql = $this->repo->getReseniasActiveByIdEmp($idemp);
        $pagination = $this->paginator->paginate($dql, 1, $perPage);
        $contar['resenias'] = $pagination->getTotalItemCount();

        foreach ($pagination as $item) {
            if(!$item->getIsPublic()) {
                $contar['res'][] = $this->repo->toArray($item, true);
            }
        }
        return $this->json($contar);
    }

    /**
     * @Rest\Post("set-resenia/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setResenia(Request $req, $apiVer)
    {
        $this->getRepo(Resenias::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        
        $result = $this->repo->setResenias($data);
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-all-publicaciones/{idEmp}/p-{page}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllPublicaciones($apiVer, $idEmp, $page)
    {
        $result = [];
        $this->getRepo(Publicaciones::class, $apiVer);
        $dql = $this->repo->getAllPubsByIdEmp($idEmp);
        $pagination = $this->paginator->paginate($dql, $page, 20);
        
        foreach ($pagination as $item) {
            $result[] = $this->repo->toArray($item, true);
        }
        return $this->json($result);
    }

}
