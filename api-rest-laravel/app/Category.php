<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'tblCategories';

    //Relacion de uno a muchos.
    public function posts(){
        return $this->hasMany('App\Post');
    }
}
