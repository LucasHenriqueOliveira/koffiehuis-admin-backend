<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UsoController extends Controller
{
    public function get(Request $request) {
        $arr = array();
        $arrItens = array();
        $uso = DB::select("SELECT * FROM `condicoes_uso` WHERE `active` = 1");

        for ($i = 0; $i < count($uso); $i++) {
            $opcoes = DB::select("SELECT * FROM `condicoes_uso_opcoes` WHERE `id_condicao_uso` = ? AND `active` = 1", [$uso[$i]->id]);
            $arr['id'] = $uso[$i]->id;
            $arr['pergunta'] = $uso[$i]->pergunta;
            $arr['opcoes'] = $opcoes;
            array_push($arrItens, $arr);
        }

        return $arrItens;
    }

    public function save(Request $request) {
        try {
            DB::insert('INSERT INTO `condicoes_uso` (`pergunta`) VALUES (?)', [$request->pergunta]);
            $id = DB::getPdo()->lastInsertId();
            
            for ($i = 0; $i < count($request->opcoes); $i++) {
                DB::insert('INSERT INTO `condicoes_uso_opcoes` (`opcao`, `id_condicao_uso`) VALUES (?, ?)', 
                    [$request->opcoes[$i], $id]);
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
            DB::update('UPDATE condicoes_uso SET `active` = ? WHERE id = ?', [0, $id]);
            DB::update('UPDATE condicoes_uso_opcoes SET `active` = ? WHERE id_condicao_uso = ?', [0, $id]);

            $list = $this->get($request);
            $message = 'Condição de uso deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function edit(Request $request) {
        try {
            DB::update('UPDATE condicoes_uso SET `pergunta` = ? WHERE id = ?', [$request->pergunta, $request->id]);
            $list = DB::select("SELECT * FROM `condicoes_uso` WHERE `active` = 1");
            $message = 'Condição de uso alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }
}
