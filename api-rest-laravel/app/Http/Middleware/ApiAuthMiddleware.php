<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $token = $request->header('Authorization');
            $jwt = new \JwtAuth;
            $checktoken = $jwt->checktoken($token);

        }catch(Exception $err){
            $data = array (
                'status'            => 'error',
                'code'              => 400,
                'message'           =>  'Error al obtener los datos del token.',
                'techMessage'       =>  $err->getMessage()
            );

            return response()->json($data,$data['code']);
        }
        
            if($checktoken){
                return $next($request);
            }else{
                $data = array (
                    'status'            => 'error',
                    'code'              => 400,
                    'message'           =>  'Los datos enviados no son correctos.'
                );
    
                return response()->json($data,$data['code']);
            }
    }
}
