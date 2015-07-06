<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 7/6/2015
 * Time: 2:26 AM
 */

namespace System;


class Message {

    public  $messages;

    public $errors;

    public function __construct()
    {
        if (isset($_SESSION['MESSAGES'])){
            $this->messages = $_SESSION['MESSAGES'];
        }elseif(isset($_SESSION['ERRORS'])){
            $this->messages = $this->errors = $_SESSION['ERRORS'];
        }else{
            $this->messages = null;
        }

        return $this;
    }

    public function has($key){
        if (isset($this->messages[$key]))
            return true;

        return false;
    }

    public function hasError(){
        if (count($this->errors) > 0)
            return true;

        return false;
    }

    public function all()
    {
        return $this->messages;
    }

    public function get($key)
    {
        if (isset($this->messages[$key]))
            return $this->messages[$key];

        return null;
    }

    public function set($key,$value)
    {
        $this->messages[$key] = $value;

        $_SESSION['MESSAGES'] = $this->messages;
    }
}