<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UsoController extends Controller
{
    public function get(Request $request) {
        $arr = array();
        $uso = DB::select("SELECT *, `co`.`id` AS `id_opcao` 
            FROM `condicoes_uso` AS `c` INNER JOIN `condicoes_uso_opcoes` AS `co` 
            ON `c`.`id` = `co`.`id_condicao_uso`
            WHERE `c`.`active` = 1 AND `co`.`active` = 1");

        for ($i = 0; $i < count($uso); $i++) {
            $items = DB::select("SELECT `m`.`id_titulo`,`m`.`item`,`c`.`id` AS `id_opcao_item` 
                FROM `condicoes_uso_opcoes_item` AS `c` 
                INNER JOIN `manual` AS `m`  
                ON `c`.`id_item` = `m`.`id`
                WHERE `id_opcao` = ? AND `c`.`active` = 1", [$uso[$i]->id_opcao]);

            $uso[$i]->items = $items;
        }

        return $uso;
    }

    public function save(Request $request) {
        try {
            DB::insert('INSERT INTO `condicoes_uso` (`pergunta`, `nickname`) VALUES (?, ?)', [$request->pergunta, $request->nickname]);
            $id = DB::getPdo()->lastInsertId();
            
            for ($i = 0; $i < count($request->opcoes); $i++) {
                DB::insert('INSERT INTO `condicoes_uso_opcoes` (`opcao`, `id_condicao_uso`, `condicao`) VALUES (?, ?, ?)', 
                    [$request->opcoes[$i]['opcao'], $id, $request->opcoes[$i]['condicao']]);
                $id_opcao = DB::getPdo()->lastInsertId();

                for ($m = 0; $m < count($request->opcoes[$i]['items']); $m++) {
                    DB::insert('INSERT INTO `condicoes_uso_opcoes_item` (`id_opcao`, `id_item`) VALUES (?, ?)', 
                    [$id_opcao, $request->opcoes[$i]['items'][$m]['id']]);
                }
            }
            
            $list = $this->get($request);
            $message = 'Condições de uso inserido com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
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

    public function remove(Request $request, $id) {
        try {
            // DB::update('UPDATE condicoes_uso SET `active` = ? WHERE id = ?', [0, $id]);
            DB::update('UPDATE condicoes_uso_opcoes SET `active` = ? WHERE id = ?', [0, $id]);
            DB::update('UPDATE condicoes_uso_opcoes_item SET `active` = ? WHERE id_opcao = ?', [0, $id]);

            $list = $this->get($request);
            $message = 'Opção da condição de uso deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function edit(Request $request) {
        try {
            DB::update('UPDATE condicoes_uso SET `pergunta` = ?, `nickname` = ? WHERE id = ?', 
                [$request->pergunta, $request->nickname, $request->id_condicao_uso]);

            DB::update('UPDATE condicoes_uso_opcoes SET `opcao` = ?, `condicao` = ? WHERE id = ?', 
                [$request->opcao, $request->condicao, $request->id]);

            for ($i = 0; $i < count($request->itensAdicionados); $i++) {
                DB::insert('INSERT INTO `condicoes_uso_opcoes_item` (`id_opcao`, `id_item`) VALUES (?, ?)', 
                    [$request->id, $request->itensAdicionados[$i]]);
            }

            for ($i = 0; $i < count($request->itensRemovidos); $i++) {
                DB::update('UPDATE condicoes_uso_opcoes_item SET `active` = ? WHERE id = ?', [0, $request->itensRemovidos[$i]]);
            }

            $list = $this->get($request);
            $message = 'Condição de uso alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }
}
