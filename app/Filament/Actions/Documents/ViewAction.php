<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\ViewAction as FilamentViewAction;
use Filament\Support\Icons\Heroicon;

class ViewAction
{
    public static function make(): Action
    {
        return FilamentViewAction::make()
            ->label('Versiones')
            ->icon(Heroicon::ListBullet);
    }
}

