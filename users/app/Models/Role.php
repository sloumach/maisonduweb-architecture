<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function assignPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }
        return $this->permissions()->syncWithoutDetaching($permission);
    }

    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }
        return $this->permissions()->detach($permission);
    }

}
