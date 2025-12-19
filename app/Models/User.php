<?php

namespace App\Models;

use App\Traits\Queryable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Queryable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'division',
        'role_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Columns searchable via ?q= parameter
     */
    protected $searchable = ['name', 'email', 'username', 'division'];

    /**
     * Columns filterable via ?filter[key]= parameter
     */
    protected $filterable = ['role_id', 'division'];

    /**
     * Columns sortable via ?sort= parameter
     */
    protected $sortable = ['id', 'name', 'email', 'created_at', 'updated_at'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'create_by');
    }

    public function csrs()
    {
        return $this->hasMany(Csr::class, 'create_by');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'create_by');
    }
}

