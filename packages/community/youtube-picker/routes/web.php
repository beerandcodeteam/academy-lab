<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/oauth/callback', function (Request $request) {
    if ($request->has('code')) {
        return "CÓDIGO DE AUTORIZAÇÃO OBTIDO!<br><br>Copie o código abaixo e cole no seu terminal:<br><br><pre style='font-size:1.5em; background-color:#eee; padding:10px; border:1px solid #ccc;'>{$request->get('code')}</pre>";
    }
    return "Ocorreu um erro ou a autorização foi negada.";
});