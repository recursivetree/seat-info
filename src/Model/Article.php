<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

class Article extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_articles';

    protected $fillable = [
        'name', 'text', 'id', 'home_entry', 'public'
    ];

    public function aclRoles(){
        return $this->hasMany(ArticleAclRole::class,"article","id");
    }

    public function owner_user(){
        return $this->hasOne(User::class,'id','owner');
    }
}