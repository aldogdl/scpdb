<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Entity\UsEmpresaTipos;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/", defaults={"apiVer":"1"})
 */
class UsAdminController extends AbstractFOSRestController
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
     * @Rest\Get("get-roles/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getRoles($apiVer)
    {
        $result = ['abort' => false, 'body' => ''];
        $roles = $this->getParameter('roles');
        $this->getRepo(UsEmpresaTipos::class, $apiVer);
        $dql = $this->repo->getAllTipos();
        $moreRoles = $dql->getScalarResult();
        if($moreRoles) {
            $rota = count($moreRoles);
            for ($i=0; $i < $rota; $i++) { 
                $roles[] = $moreRoles[$i]['emt_tipo'] . '->' . $moreRoles[$i]['emt_role'];
            }
        }
        $result['body'] = $roles;
        return $this->json($result);
    }

    /**
     * @Rest\Get("get-users-by-role/{role}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * @Rest\RequestParam(name="role", requirements="\s+", description="El role de los usuarios.")
    */
    public function getUserByRole($apiVer, $role)
    {
        $this->getRepo(UsAdmin::class, $apiVer);
        $dql = $this->repo->getUserByRole($role);
        $result = $dql->getScalarResult();
        return $this->json($result);
    }

    /**
     * @Rest\Post("crear-user-con-privilegios/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function crearUserConPrivilegios(Request $request, UserPasswordEncoderInterface $encoder, $apiVer)
    {
        $this->getRepo(UsAdmin::class, $apiVer);
        $data = json_decode($request->request->get('data'), true);
        $result = $this->repo->crearUserAdmin($encoder, $data);
        return $this->json($result);
    }
    
    /**
     * @Rest\Post("reg-usuarios-contacts/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function regUsuariosContacts(Request $request, UserPasswordEncoderInterface $encoder, $apiVer)
    {
        $this->getRepo(UsAdmin::class, $apiVer);
        $data = json_decode($request->request->get('data'), true);
        $result = $this->repo->regUsuariosContacts($encoder, $data['users'], true);
        return $this->json(array_merge($result, $data));
    }

    /**
     * @Rest\Get("get-user-by-campo/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     *  Use::FROM::app/web-talleres::CLASS::UserAdmRepository
     * 
    */
    public function getUserByCampo(Request $req, $apiVer)
    {
        $this->getRepo(UsAdmin::class, $apiVer);
        $campo = $req->query->get('campo');
        $valor = $req->query->get('valor');
        $abs = $req->query->get('abs');
        // El campo abs nos indica que se busque al usuario con el valor absoluto
        $dql = $this->repo->getUserByCampo($campo, $valor, $abs);
        return $this->json($dql->getScalarResult());
    }

    /**
     * @Rest\Get("is-tokenapz-caducado/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
     * 
     * Use::FROM::app/web-talleres::CLASS::UserAdmRepository
    */
    public function isTokenApzCaducado($apiVer)
    {
        return $this->json(['abort' => false, 'msg' => 'ok', 'body' => false]);
    }

    /**
     * @Rest\Get("existe-username/{username}/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function existeUsername(Request $req, $apiVer, $username)
    {
        $this->getRepo(UsAdmin::class, $apiVer);
        return $this->json(['abort' => false, 'msg' => 'ok', 'body' => $this->repo->existeUsername($username)]);
    }

    /**
     * @Rest\Post("update-token-device/")
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function updateTokenDevice(Request $req, $apiVer)
    {   
        $data = json_decode($req->request->get('data'), true);
        if(strpos($data['role'], 'ADMIN') !== false) {
            $ruta = $this->getParameter('empTkWorker');
            file_put_contents($ruta.$data['id'].'m.txt', $data['token']);
            return $this->json(['abort' => false]);
        }
        $this->getRepo(UsContacts::class, $apiVer);
        $this->repo->updateTokenDevice($data);
        return $this->json(['abort' => false]);
    }
    
    /**
     * @Rest\Get("get-data-tarjeta-dig/{idUser}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getDataTarjetaDig($apiVer, $idUser)
    {
        $result = ['abort' => false, 'body' => []];

        $this->getRepo(UsAdmin::class, $apiVer);
        $dql = $this->repo->getUserByCampo('id', $idUser);
        $user = $dql->getScalarResult();
        if($user) {

            $contac = [];
            if( strpos($user[0]['u_roles'][0], 'SOCIO') !== false ) {
                $this->getRepo(UsContacts::class, $apiVer);
                $dql = $this->repo->getDataTarjetaDig($idUser);
                $contac = $dql->getScalarResult();
                if($contac) {
                    $contac = $this->repo->prepareDataForTarjeta($user[0], $contac[0]);
                }
            }
            if( strpos($user[0]['u_roles'][0], 'ADMIN') !== false ) {
                $path = realpath($this->getParameter('tjtDigi') . $user[0]['u_username'] . '_td.json');
                $data = file_get_contents( $path );
                $contac = json_decode($data, true);
                $user[0]['u_roles'] = $user[0]['u_roles'][0];
                $contac = array_merge($user[0], $contac);
            }
            if(count($contac) == 0) {
                $result['abort'] = true;
                $result['body'] = 'No se encontraron datos correspondientes.';
            }else{
                $result['body'] = $contac;
            }
        }else{
            $result['abort'] = true;
            $result['body'] = 'El Usuario no esta Registrado';
        }

        return $this->json($result);
    }
}
