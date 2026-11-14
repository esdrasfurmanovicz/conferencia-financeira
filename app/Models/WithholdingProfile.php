<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithholdingProfile extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function rules()
    {
        return $this->hasMany(WithholdingProfileRule::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
