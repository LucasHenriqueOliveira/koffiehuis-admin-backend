<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ManualController extends Controller
{

    // MANUAL -------------------------------------

    public function save(Request $request) {
        try {

            for($i = 0; $i < count($request->itens); $i++) {
                DB::insert('INSERT INTO `manual_carro` (`id_manual`, `km`, `tempo`, `id_marca`, `id_modelo`) VALUES (?, ?, ?, ?, ?)', 
                [$request->itens[$i]['id'], $request->itens[$i]['km'], $request->itens[$i]['meses'], $request->selectedMarca, $request->selectedModelo]);
            }
            return $this->successResponse(null, 'Plano de manutenção inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function get(Request $request) {
        return DB::select("SELECT `manual`.`id`, `manual`.`item`, `manual_carro`.`km`, `manual_carro`.`tempo`, 
            `modelos`.`nome` as `modelo`, `marcas`.`nome` as `marca`, `modelos`.`id` as `id_modelo`, 
            `marcas`.`id` as `id_marca`, `manual_carro`.`id` as `id_manual_carro` 
        FROM `manual` AS `manual` 
        INNER JOIN `manual_carro` AS `manual_carro` ON `manual`.`id` = `manual_carro`.`id_manual` 
        INNER JOIN `modelos` AS `modelos` ON `manual_carro`.`id_modelo` = `modelos`.`id` 
        INNER JOIN `marcas` AS `marcas` ON `manual_carro`.`id_marca` = `marcas`.`id`
        WHERE `manual`.`active` = 1 AND `manual_carro`.`active` = 1");
    }

    public function remove(Request $request, $id) {
        try {
            DB::delete('DELETE FROM `manual_carro` WHERE `id` = ?', [$id]);
            $list = $this->get($request);
            $message = 'Item do manual deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function edit(Request $request) {
        try {

            $item = DB::select("SELECT * FROM `manual` WHERE `item` = ?", [$request->item]);

            if (count($item)) {
                $id = $item[0]->id;
            } else {
                DB::insert('INSERT INTO `manual` (`item`) VALUES (?)', [$request->item]);
                $id = DB::getPdo()->lastInsertId();
            }

            DB::update('UPDATE `manual_carro` SET `id_manual` = ?, `km` = ?, `tempo` = ?, `id_marca` = ?, `id_modelo` = ? 
                WHERE id = ?', [$id, $request->km, $request->tempo, $request->id_marca, 
                $request->id_modelo, $request->id]);
            $list = $this->get($request);
            $message = 'Item do manual alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    // ITEM DO MANUAL -------------------------------------

    public function getItemManual(Request $request) {
        return DB::select("SELECT `id`, `item` FROM `manual` WHERE `active` = 1");
    }

    public function saveItemManual(Request $request) {
        try {
            DB::insert('INSERT INTO `manual` (`item`) VALUES (?)', [$request->item]);
            $list = $this->getItemManual($request);

            return $this->successResponse($list, 'Item inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeItemManual(Request $request, $id) {
        try {
            DB::update('UPDATE `manual` SET `active` = 0 WHERE id = ?', [$request->id]);
            $list = $this->getItemManual($request);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editItemManual(Request $request) {
        try {
            DB::update('UPDATE `manual` SET `item` = ? WHERE id = ?', [$request->item, $request->id]);
            $list = $this->getItemManual($request);
            $message = 'Item alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    // MANUAL CARRO -----------------------------

    public function getManualCarro(Request $request, $modelo) {
        return DB::select("SELECT `manual_carro`.`id`, `manual`.`item`, `manual_carro`.`id_modelo`, `manual_carro`.`id_manual`,
            `manual_carro`.`km`, `manual_carro`.`tempo`
            FROM `manual_carro`
            INNER JOIN `manual` ON `manual_carro`.`id_manual` = `manual`.`id`
         WHERE `manual_carro`.`id_modelo` = ? AND `manual_carro`.`active` = 1 AND `manual`.`active` = 1", [$modelo]);
    }

    public function removeManualCarro(Request $request, $id) {
        try {
            $veiculo = DB::select("SELECT `id_modelo` FROM `manual_carro` WHERE `id` = ?", [$request->id]);
            DB::update('UPDATE `manual_carro` SET `active` = 0 WHERE id = ?', [$request->id]);
            $list = $this->getManualCarro($request, $veiculo[0]->id_modelo);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editManualCarro(Request $request) {
        try {
            $veiculo = DB::select("SELECT `id_modelo` FROM `manual_carro` WHERE `id` = ?", [$request->id]);
            DB::update('UPDATE `manual_carro` SET `id_manual` = ?, `km` = ?, `tempo` = ? 
                WHERE id = ?', [$request->id_manual, $request->km, $request->tempo, $request->id]);
            $list = $this->getManualCarro($request, $veiculo[0]->id_modelo);
            $message = 'Item alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    // ITEM -------------------------------------

    public function saveItem(Request $request) {

        try {
            DB::insert('INSERT INTO `item` (`id_manual`, `nome`) VALUES (?, ?)', 
            [$request->selectedManual, $request->item]);
            return $this->successResponse(null, 'Item inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function getItem(Request $request) {
        return DB::select("SELECT `manual`.`item`, `item`.*
        FROM `manual` AS `manual` 
        INNER JOIN `item` AS `item` ON `manual`.`id` = `item`.`id_manual` 
        WHERE `manual`.`active` = 1");
    }

    public function removeItem(Request $request, $id) {
        try {
            DB::delete('DELETE FROM `item` WHERE `id` = ?', [$id]);
            $list = $this->getItem($request);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editItem(Request $request) {
        try {
            DB::update('UPDATE `item` SET `nome` = ?, `id_manual` = ? WHERE id = ?', [$request->nome, $request->id_manual, $request->id]);
            $list = $this->getItem($request);
            $message = 'Item alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function getOptions(Request $request) {
        return DB::select("SELECT `id`, `item` FROM `manual` WHERE `active` = 1");
    }

    public function failedResponse(){
        return response()->json([
            'error' => 'Ocorreu um erro na operação.'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse($data, $message) {
        return response()->json([
            'data' => $data,
            'message' => $message
        ], Response::HTTP_OK);
    }
}
