<?php

namespace App\Controller\Api\FireWalls\Admin\AutoparNet;

use App\Entity\UsContacts;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;

use App\Services\PushNotifiers;

/**
 * @Route("api/firewalls/autoparnet/pushes/v{apiVer}/", defaults={"apiVer":"1"})
 */
class PushesController extends AbstractFOSRestController
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
     * Guardamos el token de messanging del usuario de la app
     * 
     * @Rest\Post("set-token-messaging-by-id-user/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     * Use::FROM::app/web-talleres::CLASS::RepoRepository
    */
    public function setTokenMessagingByIdUser(Request $req, int $apiVer)
    {
        $data = json_decode($req->request->get('data'), true);
        $this->getRepo(UsContacts::class, $apiVer);
        $result = $this->repo->updateTokenPushByIdUser($data);
        return $this->json($result);
    }

    /**
     * @Rest\Get("send-push-to-scp-for-cot/{params}/")
     * 
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="params", requirements="\s+", default="1", description="Los datos de la notificacion")
    */
    public function sendPushToScpForCot($apiVer, $params)
    {
        $tokensPush = [];
        $path = realpath($this->getParameter('empTkWorker'));
        $finder = new Finder();
        $finder->files()->in($path);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $tokensPush[] = $file->getContents();
            }
        }

        if(count($tokensPush) > 0) {
            $partes = explode('::', $params);
            $data = [
                'idMain' => $partes[0],
                'cantPzas' => $partes[1],
                'fechr' => $partes[2],
                'cantPzasResp' => 0,
                'cantResp' => 0
            ];
            $result = $this->push->sendPushTo($tokensPush, 'cot', $data);
        }else{
            $result = ['abort' => true, 'body' => 'No se encontro el token para Messanging'];
        }
        return $this->json($result);
    }
}