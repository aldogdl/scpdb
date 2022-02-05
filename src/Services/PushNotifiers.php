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
        $data = ['tipo' => 'sol', 'id_repo' => $idRepo];
        $opt = $this->getOptions($data);
        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 3
    */
    public function notificarSolicitudTomada($idRepo): array
    {   
        $data = ['tipo' => 'take_sol', 'id_repo' => $idRepo];
        $opt = $this->getOptions($data);
        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 5
    */
    public function notificarRespuestas($idRepo, $infoBody): array
    {   
        $data = ['tipo' => 'resp', 'id_repo' => $idRepo, 'body' => $infoBody];
        $opt = $this->getOptions($data);
        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 6
    */
    public function notificarLeidaPorElCliente($idRepo): array
    {   
        $data = ['tipo' => 'leida', 'id_repo' => $idRepo];
        $opt = $this->getOptions($data);
        return $this->send($opt);
    }

    /**
     * La solicitud esta en Status 8
    */
    public function notificarPedido($idRepo): array
    {   
        $data = ['tipo' => 'pedi', 'id_repo' => $idRepo];
        $opt = $this->getOptions($data);
        return $this->send($opt);
    }

    /** */
    private function getOptions(array $data): array {

        // time_to_live => 172800 (segundos) son 48 horas de vida
        $opt = [
            'name' => '',
            'registration_ids' => [],
            'priority' => 'high',
            'notification_priority' => 'PRIORITY_HIGH',
            'ttl' => '0s',
            'time_to_live' => 172800,
            'direct_boot_ok' => true,
            'sound' => 'cotizaciones.mp3',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'channel_id' => '',
            'default_vibrate_timings' => true,
            'notification' => [
                'title' => '',
                'body'  => '',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => [],
            'webpush' => [
                'fcm_options' => [
                    'link' => ''
                ],
            ]
        ];

        $deploy = $this->getTitleAndBodySegunTipo($data['tipo']);
        $opt['notification']['title'] = $deploy['title'];
        $opt['notification']['body'] = $deploy['body'];
        $opt['channel_id'] = $this->getChannelSegunTipo($data['tipo']);
        $opt['data'] = $data;
        $opt['data']['title'] = $opt['notification']['title'];
        if(!array_key_exists('body', $data)) {
            $opt['data']['body'] = $opt['notification']['body'];
        }
        if( array_key_exists('id_repo', $data)) {
            $opt['name'] = $data['id_repo'];
        }else{
            $opt['name'] = 'autoparnet';
        }

        switch ($data['tipo']) {
            case 'sol':
                $opt['registration_ids'] = $this->getTokensSCP($opt['registration_ids']);
                break;
            case 'leida':
                $opt['registration_ids'] = $this->getTokensSCP($opt['registration_ids']);
                break;
            case 'pedi':
                $opt['registration_ids'] = $this->getTokensSCP($opt['registration_ids']);
                break;
            case 'take_sol':
                $opt = $this->getTokensSCPandContact($data['id_repo'], $opt);
                break;
            case 'resp':
                $opt = $this->getTokensSCPandContact($data['id_repo'], $opt);
                break;
            
            default:
                # code...
                break;
        }

        return $opt;
    }

    ///
    private function getTokensSCPandContact($idMain, $opt): array{

        $repo = $this->getRepoById($idMain);
        if($repo) {
            $opt['data']['cat_pzas'] = count($repo['pzas']);
            $opt['data']['statusId'] = $repo['status']['id'];
            $opt['data']['statusNom'] = $repo['status']['nombre'];
            $tokens = $this->getTokensContacByIdUser($repo['own']);
            $rota = count($tokens);
            for ($i=0; $i < $rota; $i++) {
                if(!in_array($tokens[$i], $opt['registration_ids'])) {
                    $opt['registration_ids'][] = $tokens[$i];
                }
            }
        }
        $opt['registration_ids'] = $this->getTokensSCP($opt['registration_ids']);
        return $opt;
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
    
    ///
    private function getTokensSCP($tokens): array
    {
        $uriTokensEyes = $this->params->get('empTkWorker');
        $finder = new Finder();
        $finder->files()->in($uriTokensEyes);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $nt = $file->getContents();
                if(!in_array($nt, $tokens)) {
                    $tokens[] = $nt;
                }
            }
        }
        file_put_contents('tokenes.txt', $tokens);
        return $tokens;
    }

    ///
    public function getTitleAndBodySegunTipo($tipo) : array
    {
        $content = '';
        switch ($tipo) {
            case 'sol':
                $content = [
                    'title' => 'SOLICITUD DE COTIZACIÓN',
                    'body' => 'Oportunidad de Venta, un Cliente esta solicitando una nueva cotización de Autopartes',
                ];
                break;
            case 'take_sol':
                $content = [
                    'title' => 'SOLICITUD ATENDIDA',
                    'body' => 'La solicitud ya fué tomada por el SCP',
                ];
                break;
            case 'pcom':
                $content = [
                    'title' => 'PRUEBA DE COMUNICACIÓN',
                    'body' => 'La comunicación con el Servidor fué exitosa',
                ];
                break;
            case 'resp':
                $content = [
                    'title' => 'RESPUESTAS RECIBIDAS',
                    'body' => 'Haz recibido respuestas para una solicitud de cotización',
                ];
                break;
            case 'leida':
                $content = [
                    'title' => 'RESPUESTA LEIDA',
                    'body' => 'El Cliente acaba de leer las respuestas',
                ];
                break;
            case 'pedi':
                $content = [
                    'title' => 'FELICIDADES HAY PEDIDO',
                    'body' => 'Un Cliente acaba de comprar REFACCIONES',
                ];
                break;
            default:
                $content = [
                    'tipo' => '...',
                    'title' => 'SIN CLASIFICAR',
                    'body' => 'No se encontró el tipo de Notificación',
                ];
                break;
        }
        return $content;
    }

    /**
     * Sin uso, creo, analizar para borrar
    */
    public function sendPushTo($token, string $tipo, array $data = []): array
    {   
        $opt = [];

        $opt['json']['registration_ids'] = is_array($token) ? $token : [$token];
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getTitleAndBodySegunTipo($tipo);
        $data['tipo'] = $tipo;
        $data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
        $opt['json']['data'] = $data;

        return $this->send($opt);
    }

    /**
     * Pruebas de comunicacion hacia el Id del usuario
    */
    public function sendPushTestTo($idUser): array
    {   
        $tipo = 'pcom';
        $opt = [];
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $tokens = $this->getTokensContacByIdUser($idUser);
        $rota = count($tokens);
        for ($i=0; $i < $rota; $i++) {
            if(!in_array($tokens[$i], $opt['json']['registration_ids'])) {
                $opt['json']['registration_ids'][] = $tokens[$i];
            }
        }
        return $this->send($opt);
    }

    /** */
    private function send(array $opt)
    {
        $dataSend = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ],
            'json' => $opt
        ];

        $response = $this->client->request('POST', $this->urlPush, $dataSend);

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