<?php

namespace App\Filament\Actions;

use App\Models\Person;
use Filament\Actions\Action;
use Filament\Actions\EditAction as FilamentEditAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditAction
{
    public static function make(): Action
    {
        return FilamentEditAction::make()
            ->using(function (Model $record, array $data): Model {
                $data = collect($data);
                $oldName = $record->name;
                $newName = $data['name'];
                $oldEmail = $record->email ?? null;
                $newEmail = $data['email'] ?? null;

                $isDocumentable = method_exists($record, 'documents');

                if ($isDocumentable) {
                    if ($record instanceof Person && ($oldName !== $newName || $oldEmail !== $newEmail)) {
                        $oldFullName = $oldName . ' - ' . $oldEmail;
                        $newFullName = $newName . ' - ' . $newEmail;
                        handle_documentable_name_change($record, $oldFullName, $newFullName);
                    } else if ($oldName !== $newName) {
                        handle_documentable_name_change($record, $oldName, $newName);
                    }
                }

                $record->update($data->all());

                return $record;
            })
            ->label(function (Model $record) {
                if (!$record->isRecordLockedByCurrentUser()) {
                    $timeLeft = $record->getLockTimeLeft();
                    return $timeLeft ? "⏰ {$timeLeft}" : 'Editar';
                }

                return 'Editar';
            })
            ->beforeFormFilled(function (FilamentEditAction $action, Model $record) {
                if ($record->isRecordLocked() && !$record->isRecordLockedByCurrentUser()) {
                    $message = $record->getLockStatusMessage();
                    $model = model_to_spanish($record::class);

                    Notification::make()
                        ->title("{$model} en uso")
                        ->body($message)
                        ->warning()
                        ->send();

                    $action->halt();
                    return;
                }

                $record->lockRecord();
            })
            ->after(function (Model $record) {
                if ($record->isRecordLockedByCurrentUser()) {
                    $record->unlockRecord();
                }
            });
    }
}
