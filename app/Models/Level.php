<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantModel;

class Level extends Model
{
    use HasFactory, TenantModel;

    protected $fillable = ['name'];

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjectPlans()
    {
        return $this->hasMany(SubjectPlan::class);
    }
}
