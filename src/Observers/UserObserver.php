<?php

namespace RecursiveTree\Seat\InfoPlugin\Observers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;

class UserObserver
{
    public static function deleted($user){
        $articles = Article::where("owner",$user->id)->get();
        foreach ($articles as $article){
            $article->owner = null;
            $article->save();
        }
    }
}