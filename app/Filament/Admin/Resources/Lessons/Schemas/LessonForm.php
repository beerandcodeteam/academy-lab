<?php

namespace App\Filament\Admin\Resources\Lessons\Schemas;

use App\Services\PandaServices;
use Community\YoutubePicker\Facades\Youtube;
use Community\YoutubePicker\Forms\Components\YoutubePicker;
use Community\YoutubePicker\Services\YoutubeService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LessonForm
{
    public static function configure(Schema $schema, ?int $ownerRecordId = null): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('module_id')
                        ->searchable()
                        ->preload()
                        ->default(fn () => $ownerRecordId)
                        ->disabled(fn () => !is_null($ownerRecordId))
                        ->label("Modulo")
                        ->columnSpan(10)
                        ->relationship('module', 'name')
                        ->required(),
                    TextInput::make('position')
                        ->label("Posição")
                        ->required()
                        ->numeric()
                        ->columnSpan(2),
                    // Select::make('panda_id')
                    //     ->label('Vídeo Panda')
                    //     ->searchable()
                    //     ->live()
                    //     ->preload(false)
                    //     ->getSearchResultsUsing(fn ($search) => PandaServices::searchVideos($search))
                    //     ->getOptionLabelUsing(fn ($value) => PandaServices::getVideoLabel($value))
                    //     ->afterStateUpdated(function($state, callable $set){
                    //         if (blank($state)) {
                    //             $set('panda_player_url', null);
                    //             $set('panda_thumbnail_url', null);
                    //             return;
                    //         }

                    //         $video = PandaServices::getVideoDetails($state);

                    //         $set('name', $video['title']);
                    //         $set('panda_player_url', $video['video_player'] ?? null);
                    //         $set('panda_thumbnail_url', $video['thumbnail'] ?? null);
                    //     })
                    //     ->helperText('Pesquise pelo título do vídeo no Panda Video e selecione o item desejado.')
                    //     ->columnSpanFull(),
                    YoutubePicker::make('panda_id')
                        ->afterStateUpdated(function($state, callable $set){
                            if (blank($state)) {
                                $set('panda_player_url', null);
                                $set('panda_thumbnail_url', null);
                                return;
                            }

                            $video = Youtube::getVideoDetails($state);

                            $set('name', $video['title']);
                            $set('slug', str($video['title'])->slug());
                            $set('panda_player_url', $video['embed_url'] ?? null);
                            $set('panda_thumbnail_url', $video['thumbnail'] ?? null);
                        })
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->columnSpan(6)
                        ->lazy()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('slug', Str::slug($state));
                        })
                        ->required(),
                    TextInput::make('slug')
                        ->columnSpan(6)
                        ->required(),
                    RichEditor::make('description')
                        ->columnSpanFull(),
                    Hidden::make('panda_player_url'),
                    Hidden::make('panda_thumbnail_url'),
                ])->columns(12)
            ])->columns(1);
    }
}
