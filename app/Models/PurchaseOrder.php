<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_no',
        'description',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_purchase_order');
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'equipment_purchase_order');
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_purchase_order');
    }
}
