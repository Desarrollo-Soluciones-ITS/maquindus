<?php

namespace App\Filament\Actions\Documents;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction as FilamentViewAction;
use Filament\Support\Icons\Heroicon;

class ViewAction
{
    public static function make(): Action
    {
        return FilamentViewAction::make('view')
            ->label('Versiones')
            ->url(function ($record) {
                return DocumentResource::getUrl('view', [
                    'record' => $record->id
                ]);
            })
            ->icon(Heroicon::ListBullet);
    }
}

