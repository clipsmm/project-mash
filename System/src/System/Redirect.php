<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 7/6/2015
 * Time: 1:50 AM
 */

namespace System;


class Redirect {

    public $url;
    public $next;
    public $previous;
    public $intended;
    public $data = array();
    public $errors = array();

    public $home;

    public function __construct(){
       return $this;
    }

    public function next(){

    }

    public function back(){

    }

    public function with($key,$value){
        $this->data[$key] = $value;

        return $this;
    }

    public function withError($error){
        $this->errors[] = $error;

        return $this;
    }

    public function redirect($path = 'index.php')
    {
        $this->url =  $path;
        session_start();
        $_SESSION['MESSAGES'] = array();
        $_SESSION['ERRORS'] = array();
        foreach ($this->data as $key => $value){
            $_SESSION['MESSAGES'][$key] = $value;
        }
        $_SESSION['ERRORS'] = $this->errors;


        header("location: ".$this->url);

    }

    public function to($path)
    {
        $this->url = $path;
        $this->redirect($this->url);
    }
}