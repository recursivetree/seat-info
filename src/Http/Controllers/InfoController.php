<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Validation\DeleteArticle;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use Seat\Web\Http\Controllers\Controller;


class InfoController extends Controller
{
    public function getEditView(){
        return view("info::edit");
    }

    public function getCreateView(){
        return view("info::edit");
    }

    public function getSaveInterface(SaveArticle $request){
        $article = new Article();

        $article->name = $request->name;
        $article->text = $request->text;

        $article->save();
        $articles = Article::all();

        return view("info::manage", [
            'article_saved' => [
                'name' => $request->name
            ],
            'articles' => $articles
        ]);
    }

    public function getDeleteInterface(DeleteArticle $request){
        Article::destroy($request->id);
        $articles = Article::all();

        return view("info::manage", [
            'article_deleted' => [
                'name' => $request->name
            ],
            'articles' => $articles
        ]);
    }

    public function getListView(){
        $articles = Article::all();
        return view("info::list", compact('articles'));
    }

    public function getManageView(){
        $articles = Article::all();
        return view("info::manage", compact('articles'));
    }
}