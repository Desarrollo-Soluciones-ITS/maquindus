<?php

namespace App\Models;

use App\Traits\Lockable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPurchaseOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Lockable;

    protected $fillable = [
        'supplier_id',
        'order_no',
        'description',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_supplier_purchase_order');
    }
}