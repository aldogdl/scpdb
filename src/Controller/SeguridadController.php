<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function Symfony\Component\String\b;

use App\Entity\UsAdmin;

/**
 * @Route("seguridad/")
 */
class SeguridadController extends AbstractFOSRestController
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
    private function getRepo($class, $v = 1) {
        $this->repo = call_user_func_array([$this->em->getRepository($class), 'getV'.$v], [$this->em]);
    }

    /**
     * @Rest\Post("login-check-admin/")
     * 
     * Use::FROM::app/web-talleres::CLASS::UserAdmRepository
     */
    public function getTokenUserAdmin() {}

    /**
     * @Rest\Post("login_check_particular/")
     */
    public function getTokenUserParticular() {}

    /**
     * @Rest\Post("crear-user-admin/")
    */
    public function crearUserAdmin(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $this->getRepo(UsAdmin::class);
        $data = $request->request->get('data');
        foreach ($data as $key => $value) {
            if(!b($value)->isUtf8()) {
                $data[$key] = (b($data[$key]))->toCodePointString('UTF-8');
            }
        }
        
        $result = $this->repo->crearUserAdmin($encoder, $data);
        return $this->json($result);
    }

    /**
     * @Rest\Post("set-token-messaging-worker/")
    */
    public function setTokenMessagingWorker(Request $req)
    {
        $data = json_decode($req->request->get('data'), true);
        if(!file_exists($this->getParameter('empTkWorker'))) { mkdir($this->getParameter('empTkWorker'), 0777); }
        file_put_contents($this->getParameter('empTkWorker').$data['worker'].'.txt', $data['token']);

        return $this->json(['abort' => false, 'body' => 'ok']);
    }

    /**
     * Esta prueba se realiza desde C3PIO para ver si el sistema tiene servicio push
     * @Rest\Get("prueba-comunicacion-push/{idUser}/")
    */
    public function pruebaComunicacionPush(\App\Services\PushNotifiers $push, $idUser)
    {
        $path = realpath($this->getParameter('empTkWorker').$idUser.'.txt');
        if($path) {
            $tokenWorker = file_get_contents($path);
            $result = $push->sendPushTo($tokenWorker, 'pcom', []);
        }else{
            $result = ['abort' => true, 'body' => 'No se encontro el token para Messanging'];
        }
        return $this->json($result);
    }

}
