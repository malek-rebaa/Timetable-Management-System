<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $connection = 'master';

    protected $fillable = [
        'name',
        'slug',
        'database_name',
        'status',
        'logo_path',
        'primary_color',
        'secondary_color',
        'timezone',
        'locale',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(SchoolMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }
}
