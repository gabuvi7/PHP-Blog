<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use illuminate\Http\Response;


class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth',['except' => ['index','show']]);
    }

    public function index(){
        $categories = Category::all();

        return response()->json([
            'code'          =>  200,
            'status'        =>  'success',
            'categories'    =>  $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = array(
                'code'      =>   200,
                'status'    =>  'success',
                'category'  =>  $category
            );
        }else{
            $data = [
                'code'      =>   400,
                'status'    =>  'error',
                'message'  =>   'La categoria no existe.'
            ];
        }

        return response()->json($data,$data['code']);
    }


    //Metodo para guardar una categoria
    public function store(Request $request){
        
        //Recojo los datos por POST:
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        //Valido que los datos no esten vacios:
        if(!empty($params_array)){
            //Valido los datos:
            $validate = \Validator::make($params_array,[
                'name' => 'required'
            ]);

            //Guardo la categoria:
            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria'
                ];
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        }
        else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria'
            ];
        }
                

        //Devuelvo el resultado en un json:

        return response()->json($data,$data['code']);
    }

    //Metodo para actualizar una categoria
    public function update($id, Request $request){
        //Recojo los datos por POST.
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        //Valido que el array no se encuentre vacio.
        if(!empty($params_array)){
            //Valido los datos.
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            if($validate->fails())
            {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se pudo actualizar.'
                ];
            }else{
                 //Quito los datos que no quiero actualizar.
                 unset($params_array['id']);
                 unset($params_array['created_at']);
             //Actualizo los registros.
             $category = Category::where('id',$id)->update($params_array);
 
             $data = [
                 'code' => 200,
                 'status' => 'success',
                 'category' => $params_array
             ];
            }

        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria'
            ];
        }

        //Devuelvo la respuesta.
        return response()->json($data,$data['code']);
    }
}
