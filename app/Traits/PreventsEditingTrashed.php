<?php

namespace App\Traits;

use Filament\Notifications\Notification;
use Illuminate\Support\Str;

trait PreventsEditingTrashed
{
    public function mountCanAuthorizeAccess(): void
    {
        $record = $this->getRecord();

        if ($record && method_exists($record, 'trashed')) {
            abort_unless(
                !$record->trashed(),
                404,
                'No se pueden editar registros archivados.'
            );
        }

        if (method_exists(parent::class, 'mountCanAuthorizeAccess')) {
            parent::mountCanAuthorizeAccess();
        }
    }

    protected function beforeSave(): void
    {
        $record = $this->record;
        $recordTrashed = $record->trashed();

        $record->unlockRecord();

        if ($recordTrashed) {
            $modelClass = $record::class;

            $map = [
                \App\Models\Document::class => route('filament.dashboard.resources.documents.view', ['record' => $record]),
                \App\Models\Project::class => route('filament.dashboard.resources.projects.view', ['record' => $record]),
                \App\Models\Customer::class => route('filament.dashboard.resources.customers.view', ['record' => $record]),
                \App\Models\Equipment::class => route('filament.dashboard.resources.equipment.view', ['record' => $record]),
                \App\Models\Part::class => route('filament.dashboard.resources.parts.view', ['record' => $record]),
                \App\Models\Supplier::class => route('filament.dashboard.resources.suppliers.view', ['record' => $record]),
                \App\Models\Customer::class => route('filament.dashboard.resources.customers.view', ['record' => $record]),
                \App\Models\Person::class => route('filament.dashboard.resources.people.view', ['record' => $record]),
            ];

            redirect($map[$modelClass]);
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();

        $record = $this->record;
        $recordTrashed = $record->trashed();
        $recordName = $this->record->name ?? $this->record->title;

        $recordSpanish = model_to_spanish($record::class);
        $status = $recordSpanish === 'Actividad'
            ? 'archivada'
            : 'archivado';

        if (blank($title) || $recordTrashed) {
            if ($recordTrashed) {
                return Notification::make()
                    ->warning()
                    ->title("$recordSpanish $status")
                    ->body(Str::markdown("El registro ($recordName) no fue actualizado ya que se encuentra **Archivado**."));
            }

            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record->isRecordLocked() && !$this->record->isRecordLockedByCurrentUser()) {
            $message = $this->record->getLockStatusMessage();

            Notification::make()
                ->title('Acceso Denegado')
                ->body(Str::markdown($message))
                ->danger()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        } else {
            $this->record->lockRecord();
        }
    }
}