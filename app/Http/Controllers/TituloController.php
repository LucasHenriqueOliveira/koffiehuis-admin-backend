<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TituloController extends Controller
{
    public function get(Request $request) {
        return DB::select("SELECT * FROM `titulo` WHERE `active` = 1");
    }

    public function save(Request $request) {
        try {
            DB::insert('INSERT INTO `titulo` (`titulo`) VALUES (?)', [$request->nome]);
            $list = DB::select("SELECT * FROM `titulo` WHERE `active` = 1");
            return $this->successResponse($list, 'Título adicionado com sucesso.');
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
            DB::update('UPDATE `titulo` SET `active` = ? WHERE id = ?', [0, $id]);
            $list = DB::select("SELECT * FROM `titulo` WHERE `active` = 1");
            $message = 'Título deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function edit(Request $request) {
        try {
            DB::update('UPDATE `titulo` SET `titulo` = ? WHERE id = ?', [$request->nome, $request->id]);
            $list = DB::select("SELECT * FROM `titulo` WHERE `active` = 1");
            $message = 'Título alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }
}
