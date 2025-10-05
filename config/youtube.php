<?php

return [
    'client_id' => env('YOUTUBE_CLIENT_ID'),
    'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
    'redirect_uri' => env('YOUTUBE_REDIRECT_URI'), // Ex: http://localhost/youtube/oauth/callback
    'channel_id' => env('YOUTUBE_CHANNEL_ID'), // O ID do seu canal do Youtube
    'refresh_token' => env('YOUTUBE_REFRESH_TOKEN'),
];