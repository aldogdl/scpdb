<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PushNotifiers
{
    private $client;
    private $urlPush = 'https://fcm.googleapis.com/fcm/send';
    private $key = 'AAAAlrdO5NY:APA91bFvQ5C9Sx2-HcrFJSdCf3gr42tD7wAyQYXJhTr4MzCI-yJq5bR1ToBmvkNbl1NtXP8L3bxOpGKq6igh-LFovrwbzwkKgUQAlv8zGYJ4E4QHlLH5XRbghm3aCYd8lmYRS1-BtXTy';
    //private $key = 'AAAAbWrzYOQ:APA91bHMHtYKosnqcfXWHUvGh7zYFJFTTud8cF86L7OvmMX8NhBl746UMKyrYxUHwKFZaIccSypg_XzZjaMkCVG4ferAFz-1XGzAtgxJiuRsIohkZ77ClFXwLdzn73K1hMMfWPA5HGfI';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    /** */
    public function sendPushTo(String $token, string $tipo, array $data = []): array
    {   
        $opt = $this->getOptions();
        $opt['json']['registration_ids'] = [$token];
        $opt['json']['data']['tipo'] = $this->getSeccionSegunTipo($tipo);
        $opt['json']['android_channel_id'] = $this->getChannelSegunTipo($tipo);
        if(count($data) == 0) {
            $data = [
                'title' => 'TENDRÁS ESTA REFACCIÓN',
                'body' => 'Oportunidad de Venta::AutoparNet',
            ];
        }
        $opt['json']['notification'] = $data;
        
        return $this->send($opt);
    }

    ///
    public function getSeccionSegunTipo($tipo) : string
    {
        $seccion = '';
        switch ($tipo) {
            case 'pcom':
                $seccion = 'pcom';
                break;
            default:
                $seccion = 'cot';
                break;
        }
        return $seccion;
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
    public function sendPushForCotizar(array $tokens, $data): array
    {   
        $opt = $this->getOptions();
        $opt['json']['registration_ids'] = $tokens;
        $opt['json']['data'] = $data;
        $opt['json']['notification'] = [
            'title' => 'TENDRÁS ESTA REFACCIÓN',
            'body' => 'Oportunidad de Venta::AutoparNet',
        ];
        // idMain
        // idPza
        // idInf
        // tipo
        // descr
        return $this->send($opt);
    }

    /** */
    public function pushNewSocio(array $tokens, array $data)
    {
        $opt = $this->getOptions();
        $opt['json']['data'] = [
            'seccion'=> 'newSoc',
            'nombre' => $data['nombre'],
            'id' => $data['id'],
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        $opt['json']['notification'] = [
            'title' => 'NUEVO SOCIO REGISTRADO',
            'body' => 'clv: :suc-[' .implode(', ', $data['sucs']). ']',
        ];

        $opt['json']['registration_ids'] = $tokens;
        $this->send($opt);
    }

    /** */
    public function pushNewRepo(array $tokens, array $data)
    {
        $opt = $this->getOptions();
        $opt['json']['registration_ids'] = $tokens;
        $tokens = null;
        $opt['json']['data'] = [
            'seccion'=> 'newRepo::'.$data['idRepo'],
            'id' => $data['idRepo'],
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        $tipo = ($data['type'] == 'cot') ? 'COTIZACIÓN' : 'PUBLICACIÓN';
        $tipo = ($data['type'] == 'xcot') ? 'RESPUESTA' : $tipo;
        
        $opt['json']['notification'] = [
            'title' => 'NUEVA ' .$tipo. ' REGISTRADA',
            'body' => 'clv:'.$data['type'].'-'.$data['idRepo']
        ];
        
        $this->send($opt);
        $opt = null;
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
}