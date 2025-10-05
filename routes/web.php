<?php

use Community\YoutubePicker\Facades\Youtube;
use Illuminate\Support\Facades\Route;

Route::get('/test-youtube', function() {
    $videos = Youtube::searchVideos('laravel', 5);

    dd($videos); 
});