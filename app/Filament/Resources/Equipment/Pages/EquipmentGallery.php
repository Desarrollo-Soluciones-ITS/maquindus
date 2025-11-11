<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;

class EquipmentGallery extends Page
{
    use InteractsWithRecord;

    protected static string $resource = EquipmentResource::class;

    protected string $view = 'filament.pages.gallery';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Galería';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Galería de Imágenes';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $totalImages = $this->record->images()->count();
        $imageCountLabel = $totalImages === 1 ? 'imagen' : 'imágenes';

        return 'Visualiza todas las imágenes: ' . $totalImages . ' ' . $imageCountLabel;
    }

    public function getImages()
    {
        return $this->record->images->map(function ($image) {
            return [
                'id' => $image->id,
                'path' => Storage::temporaryUrl($image->path, now()->addMinutes(30)),
                'owner_name' => $this->record->name,
                'created_at' => $image->created_at->format('d/m/Y'),
            ];
        });
    }
}
