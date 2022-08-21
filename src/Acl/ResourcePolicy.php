<?php

namespace RecursiveTree\Seat\InfoPlugin\Acl;

use RecursiveTree\Seat\InfoPlugin\Model\ArticleAclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use Seat\Web\Acl\Policies\AbstractPolicy;
use Seat\Web\Models\User;

class ResourcePolicy extends AbstractPolicy
{
    public static function view(User $user,$resource_id)
    {
        $resource = Resource::find($resource_id);
        if($resource === null){
            return false;
        }

        $roles = $resource->aclRoles()
            ->where("resource",$resource_id)
            ->where("allows_view",true)
            ->pluck("role");

        return $user
                ->roles()
                ->whereIn("id", $roles)
                ->exists()
            || $user->isAdmin()
            || $user->can("info.edit_all")
            || self::edit($user,$resource_id)
            || $user->id === $resource->owner
            || $resource->owner === null; //after upgrading, keep old resources without a user public
    }

    public static function edit(User $user,$resource_id)
    {
        $resource = Resource::find($resource_id);
        if($resource === null){
            return false;
        }

        $roles = $resource->aclRoles()
            ->where("resource",$resource_id)
            ->where("allows_edit",true)
            ->pluck("role");

        return $user
                ->roles()
                ->whereIn("id", $roles)
                ->exists()
            || $user->isAdmin()
            || $user->can("info.edit_all")
            || $user->id === $resource->owner;
    }
}