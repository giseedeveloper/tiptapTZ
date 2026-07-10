<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantBranchGroup extends Model
{
    protected $fillable = ['name', 'logo'];

    public function branches(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'branch_group_id')
                    ->orderBy('branch_sort_order');
    }

    public function activeBranches(): HasMany
    {
        return $this->branches()->where('is_active', true);
    }
}
