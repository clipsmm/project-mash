<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 6/11/2015
 * Time: 1:07 PM
 */

namespace System;


class MyCurl {

    protected $curl, $timeoout,$url,$fields,$useragent,$connection_timeout,$return,$response;

    public function __construct()
    {
        $this->curl = curl_init();

        return $this;
    }


    public function get($url,$return=1){
        curl_setopt_array($this->curl,[
            CURLOPT_RETURNTRANSFER => $return,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->useragent
        ]);

        $this->exec($this->curl);

        return $this->response;
    }

    public function post($url,$return  = 1)
    {
        curl_setopt_array($this->curl,[
            CURLOPT_RETURNTRANSFER => $return,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT=>$this->useragent,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $this->fields,
        ]);

        $this->exec($this->curl);

        return $this->response;
    }

    public function setParams(array $params)
    {
        $this->fields = $params;

        return $this;
    }

    protected function exec($curl)
    {
        $response = $this->response = curl_exec($this->curl);

        curl_close($this->curl);

        return $response;
    }

    public function json()
    {
        return json_decode($this->response);
    }
}