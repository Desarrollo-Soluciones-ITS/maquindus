<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.edit')),
        ];
    }

    public function getViewData(): array
    {
        return [
            'folderStructure' => [
                'root' => 'Equipos/' . $this->record->name,
                'folders' => [
                    'Consultas de Campo',
                    'Especificaciones Tecnicas' => [
                        'Catálogos',
                        'Hoja De Datos',
                        'Manuales',
                        'Normas',
                        'Planos',
                        'Revisiones',
                    ],
                    'General',
                    'Reportes',
                    'Repuestos',
                ],
            ],
        ];
    }
}
