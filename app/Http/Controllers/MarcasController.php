<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcasController extends Controller
{
    public function getMarcas(Request $request) {
        return DB::select("SELECT * FROM marca");
    }

    public function getModelos(Request $request, $id) {
        return DB::select("SELECT * FROM modelo WHERE id_marca = " . $id . " ORDER BY modelo ASC");
    }

    public function getAnos(Request $request, $id) {
        return DB::select("SELECT * FROM modelo_ano WHERE id_modelo = " . $id . " ORDER BY ano DESC");
    }

    public function getVersao(Request $request, $id) {
        return DB::select("SELECT * FROM versao_ano AS va INNER JOIN versao AS v ON va.id_versao = v.id_versao
        	WHERE va.id_modelo_ano = " . $id . " ORDER BY v.versao DESC");
    }
}
