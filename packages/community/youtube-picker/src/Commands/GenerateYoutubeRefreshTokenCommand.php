<?php

namespace Community\YoutubePicker\Commands;

use Google\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateYoutubeRefreshTokenCommand extends Command
{
    protected $signature = 'youtube:generate-refresh-token';
    protected $description = 'Generate a YouTube API refresh token using OAuth2 for a single channel.';

    public function handle(): int
    {
        $this->info("--------------------------------------------------");
        $this->info("Gerador de Refresh Token para a API do YouTube");
        $this->info("--------------------------------------------------");

        if (!config('youtube.client_id') || !config('youtube.client_secret')) {
            $this->error('YOUTUBE_CLIENT_ID e YOUTUBE_CLIENT_SECRET não estão configurados no seu arquivo .env.');
            return self::FAILURE;
        }

        try {
            $client = new Client();
            $client->setClientId(config('youtube.client_id'));
            $client->setClientSecret(config('youtube.client_secret'));
            $client->setRedirectUri(config('youtube.redirect_uri'));
            $client->setScopes([
                'https://www.googleapis.com/auth/youtube.readonly',
            ]);
            $client->setAccessType('offline');
            $client->setPrompt('consent'); // Força a exibição da tela de consentimento

            // 1. Gera e exibe a URL de autorização
            $authUrl = $client->createAuthUrl();
            $this->line("1. Abra a seguinte URL no seu navegador:\n");
            $this->comment($authUrl);
            $this->line("\n2. Faça login com a conta do YouTube da sua comunidade e autorize o acesso.");
            $this->line("3. Você será redirecionado para uma página de callback. Copie o 'código de autorização' exibido.");

            // 2. Pede para o usuário colar o código
            $authCode = $this->ask('4. Cole o código de autorização aqui');

            // 3. Troca o código pelo token de acesso e refresh token
            $this->info("\nObtendo tokens...");
            $accessToken = $client->fetchAccessTokenWithAuthCode(trim($authCode));

            if (!isset($accessToken['refresh_token'])) {
                $this->error('Não foi possível obter um refresh token. Isso pode acontecer se você já autorizou este aplicativo antes.');
                $this->comment('Tente revogar o acesso do aplicativo em https://myaccount.google.com/permissions e rode o comando novamente.');
                return self::FAILURE;
            }

            $refreshToken = $accessToken['refresh_token'];

            // 4. Exibe o refresh token e instrui o usuário
            $this->info("\n--- SUCESSO! ---");
            $this->line("Seu Refresh Token está pronto. Adicione a seguinte linha ao seu arquivo .env:");
            $this->warn("\nYOUTUBE_REFRESH_TOKEN={$refreshToken}\n");
            $this->info("Após adicionar ao .env, seu setup estará completo.");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Ocorreu um erro: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}