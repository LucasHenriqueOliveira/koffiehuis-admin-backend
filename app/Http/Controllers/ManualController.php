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
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ? AND `active` = 1",
                [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);

            if (count($item)) {

                return response()->json([
                    'error' => 'Manual já cadastrado para este veículo.'
                ], Response::HTTP_NOT_FOUND);

            } else {

                DB::beginTransaction();

                if (!$request->selectedMarca) {
                    return response()->json([
                        'error' => 'Favor selecionar a marca.'
                    ], Response::HTTP_NOT_FOUND);
                }

                if (!$request->selectedModelo) {
                    return response()->json([
                        'error' => 'Favor selecionar o modelo.'
                    ], Response::HTTP_NOT_FOUND);
                }

                if (!$request->selectedAno) {
                        return response()->json([
                        'error' => 'Favor selecionar o ano.'
                    ], Response::HTTP_NOT_FOUND);
                }

                if (!$request->selectedVersao) {
                    return response()->json([
                        'error' => 'Favor selecionar a versão.'
                    ], Response::HTTP_NOT_FOUND);
                }

                for($i = 0; $i < count($request->itens); $i++) {
                    DB::insert('INSERT INTO `manual_carro` (`id_manual`, `km_ideal`, `tempo_ideal`, `observacao_ideal`, `km_severo`, `tempo_severo`, `observacao_severo`,
                        `id_marca`, `id_modelo`, `ano`, `id_versao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->itens[$i]['id'], $request->itens[$i]['km_ideal'], $request->itens[$i]['meses_ideal'], $request->itens[$i]['observacao_ideal'],
                    $request->itens[$i]['km_severo'], $request->itens[$i]['meses_severo'], $request->itens[$i]['observacao_severo'], $request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);
                }

                // MANUAL CARRO INFO -------------------------------------

                DB::insert('REPLACE INTO `manual_carro_info` (`id_marca`, `id_modelo`, `ano`, `id_versao`,
                        `cabine`, `roda_raio`, `pneu_medida`, `normal_traseira_calibragem_psi`, `normal_dianteira_calibragem_psi`,
                        `completa_traseira_calibragem_psi`, `completa_dianteira_calibragem_psi`, `estepe_calibragem_psi`, 
                        `observacao_geral`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, 
                    $request->selectedCabine, $request->inputRodaRaio, $request->inputPneuMedida, 
                    $request->inputNormalTraseiraCalibragemPsi, $request->inputNormalDianteiraCalibragemPsi,
                    $request->inputCompletaTraseiraCalibragemPsi, $request->inputCompletaDianteiraCalibragemPsi,
                    $request->inputEstepeCalibragemPsi, $request->observacaoInfo]);


                // MANUAL FLUIDO -------------------------------------

                for($i = 0; $i < count($request->fluidos); $i++) {

                    $descricao1 = $descricao2 = $descricao3 = $litros = $observacao = '';

                    if (array_key_exists('descricao1', $request->fluidos[$i])) {
                        $descricao1 = $request->fluidos[$i]['descricao1'];
                    }

                    if (array_key_exists('descricao2', $request->fluidos[$i])) {
                        $descricao2 = $request->fluidos[$i]['descricao2'];
                    }

                    if (array_key_exists('descricao3', $request->fluidos[$i])) {
                        $descricao3 = $request->fluidos[$i]['descricao3'];
                    }

                    if (array_key_exists('litros', $request->fluidos[$i])) {
                        $litros = $request->fluidos[$i]['litros'];
                    }

                    if (array_key_exists('observacao', $request->fluidos[$i])) {
                        $observacao = $request->fluidos[$i]['observacao'];
                    }

                    DB::insert('INSERT INTO `manual_carro_fluido` (`id_marca`, `id_modelo`, `ano`, `id_versao`, 
                        `id_fluido`, `descricao1`, `descricao2`, `descricao3`, `litros`, `observacao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, 
                    $request->fluidos[$i]['id'], $descricao1, $descricao2, $descricao3, $litros, $observacao]);
                }
                
                // MANUAL OBSERVACAO -------------------------------------

                DB::insert('REPLACE INTO `observacao` (`id_marca`, `id_modelo`, `ano`, `id_versao`, `observacao`, `observacao_fluido`) VALUES (?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, $request->observacao, $request->observacaoGeralFluido]);
                
                DB::commit();
                return $this->successResponse(null, 'Plano de manutenção inserido com sucesso.');
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->failedResponse();
        }
    }

    public function copy(Request $request) {
        try {

            $item = DB::select("SELECT * FROM `manual_carro` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ? AND `active` = 1",
                [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao]);

            if (count($item)) {

                return response()->json([
                    'error' => 'Manual já cadastrado para este veículo.'
                ], Response::HTTP_NOT_FOUND);

            } else {

                DB::beginTransaction();

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
                
                // MANUAL CARRO INFO -------------------------------------

                $manual_carro_info = DB::select("SELECT * FROM `manual_carro_info` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ? AND `active` = 1",
                [$request->marcaAntigo, $request->modeloAntigo, $request->anoAntigo, $request->versaoAntigo]);

                if(count($manual_carro_info)) {
                    $info = $manual_carro_info[0];
                    DB::insert('REPLACE INTO `manual_carro_info` (`id_marca`, `id_modelo`, `ano`, `id_versao`,
                        `cabine`, `roda_raio`, `pneu_medida`, `normal_traseira_calibragem_psi`, `normal_dianteira_calibragem_psi`,
                        `completa_traseira_calibragem_psi`, `completa_dianteira_calibragem_psi`, `estepe_calibragem_psi`, 
                        `observacao_geral`, `active`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, 
                    $info->cabine, $info->roda_raio, $info->pneu_medida, 
                    $info->normal_traseira_calibragem_psi, $info->normal_dianteira_calibragem_psi,
                    $info->completa_traseira_calibragem_psi, $info->completa_dianteira_calibragem_psi,
                    $info->estepe_calibragem_psi, $info->observacao_geral, 1]);

                }
                

                // MANUAL FLUIDO -------------------------------------

                $manual_fluido = DB::select("SELECT * FROM `manual_carro_fluido` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ? AND `active` = 1",
                [$request->marcaAntigo, $request->modeloAntigo, $request->anoAntigo, $request->versaoAntigo]);

                for($i = 0; $i < count($manual_fluido); $i++) {
                    DB::insert('INSERT INTO `manual_carro_fluido` (`id_marca`, `id_modelo`, `ano`, `id_versao`, 
                        `id_fluido`, `descricao1`, `descricao2`, `descricao3`, `litros`, `observacao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, 
                    $manual_fluido[$i]->id, $manual_fluido[$i]->descricao1, $manual_fluido[$i]->descricao2,
                    $manual_fluido[$i]->descricao3, $manual_fluido[$i]->litros, $manual_fluido[$i]->observacao]);
                }
                
                // MANUAL OBSERVACAO -------------------------------------

                $observacao = DB::select("SELECT * FROM `observacao` 
                WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ?",
                [$request->marcaAntigo, $request->modeloAntigo, $request->anoAntigo, $request->versaoAntigo]);

                if(count($observacao)) {
                    $obs = $observacao[0];

                    DB::insert('REPLACE INTO `observacao` (`id_marca`, `id_modelo`, `ano`, `id_versao`, `observacao`, `observacao_fluido`) 
                        VALUES (?, ?, ?, ?, ?, ?)', 
                    [$request->selectedMarca, $request->selectedModelo, $request->selectedAno, $request->selectedVersao, 
                    $obs->observacao, $obs->observacao_fluido]);
                }

                DB::commit();
                return $this->successResponse(null, 'Plano de manutenção inserido com sucesso.');
            }
            
        } catch (Exception $e) {
            DB::rollBack();
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
        return DB::select("SELECT `m`.`id`, `m`.`item`, `m`.`id_titulo`, `t`.`titulo`, `m`.`severo` FROM `manual` AS `m`
            INNER JOIN `titulo` AS `t` ON `m`.`id_titulo` = `t`.`id`
            WHERE `m`.`active` = 1");
    }

    public function getItemManualTitulo(Request $request, $id) {
        return DB::select("SELECT `id`, `item`, `id_titulo`, `severo` FROM `manual` WHERE `id_titulo` = ?", [$id]);
    }

    public function saveItemManual(Request $request) {
        try {
            DB::insert('INSERT INTO `manual` (`item`, `id_titulo`, `severo`) VALUES (?, ?, ?)', [$request->item, $request->selectedTitulo, $request->severo]);
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
            DB::update('UPDATE `manual` SET `item` = ?, `severo` = ? WHERE id = ?', [$request->item, $request->severo, $request->id]);
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
            $list = $this->itemManualFixo($request);

            return $this->successResponse($list, 'Item inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeItemManualFixo(Request $request, $id) {
        try {
            DB::update('UPDATE `manual_fixo` SET `active` = 0 WHERE id = ?', [$request->id]);
            $list = $this->itemManualFixo($request);
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
            $list = $this->itemManualFixo($request);
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
                `titulo`.`titulo`, `manual`.`id_titulo`, `manual_carro`.`id` AS `id_manual_carro`
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

            $arr['manual_info'] = '';

            $info = DB::select("SELECT * FROM `manual_carro_info`
             WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ? AND `active` = 1", [$marca, $modelo, $ano, $versao]);

            if(count($info)) {
                $arr['manual_info'] = $info[0];
            }

            $observacao = DB::select("SELECT `observacao`, `observacao_fluido` FROM `observacao`
             WHERE `id_marca` = ? AND `id_modelo` = ? AND `ano` = ? AND `id_versao` = ?", [$marca, $modelo, $ano, $versao]);
            
            $arr['observacao'] = '';

            if(count($observacao)) {
                $arr['observacao'] = $observacao[0]->observacao;
                $arr['observacao_fluido'] = $observacao[0]->observacao_fluido;
            }

            $arr['manual_fluido'] = $this->getListFluido($marca, $modelo, $ano, $versao);

            return $arr;

        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeItemManualCarro(Request $request, $id, $marca, $modelo, $ano, $versao) {
        try {
            DB::update('UPDATE `manual_carro` SET `active` = 0 WHERE `id` = ?', [$id]);
            
            $list = $this->getManualCarro($request, $marca, $modelo, $ano, $versao);
            $message = 'Item deletado com sucesso.';
            return $this->successResponse($list, $message);
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

    public function addItemManual(Request $request) {

        try {

            $item = DB::select("SELECT * FROM `manual_carro` WHERE `id_manual` = ? AND `id_marca` = ? AND `id_modelo` = ? AND 
                `ano` = ? AND `id_versao` = ? AND `active` = 1", [$request->item, $request->marca, $request->modelo, $request->ano, $request->versao]);

            if(count($item)) {
                return response()->json([
                    'error' => 'Item já cadastrado para o veículo.'
                ], Response::HTTP_NOT_FOUND);
            }

            DB::insert('INSERT INTO `manual_carro` (`id_manual`, `id_marca`, `id_modelo`, `ano`, `id_versao`, `km_ideal`,
                `tempo_ideal`, `observacao_ideal`, `km_severo`, `tempo_severo`, `observacao_severo`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
            [$request->item, $request->marca, $request->modelo, $request->ano, $request->versao, $request->km_ideal, $request->meses_ideal, 
            $request->observacao_ideal, $request->km_severo, $request->meses_severo, $request->observacao_severo]);
            
            $list = $this->getManualCarro($request, $request->marca, $request->modelo, $request->ano, $request->versao);

            return $this->successResponse($list, 'Item inserido com sucesso.');
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editManualItem(Request $request) {
        try {
            DB::update('UPDATE `manual_carro` SET `km_ideal` = ?, `tempo_ideal` = ?, `observacao_ideal` = ?, 
                `km_severo` = ?, `tempo_severo` = ?, `observacao_severo` = ? WHERE id = ?', 
                [$request->km_ideal, $request->meses_ideal, $request->observacao_ideal, $request->km_severo, 
                $request->meses_severo, $request->observacao_severo, $request->id]);
            
            $list = $this->getManualCarro($request, $request->id_marca, $request->id_modelo, $request->ano, $request->id_versao);
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
            WHERE `mc`.`active` = 1 $marca $modelo $ano $versao 
            GROUP BY `mc`.`id_marca`,`mc`.`id_modelo`, `mc`.`ano`, `mc`.`id_versao`
            ORDER BY `mc`.`id` DESC");
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


    // FLUIDOS --------------------------------

    public function getListFluido($marca, $modelo, $ano, $versao) {

        return DB::select("SELECT *, `mf`.`id` AS `id_manual_carro_fluido` 
                FROM `manual_carro_fluido` AS `mf` INNER JOIN `fluido` AS `f`
                ON `mf`.`id_fluido` = `f`.`id`
             WHERE `mf`.`id_marca` = ? AND `mf`.`id_modelo` = ? AND `mf`.`ano` = ? AND `mf`.`id_versao` = ? AND `mf`.`active` = 1", [$marca, $modelo, $ano, $versao]);

    }

    public function editFluidoCarro(Request $request) {
        try {
            DB::update('UPDATE `manual_carro_fluido` SET `id_fluido` = ?, `descricao1` = ?, `descricao2` = ?, `descricao3` = ?,
             `litros` = ?, `observacao` = ? WHERE id = ?', [$request->id_fluido, $request->descricao1, $request->descricao2,
             $request->descricao3, $request->litros, $request->observacao, $request->id]);
            $list = $this->getListFluido($request->marca, $request->modelo, $request->ano, $request->versao);
            $message = 'Fluido alterado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function removeFluidoCarro(Request $request) {
        try {
            DB::update('UPDATE `manual_carro_fluido` SET `active` = 0 WHERE id = ?', [$request->id]);
            $list = $this->getListFluido($request->marca, $request->modelo, $request->ano, $request->versao);
            $message = 'Fluido removido com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function addFluidoCarro(Request $request) {
        try {
            DB::insert('INSERT INTO `manual_carro_fluido` (`id_marca`, `id_modelo`, `ano`, `id_versao`,
                `id_fluido`, `descricao1`, `descricao2`, `descricao3`, `litros`, `observacao`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
            [$request->id_marca, $request->id_modelo, $request->ano, $request->id_versao, $request->id_fluido,
            $request->descricao1, $request->descricao2, $request->descricao3, $request->litros, $request->observacao]);
            $list = $this->getListFluido($request->id_marca, $request->id_modelo, $request->ano, $request->id_versao);
            $message = 'Fluido adicionado com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editObservacaoFluidoCarro(Request $request) {
        try {
            DB::update('UPDATE `observacao` SET `observacao_fluido` = ? WHERE id_marca = ? AND id_modelo = ? 
                AND ano = ? AND id_versao = ?', [$request->observacao, $request->id_marca, $request->id_modelo, 
                $request->ano, $request->id_versao]);
            $list = $request->observacao;
            $message = 'Observação alterada com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }


    // MANUAL CARRO INFO -----------------------------------

    public function editObservacaoInfo(Request $request) {
        try {
            DB::update('UPDATE `manual_carro_info` SET `observacao_geral` = ? WHERE id_marca = ? AND id_modelo = ? 
                AND ano = ? AND id_versao = ? AND active = 1', [$request->observacao, $request->id_marca, $request->id_modelo, 
                $request->ano, $request->id_versao]);
            $list = $request->observacao;
            $message = 'Observação alterada com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editObservacaoGeral(Request $request) {
        try {
            DB::update('UPDATE `observacao` SET `observacao` = ? WHERE id_marca = ? AND id_modelo = ? 
                AND ano = ? AND id_versao = ?', [$request->observacao, $request->id_marca, $request->id_modelo, 
                $request->ano, $request->id_versao]);
            $list = $request->observacao;
            $message = 'Observação alterada com sucesso.';
            return $this->successResponse($list, $message);
        } catch (Exception $e) {
            return $this->failedResponse();
        }
    }

    public function editRodas(Request $request) {
        try {
            DB::update('UPDATE `manual_carro_info` SET `cabine` = ?, `roda_raio` = ?, `pneu_medida` = ?, 
                `normal_traseira_calibragem_psi` = ?, `normal_dianteira_calibragem_psi` = ?,
                `completa_traseira_calibragem_psi` = ?, `completa_dianteira_calibragem_psi` = ?, `estepe_calibragem_psi` = ? 
                WHERE id_marca = ? AND id_modelo = ? AND ano = ? AND id_versao = ? AND active = 1', 
                [$request->cabine, $request->roda_raio, $request->pneu_medida, $request->normal_dianteira_calibragem_psi, 
                $request->normal_traseira_calibragem_psi, $request->completa_dianteira_calibragem_psi, 
                $request->completa_traseira_calibragem_psi, $request->estepe_calibragem_psi, 
                $request->id_marca, $request->id_modelo, $request->ano, $request->id_versao]);
            $list['cabine'] = $request->cabine;
            $list['roda_raio'] = $request->roda_raio;
            $list['pneu_medida'] = $request->pneu_medida;
            $list['normal_dianteira_calibragem_psi'] = $request->normal_dianteira_calibragem_psi;
            $list['normal_traseira_calibragem_psi'] = $request->normal_traseira_calibragem_psi;
            $list['completa_dianteira_calibragem_psi'] = $request->completa_dianteira_calibragem_psi;
            $list['completa_traseira_calibragem_psi'] = $request->completa_traseira_calibragem_psi;
            $list['estepe_calibragem_psi'] = $request->estepe_calibragem_psi;
            $message = 'Rodas e pneus alterado com sucesso.';
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
}
