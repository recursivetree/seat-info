<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\Acl\Role;

class PermaLink extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'permalink';
    protected $table = 'recursive_tree_seat_info_permalinks';
    protected $keyType = 'string';

    public function article(){
        return $this->belongsTo(Article::class,"id","article");
    }
}