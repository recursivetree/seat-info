<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_articles';

    protected $fillable = [
        'name', 'text', 'id', 'home_entry', 'public'
    ];

    public function aclRoles(){
        return $this->hasMany(AclRole::class,"article","id");
    }
}