<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class CrawlerController extends Controller
{
    public function process(Request $request) {
        switch ($request->type) {
            case 'marcas':
                try {
                    for ($i = 0; $i < count($request->data); $i++) {
                        DB::insert('INSERT INTO `marcas` (`id`, `nome`, `key`) VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE `nome` = ?', [$request->data[$i]['id'], 
                        $request->data[$i]['fipe_name'], $request->data[$i]['key'],
                        $request->data[$i]['fipe_name']]);
                    }
                    return $this->successResponse();
                } catch (Exception $e) {
                    return $this->failedResponse();
                }
                break;
            case 'modelos':
                try {
                    $marcas = DB::select("SELECT * FROM `marcas`");
                    
                    foreach ($marcas as $marca) {
                        $url = 'http://fipeapi.appspot.com/api/1/carros/veiculos/' . $marca->id . '.json';

                        $client = new Client(); //GuzzleHttp\Client
                        $result = $client->get($url);
                        $arr = json_decode($result->getBody(), true);
                        
                        for ($i = 0; $i < count($arr); $i++) {
                            DB::insert('INSERT INTO `modelos` (`id`, `nome`, `key`, `id_marca`) VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE `nome` = ?', [$arr[$i]['id'], 
                            $arr[$i]['fipe_name'], $arr[$i]['key'], $marca->id,
                            $arr[$i]['fipe_name']]);
                        }
                    }

                    return $this->successResponse();
                } catch (Exception $e) {
                    return $this->failedResponse();
                }
                break;
        }
    }

    public function failedResponse(){
        return response()->json([
            'error' => 'Ocorreu um erro na operação.'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse() {
        return response()->json([
            'data' => 'Operação realizada com sucesso.'
        ], Response::HTTP_OK);
    }

    public function modelo(Request $request) {
        try {
            $marcas = DB::select("SELECT * FROM `marca`");
            
            foreach ($marcas as $marca) {
                $url = 'https://fipe.webmotors.com.br/fipe/vehicle/list/models/?id=' . $marca->id . '&vehicleType=car';

                $client = new Client(); //GuzzleHttp\Client
                $result = $client->get($url);
                $arr = json_decode($result->getBody(), true);
                $arr = $arr['Result'];

                for ($i = 0; $i < count($arr); $i++) {
                    DB::insert('INSERT INTO `modelo` (`id`, `modelo`, `id_marca`) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE `modelo` = ?', [$arr[$i]['Id'], $arr[$i]['Name'], $marca->id, $arr[$i]['Name']]);
                }
            }

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function ano(Request $request) {
        try {
            $modelos = DB::select("SELECT * FROM `modelo`");
            
            foreach ($modelos as $modelo) {
                $url = 'https://fipe.webmotors.com.br/fipe/vehicle/list/years/?id=' . $modelo->id . '&vehicleType=car';

                $client = new Client(); //GuzzleHttp\Client
                $result = $client->get($url);
                $arr = json_decode($result->getBody(), true);
                $arr = $arr['Result'];

                for ($i = 0; $i < count($arr); $i++) {
                    DB::insert('INSERT INTO `modelo_ano` (`id_modelo`, `ano`) VALUES (?, ?)', [$modelo->id, $arr[$i]['Id']]);
                }
            }

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function versao(Request $request) {
        try {
            $modelosAno = DB::select("SELECT * FROM `modelo_ano`");
            
            foreach ($modelosAno as $modelo_ano) {
                $url = 'https://fipe.webmotors.com.br/fipe/vehicle/list/versions/?id=' . $modelo_ano->id_modelo . '&year='.$modelo_ano->ano;

                $client = new Client(); //GuzzleHttp\Client
                $result = $client->get($url);
                $arr = json_decode($result->getBody(), true);
                $arr = $arr['Result'];

                for ($i = 0; $i < count($arr); $i++) {
                    DB::insert('REPLACE INTO `versao` (`id_versao`, `versao`) VALUES (?, ?)', 
                    [$arr[$i]['Id'], $arr[$i]['Name']]);
                }
            }

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function versaoAno(Request $request) {
        try {
            $modelosAno = DB::select("SELECT * FROM `modelo_ano` where id > 62765");
            
            foreach ($modelosAno as $modelo_ano) {
                $url = 'https://fipe.webmotors.com.br/fipe/vehicle/list/versions/?id=' . $modelo_ano->id_modelo . '&year='.$modelo_ano->ano;

                $client = new Client(); //GuzzleHttp\Client
                $result = $client->get($url);
                $arr = json_decode($result->getBody(), true);
                $arr = $arr['Result'];

                for ($i = 0; $i < count($arr); $i++) {
                    DB::insert('REPLACE INTO `versao_ano` (`id_versao`, `id_modelo_ano`) VALUES (?, ?)', 
                    [$arr[$i]['Id'], $modelo_ano->id]);
                }
            }

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

}