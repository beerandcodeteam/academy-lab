# Laravel YouTube Filament Picker
Um pacote para Laravel e FilamentPHP que fornece uma maneira fácil de buscar e selecionar vídeos (públicos ou não listados) de um canal específico do YouTube, ideal para plataformas de cursos e comunidades.

O pacote inclui:

- Um serviço (YoutubeService) para interagir com a API v3 do YouTube Data.

- Um fluxo de autenticação OAuth 2.0 via comando Artisan para acesso seguro a vídeos privados/não listados.

- Um componente de formulário personalizado (YoutubePicker) para o Filament, com busca de vídeos.

- Uma Facade (Youtube) para fácil acesso aos métodos do serviço.

## Instalação (Como Pacote Local)
Este pacote foi desenvolvido para ser usado como um pacote local. Para adicioná-lo ao seu projeto:

1. Coloque a pasta do pacote em um diretório packages na raiz do seu projeto (ex: packages/community/youtube-picker).

2. Adicione o seguinte repositório do tipo path ao composer.json do seu projeto principal:

```JSON
"repositories": [
    {
        "type": "path",
        "url": "packages/community/youtube-picker"
    }
],
```
2. Exija o pacote no composer.json do seu projeto:

```JSON
"require": {
    "community/youtube-picker": "@dev"
}
```
4. Execute o Composer para instalar o pacote:

```Bash
composer update community/youtube-picker
```

## Configuração
A configuração é dividida em 3 etapas: credenciais no Google, variáveis de ambiente no Laravel e geração do token de acesso.

### Importante
Você precisará utilizar um serviço de túnel tipo Ngrok (ferramenta usada na documentação).
1. Acesse https://ngrok.com/ e crie uma conta caso não tenha.
2. Instale o serviço.

> **Se estiver usando docker e sail adicione no seu `composer.yaml`:**
> ```yaml
> services:
>   laravel.test: # Nomenclatura padrão do Laravel/Sail
>   # Restante dos seus serviços
>   ngrok:
>        image: ngrok/ngrok:latest
>        restart: unless-stopped
>        ports:
>            - 4040:4040
>         command: http host.docker.internal:${APP_PORT:-80}
>        environment:
>            - NGROK_AUTHTOKEN=${NGROK_AUTHTOKEN}
>        depends_on:
>            - laravel.test
> ```

### Etapa 1: Configuração no Google Cloud Console
1. Crie um Projeto: Acesse o Google Cloud Console e crie um novo projeto.

2. Ative a API: No menu, vá para APIs e Serviços > Biblioteca, procure por YouTube Data API v3 e ative-a.

3. Configure a Tela de Consentimento:

    - Vá para APIs e Serviços > Tela de consentimento OAuth.

    - Selecione Externo e preencha as informações básicas (nome do app, e-mail de suporte).

    - Na etapa "Usuários de teste", adicione o e-mail da Conta Google que você usará para autorizar o acesso.

4. Crie as Credenciais:

    - Vá para APIs e Serviços > Credenciais.

    - Clique em + CRIAR CREDENCIAIS > ID do cliente OAuth.

    - Selecione Aplicativo da Web.

    - Em URIs de redirecionamento autorizados, adicione a URL de callback. Para desenvolvimento local com Sail + ngrok, será uma URL como https://SUA_URL.ngrok-free.app/youtube/oauth/callback.

    - Clique em CRIAR. Copie o ID do cliente e a Chave secreta do cliente.

### Etapa 2: Variáveis de Ambiente (.env)
1. Publique o arquivo de configuração do pacote (opcional, mas recomendado):

```Bash
php artisan vendor:publish --provider="Community\YoutubePicker\YoutubePickerServiceProvider" --tag="youtube-picker-config"
```

2. Adicione as seguintes variáveis ao seu arquivo .env:

