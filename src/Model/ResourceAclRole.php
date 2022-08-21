<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\Acl\Role;

class ResourceAclRole extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_resources_acl_roles';

    protected $fillable = [
        'resource', 'allows_view', 'allows_edit', 'role'
    ];

    public function resource(){
        return $this->belongsTo(Resource::class,"id","resource");
    }

    public function roleModel(){
        return $this->belongsTo(Role::class,"role","id");
    }
}