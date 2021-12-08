<?php

namespace App\Controller\Api\FireWalls\Publicas;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;

use App\Entity\RepoMain;
use App\Services\PushNotifiers;

/**
 * Clase utilizada desde SCP-EYE, donde por el momento no se cuenta con un sistema
 * de alamacenamiento para el token server, por lo tanto deben ser publicos estos
 * endPoints.
 * 
 * @Route("api/firewalls/publicas/repo_scp/v{apiVer}/", defaults={"apiVer":"1"})
 */
class RepoSCP extends AbstractFOSRestController
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
            [$this->em->getRepository($class), 'getV'.$apiVer . 'SCP'],
            [$this->em]
        );
    }
    
    /**
     * @Rest\Get("get-all-repo-en-proceso/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getAllRepoEnProceso(int $apiVer)
    {
        $this->getRepo(RepoMain::class, $apiVer);
        $dql = $this->repo->getReposAllEnProceso();
        $result = $dql->getArrayResult();
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

        $hoy = new \DateTime('now');
        $fileArchivo = $hoy->format('d-m-Y');
        
        $finder = new Finder();
        $finder->files()->in($pathTokens);
        $todasExistentes = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $todasExistentes[] = [
                    'nomFile' => $file->getRelativePathname(),
                    'pathReal'=> $file->getRealPath()
                ];
            }
        }

        $sufijo = 0;
        $rota = count($todasExistentes);
        $deleteTokens = [];
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                if(strpos($todasExistentes[$i]['nomFile'], $fileArchivo) !== false) {
                    $sufijo = $sufijo +1;
                }else{
                    $deleteTokens[] = $todasExistentes[$i]['pathReal'];
                }
            }
        }

        $rota = count($deleteTokens);
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                unlink($deleteTokens[$i]);
            }
        }

        if($sufijo > 0) {
            $fileArchivo = $fileArchivo .'-'.$sufijo;
        }
        $leng = file_put_contents($pathTokens . $fileArchivo . '.txt', $partes[1]);
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
