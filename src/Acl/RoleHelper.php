<?php

namespace RecursiveTree\Seat\InfoPlugin\Acl;

use Seat\Web\Models\Acl\Role;

class RoleHelper
{
    public static function getOrCreateRole($id,$title){
        $role = Role::firstOrCreate(["id"=>$id],[
            "title"=>$title,
            "description"=>"auto-generated role",
            "logo"=>null//TODO add image
        ]);

        return $role->id;
    }

    public static function getDefaultViewRole(){
        $id = setting("seatinfo_default_view_role",true);

        $id = self::getOrCreateRole($id,"seat-info default view role");

        setting(["seatinfo_default_view_role",$id],true);

        return $id;
    }

    public static function getDefaultEditRole(){
        $id = setting("seatinfo_default_edit_role",true);

        $id = self::getOrCreateRole($id,"seat-info default edit role");

        setting(["seatinfo_default_edit_role",$id],true);

        return $id;
    }

    public static function checkForExistenceOrDefault($role, $default){
        if ($role == null) return $default;
        return Role::find($role) != null ? $role:$default;
    }
}