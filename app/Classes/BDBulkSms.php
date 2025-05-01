<?php

namespace App\Classes;

class BDBulkSms
{
    private string $apiEndpoint = 'http://api.greenweb.com.bd/api.php';
    private string $phone;
    private string $message;
    private string $token;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->token = "1056416050017049675009c423f2deadeb862f2ed589092d722de";
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    public function setApiEndpoint(string $apiEndpoint): void
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function send()
    {
        $data = array(
            'to' => $this->phone,
            'message' => $this->message,
            'token' => $this->token
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        if (curl_error($ch)) {
            return false;
        }
        //Result
        return true;
    }
}
