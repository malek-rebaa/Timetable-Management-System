<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantModel;

class Room extends Model
{
    use HasFactory, TenantModel;

    protected $fillable = ['name', 'capacity', 'type'];

    public function academicSessions()
    {
        return $this->hasMany(AcademicSession::class);
    }
}
