<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

class Resource extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_resources';

    protected $fillable = [
        'id', 'path', 'mime', "name", "owner"
    ];

    public function owner_user(){
        return $this->hasOne(User::class,'id','owner');
    }

    public function aclRoles(){
        return $this->hasMany(ResourceAclRole::class,"resource","id");
    }
}