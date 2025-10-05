<?php

namespace Community\YoutubePicker\Forms\Components;

use Filament\Forms\Components\Select;
use Community\YoutubePicker\Facades\Youtube;

class YoutubePicker extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable()
            ->label('Video no Youtube')
            ->live()
            ->preload(false)
            ->getSearchResultsUsing(function (string $search) {
                if (strlen($search) < 3) {
                    return [];
                }

                return Youtube::searchVideos($search);
            })
            ->getOptionLabelUsing(fn ($value): ?string => Youtube::getVideoLabel($value))
            ->placeholder('Digite para buscar um vídeo no Youtube')
            ->required();
    }
}