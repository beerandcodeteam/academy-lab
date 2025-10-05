<?php

namespace Community\YoutubePicker;

use Community\YoutubePicker\Commands\GenerateYoutubeRefreshTokenCommand;
use Community\YoutubePicker\Services\YoutubeService;
use Illuminate\Support\ServiceProvider;

class YoutubePickerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/youtube.php', 'youtube');

        $this->app->singleton('youtube-service', function ($app) {
            return new YoutubeService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        $this->commands([
            GenerateYoutubeRefreshTokenCommand::class
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/youtube.php' => config_path('youtube.php'),
            ], 'youtube-picker-config');
        }
    }
}