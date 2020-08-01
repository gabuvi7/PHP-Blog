<?php

namespace App\Helpers;

use App\User;
USE Firebase\JWT\JWT; //Para poder utilizar la libreria JWT.
USE Illuminate\Support\Facades\DB; //Para hacer consultas a la base de datos. 
use Exception;

Class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'Clave_unica_segurizada-99556687821';
    }

    public function signup($email, $password, $getToken = null){
        try{
            //Buscar si existe el usuario con sus credenciales.
            $user = User::where([
                'email' => $email,
                'password' => $password
            ])->first();

            
        }catch(Exception $err){
            $data = array(
                'status'            => 'error',
                'code'              =>  400,
                'message'           =>  'No existe el usuario.',
                'technicalMessage'  =>  $err->getMessage()
            );
        }
        
            //Comprobar si son correctas.
            $signup = false;
            if(is_object($user)){
                $signup = true;
            }
            
        try{
            
            //Generar el token con los datos del usuario identificado. 
        
            $token = array(
                'sub'         =>      $user->id,
                'email'       =>      $user->email,
                'name'        =>      $user->name,
                'surname'     =>      $user->surname,
                'description' =>      $user->description,
                'image'       =>      $user->image,
                'iat'         =>      time(),
                'exp'         =>      time() + (7 * 24 * 60 * 60)
            );
        
            $jwt = JWT::encode($token,$this->key, 'HS256');
            
            
            //Devolver los datos decodificados o el token en funcion de un parametro.

            $decoded = JWT::decode($jwt,$this->key, ['HS256']);
            
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }
            

        }catch(Exception $err){
            $data = array(
                'status'            => 'error',
                'code'              =>  400,
                'message'           =>  'Login incorrecto.',
                'technicalMessage'  =>  $err->getMessage()
            );
        }
        

        return $data;
    }


    public function checkToken($jwt,$getIdentity = false){
        $auth = false;

        try{
            $jwt = str_replace('"','',$jwt); //Para reemplazar posibles comillas y no de error.
            $decoded = JWT::decode($jwt, $this->key,['HS256']);
        }catch(\UnexpectedValueException $err){
            $auth = false;
        }catch(\DomainException $err){
            $auth = false;
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }

        return $auth;

    }


}