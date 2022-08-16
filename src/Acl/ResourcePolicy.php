<?php

namespace RecursiveTree\Seat\InfoPlugin\Acl;

use RecursiveTree\Seat\InfoPlugin\Model\AclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use Seat\Web\Acl\Policies\AbstractPolicy;
use Seat\Web\Models\User;

class ResourcePolicy extends AbstractPolicy
{
    public static function view(User $user,$resource_id)
    {
        return true;
    }

    public static function edit(User $user,$resource_id)
    {
        $resource = Resource::find($resource_id);

        if(!$resource){
            return false;
        }

        $user = auth()->user();

        return $resource->owner === $user->id
            || $user->can("info.edit_all")
            || $user->isAdmin();
    }
}