```
# Credenciais obtidas do Google Cloud Console
`YOUTUBE_CLIENT_ID=SEU_ID_DO_CLIENTE_AQUI`
`YOUTUBE_CLIENT_SECRET=SUA_CHAVE_SECRETA_AQUI`


# A URL de redirecionamento EXATA que você configurou no Google Console
`YOUTUBE_REDIRECT_URI=https://SUA_URL_NGROK.ngrok-free.app/youtube/oauth/callback`

# O ID do canal do YouTube que contém os vídeos (começa com "UC...")
`YOUTUBE_CHANNEL_ID=URL_DO_CANAL_QUE_VOCE_QUER_PEGAR_OS_VIDEOS`

# Esta variável será preenchida na próxima etapa
`YOUTUBE_REFRESH_TOKEN=`
```
### Etapa 3: Gerando o Refresh Token
Este pacote inclui um comando Artisan para guiar você pelo processo de autorização OAuth 2.0 e obter o refresh_token necessário para acesso contínuo.

1. Se estiver desenvolvendo com Sail, garanta que seu ambiente e o túnel do ngrok estejam ativos (sail up -d).

2. Certifique-se de que a YOUTUBE_REDIRECT_URI no seu .env e no Google Console correspondem à sua URL pública do ngrok.

3. Execute o comando no seu terminal:

```Bash
sail artisan youtube:generate-refresh-token
```
4. Siga as instruções:

    - Abra a URL de autorização gerada no navegador.

    - Faça login e permita o acesso.

    - Copie o código de autorização da página de callback.

    - Cole o código de volta no terminal.

5. O comando exibirá o `refresh_token`. Copie-o e cole no seu arquivo `.env` na variável `YOUTUBE_REFRESH_TOKEN`.

Após estes passos, sua configuração está completa!

## Uso
### Uso no Painel Filament
O pacote registra um componente de formulário personalizado YoutubePicker.

```PHP

// Em um Resource do Filament, ex: app/Filament/Resources/Forms/LessonForm.php

use Community\YoutubePicker\Forms\Components\YoutubePicker;
use Community\YoutubePicker\Facades\Youtube;
use Filament\Forms\Set;
use Filament\Forms\Components\TextInput;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // ...
            YoutubePicker::make('youtube_video_id') // Nome da sua coluna no BD
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ($video = Youtube::getVideoDetails($state)) {
                        $set('name', $video['title']); // Preenche o título da aula
                        // Adicione outros campos que queira preencher, como:
                        // $set('embed_url', $video['embed_url']); 
                    }
                })
                ->columnSpanFull(),
            
            TextInput::make('name')
                ->label('Título da Aula')
                ->required(),
            // ...
        ]);
}
```
### Uso no Frontend (Views Blade)
Use a Facade `Youtube` para buscar os detalhes completos do vídeo, incluindo o HTML de incorporação, no seu controller ou componente Livewire.

### Exemplo de Controller:

```PHP
use Community\YoutubePicker\Facades\Youtube;
use App\Models\Lesson;

public function show(Lesson $lesson)
{
    $videoDetails = Youtube::getVideoDetails($lesson->youtube_video_id);
    
    return view('lessons.show', compact('lesson', 'videoDetails'));
}
```
### Exemplo na View Blade (`lessons/show.blade.php`):

```html
@if ($videoDetails && !empty($videoDetails['embed_html']))
    {{-- A chave 'embed_html' contém o iframe responsivo e otimizado --}}
    {!! $videoDetails['embed_html'] !!}
@else
    <p>Vídeo não disponível.</p>
@endif
```

> O array `$videoDetails` contém as seguintes chaves:
> 
>   - `id`: `string`
>
>   - `title`: `string`
>
>   - `description`: `string`
> 
>   - `published_at`: `string` 
>
>   - `thumbnail`: `string` (URL da thumbnail do vídeo)
>
>   - `embed_url`: `string` (apenas a URL para o src do iframe)
>
>   - `embed_html`: `string` (o HTML completo do `<iframe>` responsivo)

## Licença
Este pacote está sob a [Licença MIT](LICENSE). Veja o arquivo de licença para mais detalhes.