<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\Acl\Role;

class ArticleAclRole extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_articles_acl_roles';

    protected $fillable = [
        'article', 'allows_view', 'allows_edit', 'role'
    ];

    public function article(){
        return $this->belongsTo(Article::class,"id","article");
    }

    public function roleModel(){
        return $this->belongsTo(Role::class,"role","id");
    }
}