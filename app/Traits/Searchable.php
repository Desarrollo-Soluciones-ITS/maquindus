<?php

namespace App\Traits;

use App\Services\SearchIndexer;

trait Searchable
{
    public static function getSearchableFieldsMap(): array
    {
        return [
            'App\Models\Customer' => ['rif', 'name', 'email', 'phone', 'about', 'address'],
            'App\Models\Document' => ['name', 'category'],
            'App\Models\Equipment' => ['name', 'code', 'about', 'details'],
            'App\Models\Part' => ['name', 'code', 'details', 'about'],
            'App\Models\Person' => ['name', 'email', 'address', 'phone', 'position'],
            'App\Models\Project' => ['name', 'code', 'about', 'status'],
            'App\Models\Supplier' => ['rif', 'name', 'email', 'phone', 'about', 'address'],
            'App\Models\User' => ['name', 'email'],
        ];
    }

    public function getSearchResultName(): string
    {
        if (isset($this->name) && filled($this->name)) {
            return $this->name;
        }

        foreach (['title', 'code', 'rif', 'email'] as $field) {
            if (isset($this->$field) && filled($this->$field)) {
                return $this->$field;
            }
        }
        return (string) $this->getKey();
    }

    public function getSearchResultDescription(): ?string
    {
        $map = self::getSearchableFieldsMap();
        $class = static::class;
        $descriptionParts = [];

        if (isset($map[$class])) {
            $fields = $map[$class];
            $fields = array_diff($fields, ['name']);

            foreach ($fields as $field) {
                if (isset($this->$field) && filled($this->$field)) {
                    $value = $this->$field;
                    if (is_array($value) || is_object($value)) {
                        continue;
                    }
                    if (is_string($value) && strlen($value) > 50) {
                        $value = substr($value, 0, 47) . '...';
                    }
                    $descriptionParts[] = $value;
                }
            }
        } else {
            foreach (['about', 'comment', 'description', 'position', 'address', 'phone'] as $field) {
                if (isset($this->$field) && filled($this->$field)) {
                    $descriptionParts[] = $this->$field;
                }
            }
        }

        return !empty($descriptionParts) ? implode(' • ', array_slice($descriptionParts, 0, 4)) : null;
    }

    /**
     * Concatena todos los campos buscables en un solo string.
     */
    public function getSearchableContent(): string
    {
        $content = collect();
        foreach (static::getSearchableColumns() as $column) {
            $value = $this->$column;

            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof \UnitEnum) {
                $value = $value->name;
            }

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if (filled($value)) {
                $content->push($value);
            }
        }
        return $content->join(' ');
    }

    public static function getSearchableColumns(): array
    {
        $map = self::getSearchableFieldsMap();
        $class = static::class;

        if (isset($map[$class])) {
            return $map[$class];
        }

        $table = (new static)->getTable();
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);

        return array_filter($columns, function ($column) {
            return !in_array($column, [
                'id',
                'password',
                'remember_token',
                'deleted_at',
                'payload',
                'properties',
                'details',
                'path',
                'mime',
                'file_size',
                'version',
                'batch_uuid',
                'created_at',
                'updated_at'
            ]);
        });
    }

    public function updateSearchIndex()
    {
        app(SearchIndexer::class)->update($this);
    }

    public function removeFromSearchIndex()
    {
        app(SearchIndexer::class)->delete($this);
    }
}