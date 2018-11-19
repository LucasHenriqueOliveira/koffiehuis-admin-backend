<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function get(Request $request) {
        $arrList = array();
        $usuarios = DB::select("SELECT count(*) AS `usuarios` FROM `users`")[0];
        $arrList['usuarios'] = $usuarios->usuarios;

        $carros = DB::select("SELECT count(*) AS `carros` FROM `carros` WHERE `active` = 1")[0];
        $arrList['carros'] = $carros->carros;

        $condicoes_uso = DB::select("SELECT count(*) AS `condicoes_uso` FROM `condicoes_uso` WHERE `active` = 1")[0];
        $arrList['condicoes_uso'] = $condicoes_uso->condicoes_uso;

        $manual = DB::select("SELECT count(*) AS `manual` FROM `manual` WHERE `active` = 1")[0];
        $arrList['manual'] = $manual->manual;

        return $arrList;
    }
}
