<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class pruebaController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['Perro', 'Gato', 'Leon'];

        return view('prueba.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }

    public function testORM(){
        
        $posts = Post::all();

        //var_dump($post); 
        foreach($posts as $post){
            echo "<h1>" .$post->title. "</h1>";
            echo "<span style='color:grey'>{$post->user->name} - {$post->category->name}</span>";
            echo "<p>" .$post->content. "</p>";
        }
        die(); //corta la ejecucion del programa.
    }
}
