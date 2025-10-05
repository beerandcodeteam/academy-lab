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

    public function searchVideos(string $searchTerm, int $maxResults = 10): array
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
                'channelId' => config('youtube.channel_id')
            ];

            $response = $this->youtubeService->search->listSearch('snippet', $params);

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

    public function getVideoLabel(?string $videoId): string
    {
        if (empty($videoId)) {
            return '';
        }

        $details = $this->getVideoDetails($videoId);

        return $details['title'] ?? 'Vídeo não encontrado';
    }

    public function getVideoDetails(?string $videoId): ?array
    {
        if (empty($videoId) || !$this->youtubeService) {
            return null;
        }

        return Cache::rememberForever("youtube_details_{$videoId}", function () use ($videoId) {
            try {
                $response = $this->youtubeService->videos->listVideos('snippet', ['id' => $videoId]);

                if (empty($response->getItems())) {
                    return null; 
                }

                $video = $response->getItems()[0];

                $embedUrl = sprintf(
                    'https://www.youtube.com/embed/%s?rel=0&showinfo=0&iv_load_policy=3&modestbranding=1&origin=%s',
                    $video->getId(),
                    url('/') 
                );

                $embedHtml = sprintf(
                    '<div style="position: relative; padding-bottom: 56.25%%; height: 0; overflow: hidden; max-width: 100%%;">
                        <iframe
                            src="%s"
                            style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%;"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            title="%s">
                        </iframe>
                    </div>',
                    $embedUrl,
                    htmlspecialchars($video->snippet->title)
                );

                return [
                    'id'            => $video->getId(),
                    'title'         => $video->snippet->title,
                    'description'   => $video->snippet->description,
                    'published_at'  => $video->snippet->publishedAt,
                    'thumbnail'     => $video->snippet->thumbnails->high->url ?? null,
                    'embed_url'     => $embedUrl,
                    'embed_html'    => $embedHtml # Se precisar no futuro
                ];

            } catch (\Exception $e) {
                Log::error('Erro ao buscar detalhes do vídeo do YouTube: ' . $e->getMessage(), ['video_id' => $videoId]);
                return null;
            }
        });
    }
}