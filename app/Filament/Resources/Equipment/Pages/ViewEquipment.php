<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.edit')),
        ];
    }

    public function getRelationManagersContentComponent(): Group
    {
        $ownerRecord = $this->getRecord();
        $managerLivewireData = ['ownerRecord' => $ownerRecord, 'pageClass' => static::class];

        return Group::make(collect($this->getRelationManagers())
            ->map(function ($manager) use ($managerLivewireData) {
                if ($manager instanceof \Filament\Resources\RelationManagers\RelationGroup) {
                    $manager->ownerRecord($this->getRecord());
                    $manager->pageClass(static::class);

                    return Section::make($manager->getLabel())
                        ->schema(collect($manager->getManagers())
                            ->map(function ($groupedManager, $groupedManagerKey) use ($managerLivewireData) {
                                $normalizedGroupedManagerClass = $this->normalizeRelationManagerClass($groupedManager);

                                return Livewire::make(
                                    $normalizedGroupedManagerClass,
                                    [...$managerLivewireData, ...(($groupedManager instanceof \Filament\Resources\RelationManagers\RelationManagerConfiguration) ? [...$groupedManager->relationManager::getDefaultProperties(), ...$groupedManager->getProperties()] : $groupedManager::getDefaultProperties())],
                                )->key("{$normalizedGroupedManagerClass}-{$groupedManagerKey}");
                            })
                            ->all());
                }

                $normalizedManagerClass = $this->normalizeRelationManagerClass($manager);

                return Livewire::make(
                    $normalizedManagerClass,
                    [...$managerLivewireData, ...(($manager instanceof \Filament\Resources\RelationManagers\RelationManagerConfiguration) ? [...$manager->relationManager::getDefaultProperties(), ...$manager->getProperties()] : $manager::getDefaultProperties())],
                )->key($normalizedManagerClass);
            })
            ->all());
    }
}
