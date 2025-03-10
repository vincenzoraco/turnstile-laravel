<?php

namespace DerekCodes\TurnstileLaravel;

class TurnstileLaravel
{
    public $secret_key;
    
    public function __construct($secret_key = null)
    {
        $this->secret_key = $secret_key;
        if (empty($secret_key)) {
            $this->secret_key = config('turnstile.secret_key');
        }
    }
    
    public function validate(String $response): Array
    {
        if (!empty($this->secret_key)) {
            try {
                $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                    ],
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_POSTFIELDS => json_encode([
                        'secret' => $this->secret_key,
                        'response' => $response
                    ]),
                ]);

                $result = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                $json = json_decode($result);
            } catch (Exception $e) {
                return [
                    'status' => 0,
                    'error' => 'Unknown error occured'
                ];
            }

            if (isset($json->success) && $json->success) {
                if ($json->success) {
                    return [
                        'status' => 1,
                    ];
                }

                return [
                    'status' => 0,
                    'turnstile_response' => $json,
                ];
            }

            return [
                'status' => 0,
                'error' => 'Unknown error occured'
            ];
        }

        return [
            'status' => 0,
            'error' => 'Turnstile secret not found'
        ];
    }
}
