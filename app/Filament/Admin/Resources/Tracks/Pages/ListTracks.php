<?php

namespace App\Filament\Admin\Resources\Tracks\Pages;

use App\Filament\Admin\Resources\Tracks\TrackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTracks extends ListRecords
{
    protected static string $resource = TrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
