<?php

namespace App\Filament\RelationManagers;

class EquipmentManualesRelationManager extends EquipmentDocumentsFolderRelationManager
{
    protected static ?string $title = 'Manuales';
    protected static string $folderPath = 'Especificaciones Tecnicas/Manuales';
}
