<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 6/8/2015
 * Time: 10:29 AM
 */

namespace System;
use App\Request;
use App\Database;
use AfricasTalkingGateway;
use AfricasTalkingGatewayException;
use Curl\Curl;
use System\MyCurl;

class System {

    /*
     * @param string $filename
     * @param string|array $content
     */
    public  static function WriteToFile($filename,$content)
    {
        $myfile = fopen($filename, "a") or die("Unable to open file!");
        //$txt = "John Doe\n";
        fwrite($myfile, $content);
        fclose($myfile);
    }

    public static function ReadFile($filename)
    {
        $myfile = fopen($filename, "r") or die("Unable to open file!");
        $text = fread($myfile,filesize($filename));
        fclose($myfile);

        return $text;
    }



    public static function flatten($array)
    {
        $string = null;
        foreach($array as $key => $value)
        {
            $string .= "$key: $value ";
        }

        return $string;
    }

    public function getDatabaseManager()
    {
        return new Database();
    }

    public function request()
    {
        return new Request();
    }

    public function getCurl()
    {
        return new MyCurl();
    }

}