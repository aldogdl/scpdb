<?php

namespace App\Repository\V1;

use App\Entity\Resenias;
use App\Entity\UsAdmin;
use App\Entity\UsContacts;
use App\Entity\UsEmpresa;
use Doctrine\ORM\EntityManagerInterface;

class ReseniasEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /** */
    public function toArray($obj) {
        return [
            'res_id' => $obj->getId(),
            'res_fromNombre' => $obj->getFromNombre(),
            'res_toSlug' => $obj->getToSlug(),
            'res_calif' => $obj->getCalif(),
            'res_resenia' => $obj->getResenia(),
            'res_isPublic' => $obj->getIsPublic(),
            'res_createdAt' => $obj->getCreatedAt(),
        ];
    }

    /** */
    public function setResenias($resenia)
    {
        $dqlEmp = $this->getEmpresaBySlug($resenia['to']);
        $emp = $dqlEmp->getResult();
        if($emp) {
            $obj = new Resenias();
            $obj->setEmp($emp[0]);
            $obj->setFromNombre($resenia['from']);
            $obj->setToSlug($emp[0]->getSlug());
            $obj->setCalif($resenia['tipo']);
            $obj->setResenia($resenia['resenia']);

            try {
                $this->em->persist($obj);
                $this->em->flush();
                $this->result['body'] = $obj->getId();
            } catch (\Throwable $th) {
                $this->result['abort'] = false;
                $this->result['body'] = 'Error::No se guardó la Reseñia, inténtalo nuevamente por favor';
            }
        }
        return $this->result;
    }

    /** */
    public function getReseniasCountByIdEmp(int $idEmp) {

        $contador = ['bueno' => 0, 'malo' => 0, 'regular' => 0];
        $dql = 'SELECT partial res.{id}, COUNT(res.id) AS numRes FROM ' . Resenias::class . ' res '.
            'WHERE res.emp = :idEmp AND res.calif = :calif';

        $result = $this->em->createQuery($dql)->setParameters([
            'idEmp' => $idEmp,
            'calif' => 'Bueno'
        ])->getScalarResult();
        if($result) {
            $contador['bueno'] = $result[0]['numRes'];
        }
        $result = $this->em->createQuery($dql)->setParameters([
            'idEmp' => $idEmp,
            'calif' => 'Malo'
        ])->getScalarResult();
        if($result) {
            $contador['malo'] = $result[0]['numRes'];
        }
        $result = $this->em->createQuery($dql)->setParameters([
            'idEmp' => $idEmp,
            'calif' => 'Regular'
        ])->getScalarResult();
        if($result) {
            $contador['regular'] = $result[0]['numRes'];
        }
        return $contador;

        // Un Ejemplo de Condiciones en la consulta.
        // $dql = 'SELECT res, SUM(CASE WHEN p.image = 1 THEN 1 ELSE 0 END) AS numImage
        //     FROM Bundle\Entity\Turn t
        //     JOIN t.pois p
        //     GROUP BY t.id
        //     ORDER BY numImage DESC';

    }

    /** */
    public function getReseniasActiveByIdEmp(int $idEmp) {

        $dql = 'SELECT res FROM ' . Resenias::class . ' res '.
        'WHERE res.emp = :idEmp';
        return $this->em->createQuery($dql)->setParameter('idEmp', $idEmp);
    }

    /** */
    public function getEmpresaBySlug(string $slug) {

        $dql = 'SELECT emp FROM ' . UsEmpresa::class . ' emp '.
        'WHERE emp.slug = :slug';
        return $this->em->createQuery($dql)->setParameter('slug', $slug);
    }
}
