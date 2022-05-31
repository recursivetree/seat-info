<?php

namespace RecursiveTree\Seat\InfoPlugin\Acl;

use RecursiveTree\Seat\InfoPlugin\Model\AclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use Seat\Web\Acl\Policies\AbstractPolicy;
use Seat\Web\Models\User;

class ArticlePolicy extends AbstractPolicy
{
    public static function view(User $user,$article_id)
    {
        $roles = AclRole::where("article",$article_id)
            ->where("allows_view",true)
            ->pluck("role");

        return $user
                ->roles()
                ->whereIn("id", $roles)
                ->exists()
            || $user->isAdmin()
            || $user->can("info.manage_article")
            || self::edit($user,$article_id);
    }

    public static function edit(User $user,$article_id)
    {
        $roles = AclRole::where("article",$article_id)
            ->where("allows_edit",true)
            ->pluck("role");

        $user = auth()->user();

        return $user
                ->roles()
                ->whereIn("id", $roles)
                ->exists()
            || $user->can("info.manage_article")
            || $user->isAdmin();
    }
}