<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CarroController extends Controller
{
    public function getCars(Request $request) {
        return DB::select("SELECT `carros`.`id` AS `id_carro`, DATE_FORMAT(`data_criacao`, '%d/%m/%Y %H:%i') AS `data`, 
        	`carros`.`placa`, `carros`.`renavam`, `carros`.`chassi`, `carros`.`ano_modelo`, `carros`.`ano_fabricacao`,
        	`marcas`.`nome` AS `marca`, `modelos`.`nome` AS `modelo`
        	FROM `carros` INNER JOIN `modelos` AS `modelos` ON `carros`.`id_modelo` = `modelos`.`id` 
        	INNER JOIN `marcas` AS `marcas` ON `carros`.`id_marca` = `marcas`.`id`");
    }
}
