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
     * @Rest\Get("send-push-nueva-cotizacion/{params}/")
     * 
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="params", requirements="\s+", default="1", description="Los datos de la notificacion")
    */
    public function notificarNewSolicitud($apiVer, $params)
    {
        $partes = explode('::', $params);
        $this->push->notificarNewSolicitud($partes[0]);
        return $this->json(['abort' => false, 'body' => 'ok']);
    }

    /**
     * @Rest\Get("send-push-tomada/{idRepo}/")
     * 
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idRepo", requirements="\d+", default="1", description="El id Repo main")
    */
    public function sendPushTomada($apiVer, $idRepo)
    {
        $this->push->notificarSolicitudTomada($idRepo);
        return $this->json(['abort' => false, 'body' => 'ok']);
    }

    /**
     * @Rest\Get("send-push-test-from-taller/{idUser}/")
     * 
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="idUser", requirements="\d+", default="1", description="El id del user")
    */
    public function sendPushTestFromTaller($apiVer, $idUser)
    {
        // protegiendo test push p1
        $nombreFile = 'test.json';
        $hoyEs = new \DateTime('now');
        $keyHoy = $hoyEs->format('dmY');
        $cantPushes = 0;
        $makePushReal = false;
        $test = [];
        $isNew = false;
        $uriPushes = $this->getParameter('whoTestPush');
        $pathToPushes = realpath($uriPushes);
        if(!is_dir($pathToPushes)) {
            mkdir($pathToPushes, 0777, true);
        }

        $finder = new Finder();
        $finder->files()->in($pathToPushes);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                if($file->getRelativePathname() == $nombreFile) {
                    $test = json_decode( $file->getContents(), true );
                }
            }

            if(array_key_exists('cantHoy', $test)) {
                if($test['cantHoy'] < 500) {

                    $encontre = false;
                    $vueltas = count($test['testing'][$keyHoy]);
                    if($vueltas > 0) {
                        for ($i=0; $i < $vueltas; $i++) { 
                            if($test['testing'][$keyHoy][$i] == $idUser){
                                $encontre = true;
                            }
                            $cantPushes++;
                        }
                        $test['cantHoy'] = $cantPushes;
                        if(!$encontre) {
                            $test['cantHoy'] = $test['cantHoy'] +1;
                            $test['testing'][$keyHoy][] = $idUser;
                            $makePushReal = true;
                        }
                    }else{
                        $test['cantHoy'] = 1;
                        $test['testing'][$keyHoy] = [$idUser];
                        $makePushReal = true;
                    }
                }
            }else{
                $isNew = true;
                $makePushReal = true;
            }
        }else{
            $isNew = true;
            $makePushReal = true;
        }

        if($isNew) {
            $test = [
                'cantHoy' => 1,
                'testing' => [
                    '' . $keyHoy . '' => [$idUser]
                ]
            ];
        }

        file_put_contents($uriPushes.'/'.$nombreFile, json_encode($test));

        if($makePushReal) {
            $this->push->sendPushTestTo($idUser);
            $result = ['abort' => false, 'body' => 'google'];
        }else{
            $result = ['abort' => false, 'body' => 'server'];
        }
        return $this->json($result);
    }
}