<?php

namespace App\Traits;

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
}