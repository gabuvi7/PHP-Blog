<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use Exception;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',
            ['except' => [
                'index',
                'show',
                'getImage',
                'getPostsByCategory',
                'getPostsByUser'
            ]]);
    }

    private function getIdentity(Request $request){
        //Importo JWT de App\Helper\JWT;
        //Recojo los datos del usuario identificado:
            $jwtAuth = new JwtAuth;
            $token = $request->header('Authorization',null);
            $user = $jwtAuth->checkToken($token,true);
        return $user;
    }

    public function index(){
        $posts = Post::all()->load('category'); // Con el metodo load busco que no solo me de el id de la categoria, sino que tambien me de el objeto y asi tener su descripcion.

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => $posts
        ], 200);
        
    }

    public function show($id){
        try{
            $post = Post::find($id);
            
            if(Empty($post)){
                throw Exception;
            }
            $post->load('category')
                ->load('user');
            
        }catch (Exception $errr){
            $error = [
                'code' => 404,
                'CodeError' => $errr->getCode(),
                'status' => 'Catch Error',
                'TechMessage' => $errr->getMessage(),
                'Message' => 'La entrada no existe.'
            ];

            return response()->json($error, $error['code']);
        }
        
        if(is_Object($post)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => $post
            ];
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La entrada no existe.'
            ];
        }  

    
        return response()->json($data, $data['code']);  
    }

    public function store(Request $request){
        
        //Recojo los datos por POST:
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //Valido que los datos no esten vacios.
        if(!empty($params_array)){
            //Conseguir usuario identificado.
                //Recojo los datos del usuario identificado.
                    $user = $this->getIdentity($request);                   
                
            //Valido los datos.
                $validate = \Validator::make($params_array,[
                    'category_id' => 'required',
                    'title' => 'required',
                    'content' => 'required',
                    'image' => 'required'
                ]);
                
                if($validate->fails()){
                    $data = [
                        'code' => 400,
                        'status' => 'Error',
                        'message' => 'Los datos ingresados son incorrectos. Faltan datos' 
                    ];
                }else{
                    //Guardo el registro.

                        $post = New Post();
                        $post->user_id = $user->sub;
                        $post->category_id = $params->category_id;
                        $post->title = $params->title;
                        $post->content = $params->content;
                        $post->image = $params->image;
                        $post->Save();

                        $data = [
                            'code' => 200,
                            'status' => 'success',
                            'message' => $post
                        ];
                }

        }else{
            $data = [ 
                'code' => 400,
                'status' => 'Error',
                'message' => 'Entrada incorrecta'
            ];
        }
        return response()->json($data,$data['code']);
    }

    public function update ($id, Request $request){
        //Recojo los datos por POST.
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos incorrectos.'
        ];

        //Recojo los datos del usuario identificado.
            $user = $this->getIdentity($request);   


        //Validar los datos.
        if(!empty($params_array)){
            $validate = \Validator::make($params_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors(); //Le agrego el campo error al array $data
                return response()->json($data,$data['code']);
            }else{
                //Eliminar lo que no queremos actualizar.
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);                
                unset($params_array['category']);   
                unset($params_array['user']);   
                try{
                    $post = Post::where('id',$id)
                    ->where('user_id',$user->sub) //Obtengo el post del usuario.
                    ->update($params_array);           

                    if(Empty($post)){
                        throw Exception;
                    }

                    $postCompleto = Post::where('id',$id)
                            ->where('user_id',$user->sub)
                            ->first(); //Obtengo el objeto completo

                    
                    $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El post se actualizó correctamente',
                    'post' => $postCompleto, //Devuelvo el objeto actualizado
                    'changes' => $params_array
                    ];
                }
                catch (Exception $errr){
                    $error = [
                        'code' => 404,
                        'CodeError' => $errr->getCode(),
                        'status' => 'Catch Error',
                        'TechMessage' => $errr->getMessage(),
                        'Message' => 'El registro no existe o no tienes permisos para actualizarlo.'
                    ];
        
                    return response()->json($error, $error['code']);
                }
             
            }

        }

        //Devolver la respuesta.

        return response()->json($data,$data['code']);
    }

    public function destroy($id, Request $request){
        try{
            //Recojo los datos del usuario identificado.
                $user = $this->getIdentity($request);   

            //Conseguir el registro
                //$post = Post::find($id);
                $post = Post::where('id',$id)
                            ->where('user_id',$user->sub)
                            ->first(); //Consigo el post del usuario.

                if(Empty($post)){
                    throw Exception;
                }

                //Borrarlo
                    $post->delete();
        
                //Devolver respuesta
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Post eliminado',
                        'post' => $post
                    ];
        }
        catch (Exception $errr){
            $error = [
                'code' => 404,
                'CodeError' => $errr->getCode(),
                'status' => 'Catch Error',
                'TechMessage' => $errr->getMessage(),
                'Message' => 'El registro no existe o no tienes permisos para borrarlo.'
            ];

            return response()->json($error, $error['code']);
        }

        
        return response()->json($data,$data['code']);
    }

    public function upload (Request $request){
        //Recoger el archivo de la peticion
        $image = $request->file('file0');

        //Validar el archivo
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        
        if(!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir el archivo'
            ];
        }else{
            //Guardar el archivo en disco (images)
                $image_name = time().$image->getClientOriginalName(); //Para sacar el nombre del archivo subido.

                \Storage::disk('images')->put($image_name, \File::get($image)); //Guardo la imagen en el disco configurado como images.

            
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El archivo se subió correctamente',
                    'filename' => $image_name
                ];
        }
        
        //Devolver datos
            return response()->json($data,$data['code']);
    }

    public function getImage($filename){
        //Comprobar si existe el archivo
            $isset = \Storage::disk('images')->exists($filename);
            if($isset){
                //Conseguir la imagen
                    $file = \Storage::disk('images')->get($filename);
                //Devuelvo la imagen
                    return response($file,200);
            }else{
                
                //Devolver error
                    $data = [
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Error. El archivo no existe'
                    ];
                    return response()->json($data,$data['code']);
            }
    }

    public function getPostsByCategory($id){
        $post = Post::where('category_id',$id)->get();

        $data = [
            'code' => 200,
            'status' => 'success',
            'posts' => $post
        ];        

        return response()->json($data,$data['code']);
    }

    public function getPostsByUser($id){
        $post = Post::where('user_id',$id)->get();

        $data = [
            'code' => 200,
            'status' => 'success',
            'posts' => $post
        ];        
        
        return response()->json($data,$data['code']);
    }
}
