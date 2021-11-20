<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\Publicaciones;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/pubs/", defaults={"apiVer":"1"})
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
     * @Rest\Post("set-publicacion/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setPublicacion(Request $req, $apiVer)
    {
        $this->getRepo(Publicaciones::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);
        $result = $this->repo->setPublicacion($data['pub']);
        return $this->json($result);
    }

    /**
     * @Rest\Post("set-images-publicacion/{idPub}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setImagesPublicacion(Request $req, $apiVer, $idPub)
    {
        $this->getRepo(Publicaciones::class, $apiVer);
        $pathPub = $this->getParameter('pubImg');
        $path = realpath($pathPub);

        if(!is_dir($path .'/'. $idPub)) {
            mkdir($path .'/'. $idPub, 0777);
        }
        $nombreFotos = json_decode($req->request->get('data'), true);
        
        $rota = count($nombreFotos);
        for ($i=0; $i < $rota; $i++) { 
            $foto = $req->files->get($nombreFotos[$i]['campo']);
            $foto->move($path .'/'. $idPub, $nombreFotos[$i]['filename']);
        }

        $result = ['abort' => false];
        return $this->json($result);
    }

    /**
     * @Rest\Get("delete-image-publicacion/{idPub}/{foto}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function deleteImagePublicacion($apiVer, $idPub, $foto)
    {
        $this->getRepo(Publicaciones::class, $apiVer);
        $pathPub = $this->getParameter('pubImg');
        $path = realpath($pathPub);
        $result = $this->repo->deleteImagePublicacion($idPub, $foto);
        if(!$result['abort']) {
            unlink($path .'/'. $idPub .'/'. $foto);
        }

        return $this->json($result);
    }

    /**
     * @Rest\Get("toggle-activate-publicacion/{idPub}/{action}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function toggleActivatePublicacion($apiVer, $idPub, $action)
    {
        $this->getRepo(Publicaciones::class, $apiVer);
        $result = $this->repo->toggleActivatePublicacion($idPub, $action);

        return $this->json(['abort' => false, 'body' => $result]);
    }

    /**
     * @Rest\Get("delete-publicacion/{idPub}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function deletePublicacion($apiVer, $idPub)
    {
        $this->getRepo(Publicaciones::class, $apiVer);
        $result = $this->repo->deletePublicacion($idPub);
        if($result['body'] == 'ok') {
            $pathPub = $this->getParameter('pubImg');
            $path = realpath($pathPub);
    
            $finder = new Finder();
            $finder->files()->in($path .'/' . $idPub);

            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    unlink($path . '/' . $idPub . '/' . $file->getRelativePathname());
                }
            }

            rmdir($path . '/' . $idPub);
        }

        return $this->json($result);
    }
}
