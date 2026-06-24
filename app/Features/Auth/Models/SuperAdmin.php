<?php

namespace App\Features\Auth\Models;

use App\Models\Concerns\HasSqid;
use Database\Factories\SuperAdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password', 'avatar', 'status', 'last_login'])]
#[Hidden(['password', 'remember_token'])]
/**
 * Eloquent model for super admin.
 */
class SuperAdmin extends Authenticatable
{
    /** @use HasFactory<SuperAdminFactory> */
    use HasFactory, HasSqid;

    protected static function newFactory(): SuperAdminFactory
    {
        return SuperAdminFactory::new();
    }

    protected $table = 'super_admins';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }
}
