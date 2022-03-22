<?php

namespace RecursiveTree\Seat\InfoPlugin\Acl;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use Seat\Web\Acl\Policies\AbstractPolicy;
use Seat\Web\Models\User;

class ArticlePolicy extends AbstractPolicy
{
    public static function view(User $user,$article_id)
    {
        $article = Article::find($article_id);

        if(!$article) return false;

        $user = auth()->user();

        return $user
                ->roles()
                ->where("id", $article->view_role)
                ->get()
                ->isNotEmpty()
            || $user->isAdmin()
            || $user->can("info.manage_article")
            || self::edit($user,$article_id);
    }

    public static function edit(User $user,$article_id)
    {
        $article = Article::find($article_id);

        if(!$article) return false;

        $user = auth()->user();

        return $user
                ->roles()
                ->where("id", $article->edit_role)
                ->get()
                ->isNotEmpty()
            || $user->can("info.manage_article")
            || $user->isAdmin();
    }
}