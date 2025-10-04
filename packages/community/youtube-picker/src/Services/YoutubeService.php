<?php

namespace Community\YoutubePicker\Services;

use Google\Client;
use Google\Service\YouTube;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class YoutubeService
{
    protected Client $googleClient;
    protected ?YouTube $youtubeService = null;

    public function __construct()
    {
        try {
            $this->googleClient = new Client();
            $this->googleClient->setClientId(config('youtube.client_id'));
            $this->googleClient->setClientSecret(config('youtube.client_secret'));
            $this->googleClient->setRedirectUri(config('youtube.redirect_uri'));
            $this->googleClient->setAccessType('offline');

            $accessToken = $this->getValidAccessToken();

            if ($accessToken) {
                $this->googleClient->setAccessToken($accessToken);
                $this->youtubeService = new YouTube($this->googleClient);
            }
        } catch (\Exception $e) {
            Log::error('Falha ao inicializar o YoutubeService: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getValidAccessToken(): ?array
    {
        $cacheKey = config('youtube.cache_key');

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $refreshToken = config('youtube.refresh_token');
        if (!$refreshToken) {
            Log::warning('YOUTUBE_REFRESH_TOKEN não configurado.');
            throw new \Exception('YOUTUBE_REFRESH_TOKEN não configurado.');
        }

        try {
            $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
            $newAccessToken = $this->googleClient->getAccessToken();

            $expiresIn = $newAccessToken['expires_in'] ?? 3599;
            Cache::put($cacheKey, $newAccessToken, now()->addSeconds($expiresIn - 60));

            return $newAccessToken;
        } catch (\Exception $e) {
            Log::error('Falha ao obter o novo access token com refresh token: ' . $e->getMessage());
            throw $e;
        }
    }

    function searchVideos(string $searchTerm, int $maxResults = 10): array
    {
        if (!$this->youtubeService) {
            return ['error' => 'Serviço do Youtube não inicializado. Verifique as credenciais.'];
        }

        try {
            $params = [
                'part' => 'snippet',
                'q' => $searchTerm,
                'maxResults' => $maxResults,
                'type' => 'video',
                'channel_id' => config('youtube.channel_id')
            ];

            $response = $this->youtubeService->search()->listSearch('snippet', $params);

            $videoList = [];
            foreach($response->getItems() as $item) {
                $videoList[$item->id->videoId] = $item->snippet->title;
            }
            return $videoList;
        } catch (\Exception $e) {
            Log::error('Erro na busca de vídeos no Youtube: '. $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}