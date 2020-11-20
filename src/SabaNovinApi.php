<?php

namespace SabaNovin;

use SabaNovin\Exceptions\ApiException;
use SabaNovin\Exceptions\HttpException;
use SabaNovin\Exceptions\RuntimeException;

class SabaNovinApi
{
    protected $apiKey;
    const API_URL = "https://api.sabanovin.com/v1/%s/%s/%s.json/";
    const VERSION = "1.0";
    public function __construct($apiKey)
    {
        if (!extension_loaded('curl')) {
            die('cURL library is not loaded');
            exit;
        }
        if (is_null($apiKey)) {
            die('apiKey is empty');
            exit;
        }
        $this->apiKey = $apiKey;
    }

    protected function get_path($method, $base = 'sms')
    {
        return sprintf(self::API_URL, $this->apiKey, $base, $method);
    }

    protected function execute($url, $data = null)
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'charset: utf-8'
        );
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);

        $response     = curl_exec($handle);
        $code         = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $curl_errno   = curl_errno($handle);
        $curl_error   = curl_error($handle);
        if ($curl_errno) {
            throw new HttpException($curl_error, $curl_errno);
        }
        $json_response = json_decode($response);
        if ($code != 200 && is_null($json_response)) {
            throw new HttpException("Request have errors", $code);
        } else {
            $json_return = $json_response->status;
            if ($json_return->code != 200) {
                throw new ApiException($json_return->message, $json_return->code);
            }
            return $json_response;
        }
    }

    public function Send($gateway, $to, $text, $at = null)
    {
        if (is_array($to)) {
            $to = implode(",", $to);
        }
        $path   = $this->get_path("send");
        $params = array(
            "to" => $to,
            "gateway" => $gateway,
            "text" => $text,
        );
        return $this->execute($path, $params);
    }

    public function SendArray($gateway, $to, $text, $at = null)
    {
        if (!is_array($to)) {
            $to = (array) $to;
        }
        if (!is_array($text)) {
            $text = (array) $text;
        }
        $repeat = count($to);
        $path   = $this->get_path("send_array");
        $params = array(
            "gateway" => $gateway,
            "to" => json_encode($to),
            "text" => json_encode($text),
        );
        return $this->execute($path, $params);
    }

    public function Status($reference_id)
    {
        $path = $this->get_path("status");
        $params = array(
            "reference_id" => is_array($reference_id) ? implode(",", $reference_id) : $reference_id
        );
        return $this->execute($path, $params);
    }

    public function StatusByBatchId($batch_id)
    {
        $path = $this->get_path("status");
        $params = array(
            "batch_id" => $batch_id
        );
        return $this->execute($path, $params);
    }

    public function Receive($gateway, $is_read = 0)
    {
        $path   = $this->get_path("receive");
        $params = array(
            "gateway" => $gateway,
            "is_read" => $is_read
        );
        return $this->execute($path, $params);
    }
}
