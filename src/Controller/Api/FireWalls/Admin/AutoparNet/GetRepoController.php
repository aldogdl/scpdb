<?php

namespace App\Controller\Api\FireWalls\Admin\AutoparNet;

use App\Entity\RepoAutos;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;

/**
 * @Route("api/firewalls/autoparnet/repo-get/v{apiVer}/", defaults={"apiVer":"1"})
 */
class GetRepoController extends AbstractFOSRestController
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
     * Recuperamos los Registro de autos por medio de sus IDS
     * 
     * @Rest\Get("get-repo-autos-by-ids/{ids}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="ids", requirements="\s+", description="Los identificadores de los repoAutos")
     * 
     * Use::FROM::app/web-talleres::CLASS::RepoRepository
    */
    public function getRepoAutosByIds(int $apiVer, $ids)
    {
        $this->getRepo(RepoAutos::class, $apiVer);
        $dql = $this->repo->getReposAutosByIds($ids);
        $autos = $dql->getScalarResult();
        return $this->json($autos);
    }
    
    /**
     * Recuperamos los Repositorios que estan en proceso por medio del id del usuario
     * 
     * @Rest\Get("get-repos-en-proceso-by-id-user/{iduser}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="iduser", requirements="\d+", description="El identificador unico del user")
     * 
     * Use::FROM::app/web-talleres::CLASS::RepoRepository
    */
    public function getReposEnProcesoByIdUser(int $apiVer, $iduser)
    {
        $repos = [];
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getReposEnProcesoByIdUserFull($iduser);
        $repo = $dql->getScalarResult();
        $rotaR = count($repo);
        if($rotaR > 0) {
            for ($i=0; $i < $rotaR; $i++) { 
                $item = $repo[$i];
                unset($item['a_cantReq']);
                $dql = $this->repo->getAllPiezasByIdRepo($item['repo_id']);
                $more = $dql->getScalarResult();
                
                if($more) {
                    $item['piezas'] = $more;
                    $rota = count($item['piezas']);
                    if( $rota > 0 ) {
                        for ($p=0; $p < $rota; $p++) { 
                            $info = $this->repo->getInfoByIdPiezas($item['piezas'][$p]['pzas_id']);
                            $item['piezas'][$p]['info'] = $info->getScalarResult();
                        }
                    }
                    $dql = $this->repo->getContactosByIdUser($item['repo_own']);
                    $more = $dql->getScalarResult();
                    if($more) {
                        $item['contacto'] = $more[0];
                    }
                    $more = null;
                }

                $repos[] = $item;
            }
        }
        return $this->json($repos);
    }

    /**
     * Recuperamos el Repositorio por medio de sus ids de la tabla main
     * 
     * @Rest\Get("get-repo-full-by-ids-main/{idsRepo}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idRepo", requirements="\d+", description="El identificador unico del repo main")
    */
    public function getRepoFullByIdMain(int $apiVer, $idsRepo)
    {
        $ids = [];
        $repos = [];
        $this->getRepo(RepoMain::class, $apiVer);
        if(strpos($idsRepo, '-') !== false){
            $ids = explode('-', $idsRepo);
        }else{
            $ids = [$idsRepo];
        }

        $dql = $this->repo->getRepoByIdsMainFull($ids);
        $repo = $dql->getScalarResult();
        $rota = count($repo);
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $item = $repo[$i];
                unset($repo['a_cantReq']);
                $dql = $this->repo->getAllPiezasByIdRepo($item['repo_id']);
                $more = $dql->getScalarResult();
                
                if($more) {
                    $item['piezas'] = $more;
                    $rota = count($item['piezas']);
                    if( $rota > 0 ) {
                        for ($p=0; $p < $rota; $p++) { 
                            $info = $this->repo->getInfoByIdPiezas($item['piezas'][$p]['pzas_id']);
                            $item['piezas'][$p]['info'] = $info->getScalarResult();
                        }
                    }
                    $dql = $this->repo->getContactosByIdUser($item['repo_own']);
                    $more = $dql->getScalarResult();
                    if($more) {
                        $item['contacto'] = $more[0];
                    }
                    $more = null;
                }

                $repos[] = $item;
            }
        }
        return $this->json($repos);
    }

    /**
     * Marcamos el repo main como eliminado por el usuario
     * 
     * @Rest\Get("mark-como-delete-repo-main/{idMain}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idMain", requirements="\d+", description="El identificador unico del repo main")
     * 
     * Use::FROM::app/web-talleres::CLASS::RepoRepository
    */
    public function markComoDeleteRepoMain(int $apiVer, $idMain)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $result = $this->repo->markComoDeleteRepoMain($idMain);
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-fotos-shared-from-app-to-web/{idMain}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idMain", requirements="\d+", default="1", description="El Identificador del RepoMain")
    */
    public function getFotosSharedFromAppToWeb(int $apiVer, int $idMain)
    {
        $result = ['abort' => false, 'msg' => 'ok', 'body' => [] ];

        $finder = new Finder();
        $uriServer = $this->getParameter('toCotizar');
        $uriServer = str_replace('_repomain_', $idMain, $uriServer);
        if(is_dir($uriServer)) {
            $finder->files()->in($uriServer);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $result['body'][] = $file->getRelativePathname();
                }
            }
        }else{
            $result = [
                'abort' => true,
                'msg' => 'No se encontraron fotografías compartidas para la Solicitud con el ID: ' . $idMain,
                'body' => []
            ];
        }
        return $this->json($result);
    }

    /**
     * @Rest\Get("delete-fotos-shared/{nameFoto}/{idMain}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="nameFoto", requirements="\s+", default="1", description="El Nombre de la foto")
     * @Rest\RequestParam(name="idMain", requirements="\d+", default="1", description="El Identificador del RepoMain")
    */
    public function deleteFotosShared(int $apiVer, string $nameFoto, int $idMain)
    {
        $result = ['abort' => false, 'msg' => 'ok', 'body' => [] ];

        $finder = new Finder();
        $uriServer = $this->getParameter('toCotizar');
        $uriServer = str_replace('_repomain_', $idMain, $uriServer);

        $finder->files()->in($uriServer);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                if($nameFoto == $file->getRelativePathname()){
                    unlink($file->getRealPath());
                }
            }
        }
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-respuestas-xcot/{idRepo}")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idRepo", requirements="\s+", description="El repo para cotizar")
    */
    public function getRespuestasXcot(int $apiVer, $idRepo)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getRepoPiezaInfoByIdRepoMain($idRepo);
        $result = $dql->getScalarResult();
        return $this->json(['abort' => false, 'body' => $result]);
    }

    /**
     * En revision
     * 
     * @Rest\Get("get-repo-last-for-build/{type}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="type", requirements="\s+", description="La ultima repo por tipo")
    */
    public function getRepoLastForBuild(int $apiVer, $type)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getRepoLastByType($type);
        $repo = $dql->getScalarResult();
        if($repo) {
            $repo = $repo[0];
            $dql = $this->repo->getAllPiezasByIdRepo($repo['repo_id']);
            $more = $dql->getScalarResult();
            if($more) {
                $repo['piezas'] = $more;
            }
            $rota = count($repo['piezas']);
            if( $rota > 0 ) {
                for ($p=0; $p < $rota; $p++) { 
                    $info = $this->repo->getInfoByIdPiezas($repo['piezas'][$p]['pzas_id']);
                    $repo['piezas'][$p]['info'] = $info->getScalarResult();
                }
            }
            $dql = $this->repo->getContactosByIdUser($repo['repo_own']);
            $more = $dql->getScalarResult();
            if($more) {
                $repo['contacto'] = $more[0];
            }
            $more = null;

        }
        return $this->json($repo);
    }
    
}