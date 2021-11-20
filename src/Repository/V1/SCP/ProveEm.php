<?php

namespace App\Repository\V1\SCP;

use App\Entity\UsContacts;
use Doctrine\ORM\EntityManagerInterface;

class ProveEm
{
    private $em;
    private $result = ['abort' => false, 'msg' => 'ok', 'body' => []];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @see PushesController::setTokenMessaging
     */
    public function updateTokenPushByIdUser(array $data) {
        
        $toSave = ($data['toSafe'] == 'app') ? 'ct.notifiKey' : 'ct.notifWeb';

        $dql = 'UPDATE ' . UsContacts::class . ' ct '.
        'SET '.$toSave.' = :token '.
        'WHERE ct.user = :user';

        $res = $this->em->createQuery($dql)->setParameters([
            'user' => $data['user'],
            'token'=> $data['token']
        ])->execute();
        return $this->result;
    }
}