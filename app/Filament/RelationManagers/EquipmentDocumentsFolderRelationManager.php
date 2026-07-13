<?php

namespace App\Filament\RelationManagers;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class EquipmentDocumentsFolderRelationManager extends DocumentsRelationManager
{
    protected static string $folderPath = '';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query) {
                if (blank(static::$folderPath)) {
                    return $query;
                }

                $folderPath = str(static::$folderPath)
                    ->replace('\\', '/')
                    ->trim('/')
                    ->toString();

                return $query->whereHas('current', function (Builder $query) use ($folderPath) {
                    $query->where('path', 'like', "%/{$folderPath}/%");
                });
            });
    }
}
