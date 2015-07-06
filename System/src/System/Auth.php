<?php
/**
 * Created by PhpStorm.
 * User: Oscar
 * Date: 6/24/2015
 * Time: 11:54 AM
 */

namespace System;

use System\System;
use System\Mail;
class Auth {

    protected $user;
    protected $session;

    public static function hash($password)
    {
        $auth = new Auth();
        //$salt = $auth->createSalt();
        $hash = hash('sha256',$password);

        return hash('sha256',API_KEY.$hash);
    }

    public static function register($credentials,$next = null){
        $db = new Database();
        $auth = new Auth();

        try {
            $user = $db->table(USER_TABLE)->insert($credentials);
            $auth->createSession($user);
            redirect($next);

        }catch (\Exception $e)
        {
            dump($e->getMessage());
        }

    }

    public function createSession($user)
    {
        @session_start();
        session_regenerate_id();
        $_SESSION['user'] = $user;
    }


    function createSalt()
    {
        return API_KEY;
    }

    function verify($password, $hashedPassword) {
        return crypt($password, $hashedPassword) == $hashedPassword;
    }

    public static function logout($path = null)
    {
        // delete the session of the user
        $_SESSION = array();
        session_destroy();
        redirect($path);

    }


    public static function login(array $credentials,$next=null)
    {
        $auth = new Auth();
        $db = new Database();
        if (!array_key_exists(LOGIN_FIELD,$credentials) || !array_key_exists('password',$credentials))
            dump(['error'=>"Please supply ".LOGIN_FIELD." and password"]);
        try {

            $person = $db->table(USER_TABLE)->find([LOGIN_FIELD=>$credentials[LOGIN_FIELD]]);
            if (!$person){
                return "Invalid login credentials!";
            }else{
                //check if the user password is the same as the supplied password
                if ($person->password === $auth->hash($credentials['password'])){

                    $auth->createSession($person);
                    redirect($next);
                }

                return 'Invalid password';
            }
        } catch (\Exception $e){
            dump($e->getMessage());
        }



    }


    public static function check()
    {

        if (isset($_SESSION['user']) && is_object($_SESSION['user'])){
            return true;
        }

        return false;
    }

    public function forceLogin()
    {

    }

    public static function user()
    {

        if (isset($_SESSION['user']) && is_object($_SESSION['user'])){
            return $_SESSION['user'];
        }

        return false;
    }

    public static function ResetRequest($email)
    {
        $time = new \DateTime();
        $auth =  new Auth();
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
            dump('Invalid email supplied');
        }

        try {
            $db = new Database();

            $db->table(PASSWORD_RESET_TABLE)->insert([
                'email'=>$email,
                'token'=>$token = $auth->hash($time->format('Y-m-d H:i:s')),
                'created_at'=>$time->format('Y-m-d H:i:s')
            ]);
            $url = url('reset.php?token='.$token,1);
            $emails = [$email];
            $subject = 'Change Password Request';
            $body = "
            Hi,<br><br>

            You requested for password change. Click the link below to change your password <br>
            <a href='$url'>$url</a><br>

            Regards,<br>
            Cleaning Services
            ";
            Mail::Send($emails,$subject,$body);

            return true;


        } catch (\Exception $e){
            dump($e->getMessage());
        }
    }


    /**
     * Reset the user password
     * @param array $credentials
     * @return array|object
     */
    public static function ResetPassword(array $credentials)
    {
        $auth = new Auth();
        $db = new Database();
        $keys = ['password','token'];
        foreach ($keys as $key => $value){
            if (!array_key_exists($value,$credentials)){
                dump("$value is missing");
            }
        }

        //check request exists
        if (!$reset = $auth->getReset($credentials['token'])){
            dump('Invalid request');
        }
        //dump($reset->email);


        //hash the password
        $password = $auth->hash($credentials['password']);

        try {
            //update new user password

            $user = $db->table(USER_TABLE)->update('email',$reset->email,['password'=>$password]);
            //dump($user);

            $db->table(PASSWORD_RESET_TABLE)->delete('token',$credentials['token']);

            return true;
        } catch (\Exception $e){
            dump($e->getMessage());
        }



    }

    /**
     * @param string $token
     * @return array|object
     */
    public function getReset($token)
    {
        $db = new Database();
        $reset = $db->table(PASSWORD_RESET_TABLE)->find(['token'=>$token]);

        if ($reset)
            return $reset;

        return false;
    }

    public static function ValidateResetToken($token)
    {
        $auth = new Auth();
        if ($auth->getReset($token))
            return true;

        return false;

    }


}