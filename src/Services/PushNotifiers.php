<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PushNotifiers
{
    private $client;
    private $params;
    private $urlPush = 'https://fcm.googleapis.com/fcm/send';
    private $key = 'AAAAlrdO5NY:APA91bFvQ5C9Sx2-HcrFJSdCf3gr42tD7wAyQYXJhTr4MzCI-yJq5bR1ToBmvkNbl1NtXP8L3bxOpGKq6igh-LFovrwbzwkKgUQAlv8zGYJ4E4QHlLH5XRbghm3aCYd8lmYRS1-BtXTy';

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->params = $params;
    }
    
    /** */
    public function notificarNewSolicitud($idRepo): array
    {   
        $opt = $this->getOptions();

        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo('sol');
        $opt['json']['data'] = $this->getCargaUtilSegunTipo('sol');
        $opt['json']['notification'] = $this->getNotificationSegunTipo('sol');

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
                $seccion = 'pcom';
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
            'title' => $data['title'],
            'body'  => $data['body'],
            'sound' => '',
            'ttl'          => 0,
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
            case 'pcom':
                $content = [
                    'tipo' => '...',
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
                    'tipo' => '...',
                    'title' => 'RESPUESTA RECIBIDA',
                    'body' => 'Un Parnet ha respondido a una COTIZACIÓN',
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

}