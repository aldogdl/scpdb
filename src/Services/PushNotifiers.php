<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PushNotifiers
{
    private $client;
    private $urlPush = 'https://fcm.googleapis.com/fcm/send';
    private $key = 'AAAAlrdO5NY:APA91bFvQ5C9Sx2-HcrFJSdCf3gr42tD7wAyQYXJhTr4MzCI-yJq5bR1ToBmvkNbl1NtXP8L3bxOpGKq6igh-LFovrwbzwkKgUQAlv8zGYJ4E4QHlLH5XRbghm3aCYd8lmYRS1-BtXTy';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    /** */
    public function sendPushTo(String | array $token, string $tipo, array $data = []): array
    {   
        $opt = $this->getOptions();

        $opt['json']['registration_ids'] = is_array($token) ? $token : [$token];
        $opt['json']['data']['tipo'] = $tipo;
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        $opt['json']['notification'] = $this->getTitleAndBodySegunTipo($tipo);
        $data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
        $opt['json']['data'] = $data;
                
        return $this->send($opt);
    }

    /** */
    private function getOptions(): array {

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
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'autoparnet_push'
                    ]
                ],
                'data' => [
                    'ttl'          => 0,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'notification' => [],
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

    ///
    public function getTitleAndBodySegunTipo($tipo) : array
    {
        $content = '';
        switch ($tipo) {
            case 'pcom':
                $content = [
                    'title' => 'PRUEBA DE COMUNICACIÓN',
                    'body' => 'La comunicación con el Servidor fué exitosa',
                ];
                break;
            case 'cot':
                $content = [
                    'title' => 'TENDRÁS ESTA REFACCIÓN',
                    'body' => 'Oportunidad de Venta::AutoparNet',
                ];
                break;
            case 'resp':
                $content = [
                    'title' => 'RESPUESTA RECIBIDA',
                    'body' => 'Un Parnet ha respondido a una COTIZACIÓN',
                ];
                break;
            default:
                $content = [
                    'title' => 'SIN CLASIFICAR',
                    'body' => 'No se encontró el tipo de Notificación',
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