<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\Lockable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Equipment extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog, SoftDeletes, Searchable, Lockable;

    public function supplierPurchaseOrders()
    {
        return $this->belongsToMany(SupplierPurchaseOrder::class, 'equipment_supplier_purchase_order');
    }

    public function dataSheets()
    {
        return $this->hasMany(EquipmentDataSheet::class);
    }

    public function blueprints()
    {
        return $this->hasMany(EquipmentBlueprint::class);
    }

    public function catalogs()
    {
        return $this->hasMany(EquipmentCatalog::class);
    }

    public function technicalSpecifications()
    {
        return $this->hasMany(EquipmentTechnicalSpecification::class);
    }

    public function standards()
    {
        return $this->hasMany(EquipmentStandard::class);
    }

    public function fieldQueries()
    {
        return $this->hasMany(EquipmentFieldQuery::class);
    }

    public function equipmentSpareParts()
    {
        return $this->hasMany(EquipmentSparePart::class);
    }

    public function manuals()
    {
        return $this->hasMany(EquipmentManual::class);
    }

    public function reports()
    {
        return $this->hasMany(EquipmentReport::class);
    }

    protected static function booted()
    {
        static::created(function ($model) {
            $model->updateSearchIndex();
        });

        static::updated(function ($model) {
            $model->updateSearchIndex();
        });

        static::deleted(function ($model) {
            $model->removeFromSearchIndex();
        });
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
