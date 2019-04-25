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

            $item = DB::select("SELECT * FROM `manual_carro` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ?",
                [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);

            if (count($item)) {

                return response()->json([
                    'error' => 'Manual já cadastrado para este veículo.'
                ], Response::HTTP_NOT_FOUND);

            } else {
                for($i = 0; $i < count($request->itens); $i++) {
                    DB::insert('INSERT INTO `manual_carro` (`id_manual`, `km_ideal`, `tempo_ideal`, `observacao_ideal`, `km_severo`, `tempo_severo`, `observacao_severo`,
                        `id_marca`, `id_modelo`, `ano`, `id_versao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->itens[$i]['id'], $request->itens[$i]['km_ideal'], $request->itens[$i]['meses_ideal'], $request->itens[$i]['observacao_ideal'],
                    $request->itens[$i]['km_severo'], $request->itens[$i]['meses_severo'], $request->itens[$i]['observacao_severo'], $request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);
                }
                DB::insert('REPLACE INTO `observacao` (`id_marca`, `id_modelo`, `ano`, `id_versao`, `observacao`) VALUES (?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, $request->observacao]);
                
                return $this->successResponse(null, 'Plano de manutenção inserido com sucesso.');
            }
            
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function copy(Request $request) {
        try {

            $item = DB::select("SELECT * FROM `manual_carro` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ?",
                [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);

            if (count($item)) {

                return response()->json([
                    'error' => 'Manual já cadastrado para este veículo.'
                ], Response::HTTP_NOT_FOUND);

            } else {

                $manual = DB::select("SELECT `manual_carro`.`id_manual` AS `id`, `manual`.`item`, `manual_carro`.`id_manual`,
                    `manual_carro`.`km_ideal`, `manual_carro`.`tempo_ideal` AS `meses_ideal`, `manual_carro`.`observacao_ideal`,
                    `manual_carro`.`km_severo`, `manual_carro`.`tempo_severo` AS `meses_severo`, `manual_carro`.`observacao_severo`,
                    `titulo`.`titulo`, `manual`.`id_titulo`
                    FROM `manual_carro`
                    INNER JOIN `manual` ON `manual_carro`.`id_manual` = `manual`.`id`
                    INNER JOIN `titulo` ON `manual`.`id_titulo` = `titulo`.`id`
                 WHERE `manual_carro`.`id_marca` = ? AND `manual_carro`.`id_modelo` = ? AND 
                 `manual_carro`.`ano` = ? AND `manual_carro`.`id_versao` = ? AND `manual_carro`.`active` = 1", [$request->marcaAntigo, 
                    $request->modeloAntigo, $request->anoAntigo, $request->versaoAntigo]);

                for ($i = 0; $i < count($manual); $i++) {
                    DB::insert('INSERT INTO `manual_carro` (`id_manual`, `km_ideal`, `tempo_ideal`, `observacao_ideal`, `km_severo`, `tempo_severo`, `observacao_severo`,
                            `id_marca`, `id_modelo`, `ano`, `id_versao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                        [$manual[$i]->id, $manual[$i]->km_ideal, $manual[$i]->meses_ideal,
                        $manual[$i]->observacao_ideal, $manual[$i]->km_severo,
                        $manual[$i]->meses_severo, $manual[$i]->observacao_severo,
                        $request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);
                }
                DB::insert('REPLACE INTO `observacao` (`id_marca`, `id_modelo`, `ano`, `id_versao`, `observacao`) VALUES (?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, $request->observacao]);
                
                return $this->successResponse(null, 'Plano de manutenção inserido com sucesso.');
            }
            
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function get(Request $request) {
        return DB::select("SELECT `manual`.`id`, `manual_carro`.`id_manual`, `manual`.`item`, `manual_carro`.`km_ideal`, `manual_carro`.`tempo_ideal` AS `meses_ideal`, `manual_carro`.`observacao_ideal`,
            `manual_carro`.`km_severo`, `manual_carro`.`tempo_severo` AS `meses_severo`, `manual_carro`.`observacao_severo`,
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
        return DB::select("SELECT `m`.`id`, `m`.`item`, `m`.`id_titulo`, `t`.`titulo` FROM `manual` AS `m`
            INNER JOIN `titulo` AS `t` ON `m`.`id_titulo` = `t`.`id`
            WHERE `m`.`active` = 1");
    }

    public function getItemManualTitulo(Request $request, $id) {
        return DB::select("SELECT `id`, `item`, `id_titulo` FROM `manual` WHERE `id_titulo` = ?", [$id]);
    }

    public function saveItemManual(Request $request) {
        try {
            DB::insert('INSERT INTO `manual` (`item`, `id_titulo`) VALUES (?, ?)', [$request->item, $request->selectedTitulo]);
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


    // ITEM DO MANUAL FIXO -------------------------------------

    public function getItemManualFixo(Request $request) {

        $arrItems = array();

        $manual = DB::select("SELECT `m`.`id`, `m`.`item`, `m`.`id_titulo`, `t`.`titulo`,
            `m`.`km_ideal`, `m`.`tempo_ideal`, `m`.`observacao_ideal`, `m`.`km_severo`, `m`.`tempo_severo`, `m`.`observacao_severo`
            FROM `manual_fixo` AS `m`
            INNER JOIN `titulo_fixo` AS `t` ON `m`.`id_titulo` = `t`.`id`
            WHERE `m`.`active` = 1");

        for ($i = 0; $i < count($manual); $i++) {
            if (count($arrItems)) {
                $tituloExist = false;
                for ($j = 0; $j < count($arrItems); $j++) {
                    if ($arrItems[$j]['titulo'] === $manual[$i]->id_titulo) {
                        array_push($arrItems[$j]['items'], $manual[$i]);
                        $tituloExist = true;
                        break;
                    }
                }
                if (!$tituloExist) {
                    $arrItem = array();
                    $arrItem['titulo'] = $manual[$i]->id_titulo;
                    $arrItem['txtTitulo'] = $manual[$i]->titulo;
                    $arrItem['items'] = [$manual[$i]];
                    array_push($arrItems, $arrItem);
                }
            } else {
                $arrItem = array();
                $arrItem['titulo'] = $manual[$i]->id_titulo;
                $arrItem['txtTitulo'] = $manual[$i]->titulo;
                $arrItem['items'] = [$manual[$i]];
                array_push($arrItems, $arrItem);
            }
        }

       return $arrItems;
    }

    public function itemManualFixo(Request $request) {
        return DB::select("SELECT `m`.`id`, `m`.`item`, `m`.`id_titulo`, 
            `m`.`km_ideal`, `m`.`tempo_ideal`, `m`.`observacao_ideal`,
            `m`.`km_severo`, `m`.`tempo_severo`, `m`.`observacao_severo`,
            `t`.`titulo` FROM `manual_fixo` AS `m`
            INNER JOIN `titulo_fixo` AS `t` ON `m`.`id_titulo` = `t`.`id`
            WHERE `m`.`active` = 1");
    }

    public function saveItemManualFixo(Request $request) {
        try {
            DB::insert('INSERT INTO `manual_fixo` (`item`, `id_titulo`, 
                `km_ideal`, `tempo_ideal`, `observacao_ideal`, `km_severo`, `tempo_severo`, `observacao_severo`) 
            VALUES (?,?,?,?,?,?,?,?)', [$request->item, $request->selectedTitulo, $request->km_ideal, $request->meses_ideal,
            $request->observacao_ideal, $request->km_severo, $request->meses_severo, $request->observacao_severo]);
            $list = $this->getItemManualFixo($request);

            return $this->successResponse($list, 'Item inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeItemManualFixo(Request $request, $id) {
        try {
            DB::update('UPDATE `manual_fixo` SET `active` = 0 WHERE id = ?', [$request->id]);
            $list = $this->getItemManualFixo($request);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editItemManualFixo(Request $request) {
        try {
            DB::update('UPDATE `manual_fixo` SET `item` = ?, 
                `km_ideal` = ?, `tempo_ideal` = ?, `observacao_ideal` = ?,
                `km_severo` = ?, `tempo_severo` = ?, `observacao_severo` = ? WHERE id = ?',
                [$request->item, $request->km_ideal, $request->meses_ideal, $request->observacao_ideal, 
                $request->km_severo, $request->meses_severo, $request->observacao_severo, $request->id]);
            $list = $this->getItemManualFixo($request);
            $message = 'Item alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    // MANUAL CARRO -----------------------------

    public function getManualCarro(Request $request, $marca, $modelo, $ano, $versao) {
        
        try {
            $arr = array();
            $arrItems = array();

            $manual = DB::select("SELECT `manual_carro`.`id_manual` AS `id`, `manual`.`item`, `manual_carro`.`id_manual`,
                `manual_carro`.`km_ideal`, `manual_carro`.`tempo_ideal` AS `meses_ideal`, `manual_carro`.`observacao_ideal`,
                `manual_carro`.`km_severo`, `manual_carro`.`tempo_severo` AS `meses_severo`, `manual_carro`.`observacao_severo`,
                `titulo`.`titulo`, `manual`.`id_titulo`
                FROM `manual_carro`
                INNER JOIN `manual` ON `manual_carro`.`id_manual` = `manual`.`id`
                INNER JOIN `titulo` ON `manual`.`id_titulo` = `titulo`.`id`
             WHERE `manual_carro`.`id_marca` = ? AND `manual_carro`.`id_modelo` = ? AND 
             `manual_carro`.`ano` = ? AND `manual_carro`.`id_versao` = ? AND `manual_carro`.`active` = 1", [$marca, $modelo, $ano, $versao]);
        

            for ($i = 0; $i < count($manual); $i++) {
                if (count($arrItems)) {
                    $tituloExist = false;
                    for ($j = 0; $j < count($arrItems); $j++) {
                        if ($arrItems[$j]['titulo'] === $manual[$i]->id_titulo) {
                            array_push($arrItems[$j]['items'], $manual[$i]);
                            $tituloExist = true;
                            break;
                        }
                    }
                    if (!$tituloExist) {
                        $arrItem = array();
                        $arrItem['titulo'] = $manual[$i]->id_titulo;
                        $arrItem['txtTitulo'] = $manual[$i]->titulo;
                        $arrItem['items'] = [$manual[$i]];
                        array_push($arrItems, $arrItem);
                    }
                } else {
                    $arrItem = array();
                    $arrItem['titulo'] = $manual[$i]->id_titulo;
                    $arrItem['txtTitulo'] = $manual[$i]->titulo;
                    $arrItem['items'] = [$manual[$i]];
                    array_push($arrItems, $arrItem);
                }
            }

            $arr['manual'] = $arrItems;
            $arr['manual_fixo'] = $this->getItemManualFixo($request);


            $observacao = DB::select("SELECT `observacao` FROM `observacao`
             WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ?", [$marca, $modelo, $ano, $versao]);
            
            $arr['observacao'] = '';

            if(count($observacao)) {
                $arr['observacao'] = $observacao[0]->observacao;
            }

            return $arr;

        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeManualCarro(Request $request, $id_marca, $id_modelo, $ano, $id_versao) {
        try {
            DB::update('UPDATE `manual_carro` SET `active` = 0 WHERE `id_marca` = ? AND `id_modelo` = ?
                AND `ano` = ? AND `id_versao` = ?', [$id_marca, $id_modelo, $ano, $id_versao]);
            
            $list = $this->getListManualModelo($request, $id_modelo);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editManualCarro(Request $request) {
        try {

            for ($i = 0; $i < count($request->itens); $i++) {
                DB::update('UPDATE `manual_carro` SET `km_ideal` = ?, `tempo_ideal` = ?, `observacao_ideal` = ?,
                    `km_severo` = ?, `tempo_severo` = ?, `observacao_severo` = ?
                WHERE id_manual = ? AND id_marca = ? AND id_modelo = ? AND ano = ? AND id_versao = ?', [$request->itens[$i]['km_ideal'], $request->itens[$i]['meses_ideal'], $request->itens[$i]['observacao_ideal'], 
                $request->itens[$i]['km_severo'], $request->itens[$i]['meses_severo'], $request->itens[$i]['observacao_severo'], $request->itens[$i]['id'], 
                $request->marca, $request->modelo, $request->ano, $request->versao]);
            }
            
            DB::update('UPDATE `observacao` SET `observacao` = ? 
                WHERE id_marca = ? AND id_modelo = ? AND ano = ? AND id_versao = ?', 
                [$request->observacao, $request->marca, $request->modelo, $request->ano, $request->versao]);
            
            $list = [];
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


    // LAST MANUAL -------------------------------

    public function lastManual(Request $request) {
        return DB::select("SELECT `mc`.`id_marca`, `ma`.`marca`, `mc`.`id_modelo`, `mo`.`modelo`, `mc`.`ano` as `id_ano`, `m_ano`.`ano` as `ano`, `mc`.`id_versao`, `v`.`versao`
            FROM `manual_carro` AS `mc` INNER JOIN `versao` AS `v` ON `mc`.`id_versao` = `v`.`id_versao`
            INNER JOIN `marca` AS `ma` ON `mc`.`id_marca` = `ma`.`id`
            INNER JOIN `modelo` AS `mo` ON `mc`.`id_modelo` = `mo`.`id`
            INNER JOIN `modelo_ano` AS `m_ano` ON `mc`.`ano` = `m_ano`.`id`
            WHERE `mc`.`active` = 1 
            GROUP BY `mc`.`id_marca`,`mc`.`id_modelo`, `mc`.`ano`, `mc`.`id_versao`
            ORDER BY `mc`.`id` DESC 
            LIMIT 10");
    }

    // LIST MANUAL -------------------------------

    public function getListManual(Request $request) {
        $marca = '';
        $modelo = '';
        $ano = '';
        $versao = '';

        if ($request->selectedMarca) {
            $marca = ' AND `mc`.`id_marca` ='.$request->selectedMarca;
        }

        if ($request->selectedModelo) {
            $modelo = ' AND `mc`.`id_modelo` ='.$request->selectedModelo;
        }

        if ($request->selectedAno) {
            $ano = ' AND `mc`.`ano` ='.$request->selectedAno;
        }

        if ($request->selectedVersao) {
            $versao = ' AND `mc`.`id_versao` ='.$request->selectedVersao;
        }

        return DB::select("SELECT `mc`.`id_marca`, `ma`.`marca`, `mc`.`id_modelo`, `mo`.`modelo`, `mc`.`ano` as `id_ano`, `m_ano`.`ano` as `ano`, `mc`.`id_versao`, `v`.`versao`
            FROM `manual_carro` AS `mc` INNER JOIN `versao` AS `v` ON `mc`.`id_versao` = `v`.`id_versao`
            INNER JOIN `marca` AS `ma` ON `mc`.`id_marca` = `ma`.`id`
            INNER JOIN `modelo` AS `mo` ON `mc`.`id_modelo` = `mo`.`id`
            INNER JOIN `modelo_ano` AS `m_ano` ON `mc`.`ano` = `m_ano`.`id`
            WHERE `mc`.`active` = 1 $marca $modelo $ano $versao ORDER BY `mc`.`id` DESC");
    }

    public function getListManualModelo(Request $request, $modelo) {
        if ($modelo) {
            return DB::select("SELECT `mc`.`id_marca`, `ma`.`marca`, `mc`.`id_modelo`, `mo`.`modelo`, `mc`.`ano`, `mc`.`id_versao`, `v`.`versao`
            FROM `manual_carro` AS `mc` INNER JOIN `versao` AS `v` ON `mc`.`id_versao` = `v`.`id_versao`
            INNER JOIN `marca` AS `ma` ON `mc`.`id_marca` = `ma`.`id`
            INNER JOIN `modelo` AS `mo` ON `mc`.`id_modelo` = `mo`.`id`
            WHERE `mc`.`active` = 1 AND `mc`.`id_modelo` = ? GROUP BY `mc`.`ano`,`mc`.`id_versao` 
            ORDER BY `mc`.`id` DESC", [$modelo]);
        } else {
            
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
}
