<?php

namespace App\Controller\Api\FireWalls\Admin\ApiShared;

use App\Entity\UsAdmin;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\UsContacts;
use App\Entity\UsSucursales;

/**
 * @Route("api/firewalls/admin/api-shared/v{apiVer}/contacts/", defaults={"apiVer":"1"})
 */
class UsContacsController extends AbstractFOSRestController
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
     * @Rest\Get("get-contactos-by-id-emp/{idEm}/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function getContactosByIdEmp($apiVer, $idEm)
    {
        $this->getRepo(UsContacts::class, $apiVer);
        $dql = $this->repo->getContactosByIdEmp($idEm);
        return $this->json($dql->getScalarResult());
    }

    /**
     * @Rest\Post("set-contactos/")
     * callFrom::app
     * @Rest\RequestParam(name="apiVer", requirements="\d+", default="1", description="La version del API")
    */
    public function setContactos(Request $req, UserPasswordEncoderInterface $encoder, $apiVer)
    {
        $this->getRepo(UsContacts::class, $apiVer);
        $data = json_decode($req->request->get('data'), true);

        // Recuperamos el role de la empresa
        $role = $this->repo->getRoleDeSucursalById($data['contacts'][0]['sucId']);

        if($role != 'nop') {

            // Creamos los usuarios correspondientes.
            $this->getRepo(UsAdmin::class, $apiVer);
            $result = $this->repo->regUsuariosContacts($encoder, $role, $data['contacts']);
            if(!$result['abort']) {
                $data = $result['body'];
                
                $this->getRepo(UsContacts::class, $apiVer);
                $result = $this->repo->setContactos($data);
            }
        }else{
            $result['abort'] = true;
            $result['msg'] = 'err';
            $result['body'] = 'No se pudo recuperar el Role de la Empresa, inténtalo nuevamente.';
        }
        
        return $this->json($result);
    }
       
}
