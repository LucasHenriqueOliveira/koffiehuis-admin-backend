<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function getUsers(Request $request) {
        return DB::select("SELECT `name` as `nome`, `email`, DATE_FORMAT(`created_at`, '%d/%m/%Y %H:%i') AS `data`
        FROM `users`");
    }
}
