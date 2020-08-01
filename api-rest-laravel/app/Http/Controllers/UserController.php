<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Exception;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Metodo para probar el controlador UserController";
    }

    public function register(Request $request){
        //Recoger los datos del usuario por POST

        $json = $request->input('json', null);
        
        $params = json_decode($json); //Objeto.
        $params_array = json_decode($json,true); //Array.
        
        try{
            //Limpiar los datos
            $params_array = array_map('trim',$params_array);
            
            //Validar los datos

            $validate = \Validator::make($params_array, [
                'name'      =>  'required',
                'surname'   =>  'required',
                'email'     =>  'required|email|unique:tblUsers',
                'password'  =>  'required'
            ]);

            //Compruebo fallos

            if($validate->fails()){

                $data = array (
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => "El usuario no se ha creado.",
                    'errors'    =>  $validate->errors()
                );
            }else{
                //Validacion pasada correctamente

                //Cifrar contrasenia
                // $pwd = password_hash($params->password,PASSWORD_BCRYPT,['cost' => 4]);  
                //Cambio el cifrado porque genera variables dinamicas y necesito que siempre sean las mismas.

                $pwd = hash('sha256', $params->password);
                //$pwd = password_hash($params->password, CRYPT_SHA256);

                //Comprobar si el usuario ya existe
                //Para eso le agrego el campo unique a la validacion de usuario.

                //Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //Guardar el usuario
                $user->save();


                $data = array (
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => "El usuario se ha creado correctamente." 
                );
            }
        }
        catch (Exception $err){
            $data = array (
                'status'            => 'error',
                'code'              => 400,
                'message'           =>  'Los datos enviados no son correctos.',
                'technicalMessage'  => $err->getMessage()
            );
        }
    
        return response()->json($data,$data['code']);

    }

    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        //Recibo datos por POST
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //Valido los datos
        $validate = \Validator::make($params_array,[
            'email'     =>  'required|email',
            'password'  =>  'required'
        ]); 

        if($validate->fails()){

            $signup = array (
                'status'    => 'error',
                'code'      => 400,
                'message'   => "El usuario no se ha podido identificar.",
                'errors'    =>  $validate->errors()
            );
        }else{
            //Cifro la contrasenia
            $pwd = hash('sha256', $params->password);
           //$pwd = password_hash($params->password, CRYPT_SHA256); //Password_hass() es mas seguro que hash(SHA256) dado que permite generar un SALT dinamico, con la opccion PASSWORD_DEFAULT, lo genera automaticamente.
           //Pero no puedo usarlo dado que cambia el salt y la contrasenia nunca coincide.
            //Devuelvo datos o token

            $signup = $jwtAuth->signup($params->email, $pwd); //Me devuelve el token.
            if(!empty($params->getToken)){
                $signup = $jwtAuth->signup($params->email, $pwd,true); //Me devuelve los datos decodificados.
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request){
        //Compruebo si el usuario esta identificado

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth;
        $checkToken = $jwtAuth->checkToken($token);

        //Recojo los datos por POST
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if($checkToken && !empty($params_array)){
            
            //Obtener el id del usuario para concatenarlo en el validador del mail.
            $idUser = $jwtAuth->checkToken($token,true);
            //Validar los datos
            $validate = \Validator::make($params_array,[
                'name'      =>  'required',
                'surname'   =>  'required',
                'email'     =>  'required|email|unique:tblUsers,'.$idUser->sub//Concateno el id del usuario
            ]);

            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar usuario en la base de datos.
            $user_update = User::where('id', $idUser->sub)->update($params_array);
            
            //Devolver array con el resultado.
            $data = array(
                'status'    => 'success',
                'code'      => 200,
                'user'      => $user_update,
                'changes'   => $params_array
            );

        }else{
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no esta identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){

        //Recoger los datos de la peticion.
        $image = $request->file('file0');

        $validate = \Validator::make($request->all(),[
            'file0' =>  'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardo la imagen.
        if(!$image || $validate->fails()){
            $data = array(
                'code'      =>  400,
                'status'    =>  'error',
                'message'   =>  'Error al subir imagen'
            );  
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name,\File::get($image));

            $data = array (
                'code'      =>  200,
                'status'    =>  'success',
                'image'     =>  $image_name
            );
        }

        //Devolver resultado.
        

        return response()->json($data,$data['code']);
    }

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file,200);
        }else{
            $data = array(
                'code'      =>  400,
                'status'    =>  'error',
                'message'   =>  'Error al cargar imagen'
            ); 
            return response()->json($data,$data['code']);
        }
    }

    public function userDetail($id){
        try{
            $user = User::find($id);
            if(is_object($user)){
                $data = array(
                    'code'      =>  200,
                    'status'    =>  'success',
                    'user'      =>  $user
                );
            }else{
                $data = array(
                    'code'      =>  400,
                    'status'    =>  'error',
                    'message'   =>  'El usuario no existe.'
                ); 
            }

        }catch(Exception $err){
            $error = array(
                'code'          =>  400,
                'codeError'     =>  $err->getCode(),
                'status'        =>  'error',
                'techMessage'   =>  $err->getMessage()
            ); 
            return response()->json($error,$error['code']);
        }

       

        return response()->json($data,$data['code']);
    }
}
