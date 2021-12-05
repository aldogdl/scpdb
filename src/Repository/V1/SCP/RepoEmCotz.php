<?php

namespace App\Repository\V1\SCP;

use App\Entity\AO1Marcas;
use App\Entity\AO2Modelos;
use App\Entity\RepoMain;
use App\Entity\RepoPzaInfo;
use App\Entity\RepoPzas;
use App\Entity\SisCategos;
use App\Entity\Sistemas;
use App\Entity\StatusTypes;
use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Repository\V1\SCP\RepoEm;
use Doctrine\ORM\EntityManagerInterface;

class RepoEmCotz extends RepoEm
{

    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->em = $entityManager;
    }

    /** */
    public function getAllMarcas()
    {
        $dql = 'SELECT mk FROM ' . AO1Marcas::class . ' mk ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllSistems()
    {
        $dql = 'SELECT sis FROM ' . Sistemas::class . ' sis ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllCategos()
    {
        $dql = 'SELECT cat FROM ' . SisCategos::class . ' cat ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllModelos()
    {
        $dql = 'SELECT md, partial mk.{id} FROM ' . AO2Modelos::class . ' md '.
        'JOIN md.marca mk '.
        'ORDER BY mk.id ASC';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getAllStatus()
    {
        $dql = 'SELECT st FROM ' . StatusTypes::class . ' st ';
        return $this->em->createQuery($dql)->getScalarResult();
    }

    /** */
    public function getRepoById(int $idRepo)
    {
        $idStatus = 3;
        $dql = $this->getRepoMainAndPiezasByIdMain([$idRepo]);
        $this->changeStatusRepoPzaByIdRepo($idRepo, $idStatus);
        $this->changeStatusRepoMain($idRepo, $idStatus);
        return $dql;
    }

    /** */
    public function updateFotoDeRespuesta($idInfo, $fotos)
    {
        $dql = $this->getRepoInfoById($idInfo);
        $has = $dql->execute();
        if($has) {
            $obj = $has[0];
            $obj->setFotos($fotos);
            try {
                $this->em->persist($obj);
                $this->em->flush();
                $this->result['abort'] = false;
                $this->result['body']  = $obj->getId();
            } catch (\Throwable $th) {
                $this->result['abort'] = true;
                $this->result['body']  = 'Error al guardar las Fotos.';
            }
        } 
        return $this->result;
    }

    /** */
    public function saveDataRespuesta($resp)
    {
        $obj = null;
        $idStatus = 5;

        if(array_key_exists('id_info', $resp)) {
            // Buscamos el registro
            $dql = $this->getRepoInfoById($resp['id_info']);
            $has = $dql->execute(); 
            if($has) {
                $obj = $has[0];
            } 
        }

        if($obj == null) {
            $obj = new RepoPzaInfo();
            $obj->setRepo($this->em->getPartialReference(RepoMain::class, $resp['id_main']));
            $obj->setPzas($this->em->getPartialReference(RepoPzas::class, $resp['idPz']));
            $obj->setStatus($this->em->getPartialReference(StatusTypes::class, $idStatus));
            $obj->setOwn($this->em->getPartialReference(UsContacts::class, $resp['idCt']));
            $obj->setIdTmpPza($resp['idTm']);
        }

        $obj->setCaracteristicas($resp['carac']);
        $obj->setDetalles($resp['deta']);
        $obj->setCosto($resp['costo']);
        $obj->setPrecio($resp['precio']);
        $comi = (float) $resp['precio'] - (float) $resp['costo'];
        $obj->setComision($comi);
        $obj->setSistema($this->em->getPartialReference(Sistemas::class, $resp['sistem']));
        $obj->setSisCat($this->em->getPartialReference(SisCategos::class, $resp['catego']));

        try {

            $this->em->persist($obj);
            $this->em->flush();
            $this->result['abort'] = false;
            $this->result['body']  = $obj->getId();
            $this->changeStatusRepoPza($resp['idPz'], $idStatus);
            $this->changeStatusRepoMain($resp['id_main'], $idStatus);

        } catch (\Throwable $th) {
            $this->result['abort'] = true;
            $this->result['body']  = 'Error al guardar la Respuesta.';
        }

        return $this->result;
    }

    ///
    public function changeStatusRepoMain($idMain, $idStatus) {

        $dql = 'UPDATE ' . RepoMain::class . ' repo ' .
        'SET repo.status = :newStatus '.
        'WHERE repo.id = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idMain,
        ]);
    }

    ///
    public function changeStatusRepoPza($idPza, $idStatus) {

        $dql = 'UPDATE ' . RepoPzas::class . ' pza ' .
        'SET pza.status = :newStatus '.
        'WHERE pza.id = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idPza,
        ]);
    }

    ///
    public function changeStatusRepoPzaByIdRepo($idMain, $idStatus) {

        $dql = 'UPDATE ' . RepoPzas::class . ' pza ' .
        'SET pza.status = :newStatus '.
        'WHERE pza.repo = :id';
        return $this->em->createQuery($dql)->setParameters([
            'newStatus' => $this->em->getPartialReference(StatusTypes::class, $idStatus),
            'id' => $idMain,
        ]);
    }

    ///
    public function getRepoInfoById($idInfo) {

        $dql = 'SELECT inf FROM ' . RepoPzaInfo::class . ' inf ' .
        'WHERE inf.id = :idInfo';
        return $this->em->createQuery($dql)->setParameter('idInfo', $idInfo);
    }
}