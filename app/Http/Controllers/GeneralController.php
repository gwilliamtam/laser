<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralController extends Controller
{
    //
    public function test()
    {
        return view('test');
    }

    public function hola(){
        $hola = rand(1,100);
        $half_hola = $hola / 2;
        return $half_hola;
    }
}
