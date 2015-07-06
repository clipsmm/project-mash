<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 6/8/2015
 * Time: 9:25 AM
 */

namespace System;


class Request {

    protected $method;
    public $request;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->request = $_REQUEST;
    }

    public static function all()
    {
        return $_REQUEST;
    }

    public static function get($key)
    {
        try{
            return $_REQUEST[$key];
        } catch(\Exception $e){
            return false;
        }
    }

    public function getRequestMethod()
    {
        return $this->method;
    }
}