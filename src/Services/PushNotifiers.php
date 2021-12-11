<?php

namespace App\Services;

use App\Entity\RepoMain;
use App\Entity\UsContacts;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;

class PushNotifiers
{
    private $em;
    private $client;
    private $params;
    private $urlPush = 'https://fcm.googleapis.com/fcm/send';
    private $key = 'AAAAlrdO5NY:APA91bFvQ5C9Sx2-HcrFJSdCf3gr42tD7wAyQYXJhTr4MzCI-yJq5bR1ToBmvkNbl1NtXP8L3bxOpGKq6igh-LFovrwbzwkKgUQAlv8zGYJ4E4QHlLH5XRbghm3aCYd8lmYRS1-BtXTy';

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->params = $params;
        $this->em = $entityManager;
    }
    
    /**
     * La solicitud esta en Status 2
    */
    public function notificarNewSolicitud($idRepo): array
    {   
        $tipo = 'sol';
        $opt = $this->getOptions();
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getNotificationSegunTipo($tipo);
        $opt['json']['data'] = $this->getCargaUtilSegunTipo($tipo);
        $opt['json']['data']['id_repo'] = $idRepo;

        $uriTokensEyes = $this->params->get('empTkWorker');
        $finder = new Finder();
        $finder->files()->in($uriTokensEyes);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $opt['json']['registration_ids'][] = $file->getContents();
            }
        }

        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 3
    */
    public function notificarSolicitudTomada($idRepo): array
    {   
        $tipo = 'take_sol';
        $opt = $this->getOptions();
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getNotificationSegunTipo($tipo);
        $opt['json']['data'] = $this->getCargaUtilSegunTipo($tipo);
        $opt['json']['data']['id_repo'] = $idRepo;

        $uriTokensEyes = $this->params->get('empTkWorker');
        $finder = new Finder();
        $finder->files()->in($uriTokensEyes);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $opt['json']['registration_ids'][] = $file->getContents();
            }
        }
        $repo = $this->getRepoById($idRepo);
        if($repo) {
            $opt['json']['data']['cat_pzas'] = count($repo['pzas']);
            $tokens = $this->getTokensContacByIdUser($repo['own']);
            $rota = count($tokens);
            for ($i=0; $i < $rota; $i++) { 
                $opt['json']['registration_ids'][] = $tokens[$i];
            }
        }
        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 5
    */
    public function notificarRespuestas($idRepo, $infoBody): array
    {   
        $tipo = 'resp';
        $opt = $this->getOptions();
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getNotificationSegunTipo($tipo);
        $opt['json']['data'] = $this->getCargaUtilSegunTipo($tipo);
        $opt['json']['data']['id_repo'] = $idRepo;
        $opt['json']['data']['body'] = $infoBody;

        $uriTokensEyes = $this->params->get('empTkWorker');
        $finder = new Finder();
        $finder->files()->in($uriTokensEyes);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $opt['json']['registration_ids'][] = $file->getContents();
            }
        }
        $repo = $this->getRepoById($idRepo);
        if($repo) {
            $opt['json']['data']['cat_pzas'] = count($repo['pzas']);
            $tokens = $this->getTokensContacByIdUser($repo['own']);
            $rota = count($tokens);
            for ($i=0; $i < $rota; $i++) { 
                $opt['json']['registration_ids'][] = $tokens[$i];
            }
        }
        return $this->send($opt);
    }

    /**
     * Pruebas de comunicacion hacia el Id del usuario
    */
    public function sendPushTestTo($idUser): array
    {   
        $tipo = 'pcom';
        $opt = $this->getOptions();
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getNotificationSegunTipo($tipo);
        $opt['json']['data'] = $this->getCargaUtilSegunTipo($tipo);

        $tokens = $this->getTokensContacByIdUser($idUser);
        $rota = count($tokens);
        for ($i=0; $i < $rota; $i++) { 
            $opt['json']['registration_ids'][] = $tokens[$i];
        }
        return $this->send($opt);
    }

    /** */
    public function sendPushTo($token, string $tipo, array $data = []): array
    {   
        $opt = $this->getOptions();

        $opt['json']['registration_ids'] = is_array($token) ? $token : [$token];
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getTitleAndBodySegunTipo($tipo);
        $data['tipo'] = $tipo;
        $data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
        $opt['json']['data'] = $data;

        return $this->send($opt);
    }

    /** */
    private function getOptions(): array {

        // time_to_live => 172800 (segundos) son 48 horas de vida
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ],
            'json' => [
                'registration_ids' => [],
                'direct_boot_ok' => true,
                'android_channel_id' => 'autoparnet_push',
                'priority' => 'high',
                'time_to_live' => 172800,
                'android' => [
                    'priority' => 'high',
                ],
            ],
        ];
    }

    ///
    public function getChannelSegunTipo($tipo) : string
    {
        $seccion = '';
        switch ($tipo) {
            case 'pcom':
                $seccion = 'RESCOT';
                break;
            default:
                $seccion = 'RESCOT';
                break;
        }
        return $seccion;
    }

    /** */
    private function getCargaUtilSegunTipo($tipo) {

        $data = $this->getTitleAndBodySegunTipo($tipo);
        return [
            'tipo' => $data['tipo'],
            'title' => $data['title'],
            'body'  => $data['body'],
            'sound' => '',
            'ttl'   => 0,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];
    }

    /** */
    private function getNotificationSegunTipo($tipo) {

        $data = $this->getTitleAndBodySegunTipo($tipo);
        return [
            'title' => $data['title'],
            'body'  => $data['body'],
            'sound' => $data['sound'],
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];
    }

    ///
    public function getTitleAndBodySegunTipo($tipo) : array
    {
        $content = '';
        switch ($tipo) {
            case 'sol':
                $content = [
                    'tipo' => 'sol',
                    'title' => 'SOLICITUD DE COTIZACIÓN',
                    'body' => 'Oportunidad de Venta, un Cliente esta solicitando una nueva cotización de Autopartes',
                    'sound' => '',
                ];
                break;
            case 'take_sol':
                $content = [
                    'tipo' => 'take_sol',
                    'title' => 'SOLICITUD ATENDIDA',
                    'body' => 'La solicitud ya fué tomada por el SCP',
                    'sound' => '',
                ];
                break;
            case 'pcom':
                $content = [
                    'tipo' => 'pcom',
                    'title' => 'PRUEBA DE COMUNICACIÓN',
                    'body' => 'La comunicación con el Servidor fué exitosa',
                    'sound' => '',
                ];
                break;
            case 'cot':
                $content = [
                    'tipo' => '...',
                    'title' => 'TENDRÁS ESTA REFACCIÓN',
                    'body' => 'Oportunidad de Venta::AutoparNet',
                    'sound' => '',
                ];
                break;
            case 'resp':
                $content = [
                    'tipo' => 'resp',
                    'title' => 'RESPUESTAS RECIBIDAS',
                    'body' => 'Haz recibido respuestas para una solicitud de cotización',
                    'sound' => '',
                ];
                break;
            default:
                $content = [
                    'tipo' => '...',
                    'title' => 'SIN CLASIFICAR',
                    'body' => 'No se encontró el tipo de Notificación',
                    'sound' => '',
                ];
                break;
        }
        return $content;
    }

    /** */
    private function send(array $opt)
    {
        $response = $this->client->request('POST', $this->urlPush, $opt);

        $content = '';
        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            $content = $response->toArray();
        }else{
            $content = ['abort' => true, 'body' => 'codigo ' .$statusCode];
        }
        return $content;
    }

    /** */
    private function getRepoById($idRepo): array
    {
        $dql = 'SELECT rep, partial sts.{id, nombre}, partial pzas.{id} FROM ' . RepoMain::class . ' rep '.
        'JOIN rep.status sts '.
        'JOIN rep.pzas pzas '.
        'WHERE rep.id = :idRepo';
        $result = $this->em->createQuery($dql)->setParameter('idRepo', $idRepo)->getArrayResult();
        if($result) {
            return $result[0];
        }
        return [];
    }

    /** */
    private function getTokensContacByIdUser($idUser): array
    {
        $dql = 'SELECT ct FROM ' . UsContacts::class . ' ct '.
        'WHERE ct.user = :idUser';
        $result = $this->em->createQuery($dql)->setParameter('idUser', $idUser)->execute();
        if($result) {
            return [
                $result[0]->getNotifiKey(),
                $result[0]->getNotifWeb(),
            ];
        }
        return [];
    }
}