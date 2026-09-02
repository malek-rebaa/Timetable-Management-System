<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SystemRole extends Model
{
    protected $connection = 'master';

    public $timestamps = false;

    protected $fillable = ['code'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'system_role_user');
    }
}
