<?php

namespace App\Repository\V1\CpanelWeb;

use App\Entity\LO4Localidades;
use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use App\Entity\UsSucursales;
use App\Repository\V1\UsContactsEm;
use App\Repository\V1\UsEmpresaEm;
use App\Repository\V1\UsSucursalesEm;
use Doctrine\ORM\EntityManagerInterface;

class PnlEmpresaEm extends UsEmpresaEm
{
  private $em;
  private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->em = $entityManager;
    parent::__construct($entityManager);
  }

  /** */
  public function getIdEmpresaByIdSuc($idSuc)
  {
    $dql = 'SELECT partial suc.{id}, partial em.{id} ' .
      'FROM ' . UsSucursales::class . ' suc ' .
      'JOIN suc.empresa em '.
      'WHERE suc.id = :id';

    return $this->em->createQuery($dql)->setParameter('id', $idSuc);
  }

  /** */
  public function getLastEmpresas($idEmp)
  {
    $where = ($idEmp != 0) ? 'WHERE em.id = :idEmp ' : '';

    $dql = 'SELECT partial em.{id, nombre}, ' .
      'partial suc.{id} as suc_id, ' .
      'partial t.{id, tipo, role}, ' .
      'partial avo.{id, username} ' .
      'FROM ' . UsEmpresa::class . ' em ' .
      'JOIN em.tipo t ' .
      'LEFT JOIN em.sucursales suc ' .
      'LEFT JOIN em.avo avo ' .
      $where .
      'GROUP BY em.id ' .
      'ORDER BY em.id DESC';

    if($idEmp != 0) {
      return $this->em->createQuery($dql)->setParameter('idEmp', $idEmp);
    }
    return $this->em->createQuery($dql)->setMaxResults(20);
  }

  /** */
  public function getAllSucursalesByIdEmpresa($idEmp)
  {
    $dql = 'SELECT partial suc.{id, domicilio}, ' .
      'loc, ' .
      'partial cd.{id, nombre} ' .
      'FROM ' . UsSucursales::class . ' suc ' .
      'JOIN suc.localidad loc ' .
      'JOIN loc.ciudad cd ' .
      'WHERE suc.empresa = :empresa ' .
      'ORDER BY suc.domicilio ASC';

    return $this->em->createQuery($dql)->setParameter('empresa', $idEmp);
  }

  /** */
  public function getContactsTokenPush()
  {
    $dql = 'SELECT partial cts.{id, notifiKey}, partial emp.{id}, partial suc.{id, palclas} ' .
      'FROM ' . UsContacts::class . ' cts ' .
      'JOIN cts.sucursal suc ' .
      'JOIN suc.empresa emp ' .
      'WHERE cts.notifiKey != :notif AND emp.tipo = :tipoSocio ' .
      'ORDER BY cts.nombre ASC';

    return $this->em->createQuery($dql)->setParameters([
      'notif' => '0',
      'tipoSocio' => 1,
    ]);
  }

  /** */
  public function getDataSucursalById($idSuc)
  {
    $dql = 'SELECT suc, partial emp.{id, marcas, logo}, loc, partial cd.{id, nombre} ' .
      'FROM ' . UsSucursales::class . ' suc ' .
      'JOIN suc.empresa emp ' .
      'JOIN suc.localidad loc ' .
      'JOIN loc.ciudad cd ' .
      'WHERE suc.id = :id ' .
      'ORDER BY suc.domicilio ASC';

    return $this->em->createQuery($dql)->setParameter('id', $idSuc);
  }

  /** */
  public function getSucursalById($idSuc)
  {
    $dql = 'SELECT suc FROM ' . UsSucursales::class . ' suc ' .
      'WHERE suc.id = :id';
    return $this->em->createQuery($dql)->setParameter('id', $idSuc);
  }

  /** */
  public function getContactosByIdSucursal($idSuc)
  {
    $dql = 'SELECT ct, partial suc.{id} FROM ' . UsContacts::class . ' ct ' .
      'JOIN ct.sucursal suc ' .
      'WHERE ct.sucursal = :idSuc ' .
      'ORDER BY ct.nombre ASC';

    return $this->em->createQuery($dql)->setParameter('idSuc', $idSuc);
  }

  /** */
  public function getContactosByIdUser($id)
  {

    $dql = 'SELECT ct, partial suc.{id} FROM ' . UsContacts::class . ' ct ' .
      'JOIN ct.sucursal suc ' .
      'WHERE ct.user = :id ' .
      'ORDER BY ct.nombre ASC';

    return $this->em->createQuery($dql)->setParameter('id', $id);
  }

  /** */
  public function getMoreDataEmpresa($idEmp)
  {
    $dql = 'SELECT emp FROM ' . UsEmpresa::class . ' emp ' .
      'WHERE emp.id = :id ';
    return $this->em->createQuery($dql)->setParameter('id', $idEmp);
  }

  /** */
  public function getDataBuildPerpza() {

    $dql = 'SELECT ct, partial suc.{id, telefono, palclas, latLng}, '.
    'partial emp.{id, nombre, marcas}, partial usu.{id, roles} '.
    'FROM ' . UsContacts::class . ' ct ' .
    'JOIN ct.user usu ' .
    'JOIN ct.sucursal suc ' .
    'JOIN suc.empresa emp ' .
    'WHERE usu.roles LIKE :role '.
    'ORDER BY emp.nombre ASC';

    return $this->em->createQuery($dql)->setParameter('role', '%ROLE_SOCIO_PROV%');
  }

  /** */
  public function updateDataEmpresa($data)
  {
    $dql = $this->getEmpresaById($data['id']);
    $obj = $dql->getResult();
    if ($obj) {
      $obj = $obj[0];
      $obj->setNombre($data['nombre']);
      $obj->setDespeq($data['despeq']);
      $obj->setNotas($data['notas']);
      $obj->setPagWeb($data['pagWeb']);
      $obj->setRazonSocial($data['razonSocial']);
      $obj->setRfc($data['rfc']);
      $obj->setDomFiscal($data['domFiscal']);
      try {
        $this->em->persist($obj);
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = 'No se encontró la empresa ' . $data['nombre'] . ' con el ID ' . $data['id'];
    }
    return $this->result;
  }

  /** */
  public function updateDataLocalidad($data)
  {
    // {"idSuc":1, "idCd":1,"idCl":3,"cp":"44460","latLng":"20.6600880413848, -103.33499727369052"}
    $dql = $this->getSucursalById($data['idSuc']);
    $obj = $dql->getResult();
    
    if ($obj) {

      $obj = $obj[0];

      $obj->setLocalidad($this->em->getPartialReference(LO4Localidades::class, $data['idCl']));
      $obj->setLatLng($data['latLng']);
      $obj->setCp($data['cp']);

      try {
        $this->em->persist($obj);
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = 'No se encontró la sucursal con el ID ' . $data['id'];
    }
    return $this->result;
  }

  /** */
  public function updateDataSucursal($data)
  {
    $dql = $this->getSucursalById($data['id']);
    $obj = $dql->getResult();
    
    if ($obj) {

      $obj = $obj[0];

      $obj->setDomicilio($data['domicilio']);
      $obj->setEntreAntes($data['entreAntes']);
      $obj->setEntreDespues($data['entreDespues']);
      $obj->setReferencias($data['referencias']);
      $obj->setTelefono($data['telefono']);
      try {
        $this->em->persist($obj);
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = 'No se encontró la sucursal ' . $data['domicilio'] . ' con el ID ' . $data['id'];
    }
    return $this->result;
  }

  /** */
  public function updateDataContactos($data)
  {
    $contactos = new UsContactsEm($this->em);
    $ids = [];
    $rota = count($data);
    for ($i = 0; $i < $rota; $i++) {
      $ids[] = $data[$i]['id'];
    }
    $dql = $contactos->getContactoByIds($ids);
    $objs = $dql->getResult();
    if ($objs) {
      $vueltas = count($objs);
      for ($o = 0; $o < $vueltas; $o++) {
        $contac = [];
        $obj = $objs[$o];
        for ($i = 0; $i < $rota; $i++) {
          if ($data[$i]['id'] == $objs[$o]->getId()) {
            $contac = $data[$i];
            break;
          }
        }
        if (count($contac) > 0) {
          $obj->setNombre($contac['nombre']);
          $obj->setCelular($contac['celular']);
          $obj->setCargo($contac['cargo']);
          $this->em->persist($obj);
        }
      }
      try {
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = 'No se encontraron uno o mas Contactos';
    }
    return $this->result;
  }

  /** */
  public function updateDataFachada($data)
  {
    $idSuc = $data['idSuc'];
    $sucursal = new UsSucursalesEm($this->em);
    $dql = $sucursal->getSucursalesById($idSuc);
    $obj = $dql->getResult();

    $delImages = [];

    if ($obj) {
      $obj = $obj[0];
      $facha = $obj->getFachada();
      if (strpos($facha, '[') !== false) {
        $images = json_decode($facha, true);
        $rota = count($images);
        if ($rota > 1) {
          for ($i = 0; $i < $rota; $i++) {
            if ($images[$i] != $data['image']) {
              $delImages[] = $images[$i];
            }
          }
        }
      }

      $obj->setFachada($data['image']);
      try {
        $this->em->flush();
        $this->result['body'] = [
          'idEmp' => $obj->getEmpresa()->getId(),
          'images'=> $delImages
        ];
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    }
    return $this->result;
  }

  /** */
  public function updateDataLogotipo($data)
  {
    $dql = $this->getEmpresaById($data['idEmp']);
    $obj = $dql->getResult();

    $delImages = [];
    if ($obj) {
      $obj = $obj[0];
      $logo = $obj->getLogo();
      if (strpos($logo, '[') !== false) {
        $images = json_decode($logo, true);
        $rota = count($images);
        if ($rota > 1) {
          for ($i = 0; $i < $rota; $i++) {
            if ($images[$i] != $data['image']) {
              $delImages[] = $images[$i];
            }
          }
        }
      }
      $obj->setLogo($data['image']);
      try {
        $this->em->flush();
        $this->result['body'] = $delImages;
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    }
    return $this->result;
  }

  /** */
  public function updateDataPalclas($data)
  {
    $idSuc = $data['idSuc'];
    $data = $data['palclas'];
    $rota = count($data);
    // Filtrar las palabras que se hallan editado.
    for ($i = 0; $i < $rota; $i++) {
      if (!$data[$i]['echo']) {
        unset($data[$i]);
      }
    }
    sort($data);
    $sucursal = new UsSucursalesEm($this->em);
    $dql = $sucursal->getSucursalesById($idSuc);
    $obj = $dql->getResult();
    if ($obj) {
      $obj = $obj[0];
      $palOlds = $obj->getPalclas();
      $palabras = explode(',', $palOlds);

      $rota = count($data);
      for ($i = 0; $i < $rota; $i++) {
        $vueltas = count($palabras);
        $isNew = true;
        for ($p = 0; $p < $vueltas; $p++) {
          if (strtolower($data[$i]['oldSafe']) == strtolower($palabras[$p])) {
            $palabras[$p] = $data[$i]['palabra'];
            $isNew = false;
            break;
          }
        }
        if($isNew) {
          $palabras[] = $data[$i]['palabra'];
        }
      }
      $palOlds = implode(',', $palabras);
      $obj->setPalclas(strtoupper($palOlds));
      try {
        $this->em->persist($obj);
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = '[PalClas] No se encontró la sucursal con el ID ' . $idSuc;
    }

    return $this->result;
  }

  /** */
  public function updateDataMarcas($data)
  {
    $dql = $this->getEmpresaById($data['idEmp']);
    $obj = $dql->getResult();
    if ($obj) {
      $obj = $obj[0];
      $obj->setMarcas($data['marcas']);
      try {
        $this->em->persist($obj);
        $this->em->flush();
      } catch (\Throwable $th) {
        $this->result['abort'] = true;
        $this->result['msg'] = 'error';
        $this->result['body'] = $th->getMessage();
      }
    } else {
      $this->result['abort'] = true;
      $this->result['msg'] = 'error';
      $this->result['body'] = '[Marcas] No se encontró la empresa con el ID ' . $data['idEmp'];
    }
    return $this->result;
  }

}
