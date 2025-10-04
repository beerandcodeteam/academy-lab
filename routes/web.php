<?php

use Community\YoutubePicker\Facades\Youtube;
use Illuminate\Support\Facades\Route;

Route::get('/test-youtube', function() {
    // Tenta buscar vídeos com o termo "laravel" no canal configurado no .env
    $videos = Youtube::searchVideos('laravel', 5);

    // O dd() vai parar a execução e mostrar o resultado na tela.
    dd($videos); 
});