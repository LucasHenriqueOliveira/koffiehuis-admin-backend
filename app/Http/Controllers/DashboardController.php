<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function get(Request $request) {
        $arrList = array();
        $produtos = DB::select("SELECT `id_product`, `name` FROM `products` WHERE `quantity` > 0 ORDER BY name ASC");
        $arrList['produtos'] = $produtos;

        $estoque = DB::select("SELECT `name`, DATE_FORMAT(`date`,'%d/%m/%Y %H:%m') as date FROM `stock` INNER JOIN `products` ON `stock`.`id_product` = `products`.`id_product` WHERE `date` >= DATE_SUB(CONCAT(CURDATE(), ' 00:00:00'), INTERVAL 1 DAY)");
        $arrList['estoque'] = $estoque;

        return $arrList;
    }

    public function remove(Request $request) {

    	DB::insert('INSERT INTO `stock` (`id_product`, `id_user`, `date`) VALUES (?, ?, ?)', 
    		[$request->id, 1, now()]);

        DB::update('UPDATE `products` SET `quantity` = quantity - 1 WHERE id_product = ?', [$request->id]);

        return $this->get($request);
    }
}
