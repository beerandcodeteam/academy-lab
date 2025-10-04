<?php

namespace Community\YoutubePicker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array searchVideos(string $searchTerm, int $maxResults = 10)
 * @method static array|null getVideoDetails(string $videoId)
 * @method static string getVideoLabel(?string $videoId)
 */
class Youtube extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'youtube-service';
    }
}