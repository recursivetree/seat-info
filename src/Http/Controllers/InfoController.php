<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Validation\CommonModifyArticleRequest;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Database\QueryException;


class InfoController extends Controller
{
    public function getHomeView(){
        try {
            $article = Article::where("home_entry",true)->first();

            if ($article===null){
                return view("info::view", [
                    'error_message' => "No home page configured!",
                ]);
            }

            return view("info::view", [
                "title" => $article->name,
                "content" => $article->text
            ]);
        } catch (QueryException $e){
            return view("info::view", [
                'error_message' => "Could not fetch article!",
            ]);
        }
    }

    public function getSetHomeArticleInterface(CommonModifyArticleRequest $request){
        try {
            Article::query()->update(['home_entry' => false]);

            $article = Article::find($request->id);
            $article->home_entry = true;
            $article->save();

            $articles = Article::all();

            return view("info::manage", [
                'articles' => $articles
            ]);
        } catch (QueryException $e){
            $articles = Article::all();

            return view("info::manage", [
                'error_message' => "Could not delete article!",
                'articles' => $articles
            ]);
        }
    }

    public function getCreateView(){
        return view("info::edit", [
            "id" => 0,
            "name" => "",
            "text" => ""
        ]);
    }

    public function getSaveInterface(SaveArticle $request){
        try {

            $article = Article::find($request->id);

            if ($article===null){
                $article = new Article();
            }


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
        } catch (QueryException $e){
            $articles = Article::all();

            return view("info::manage", [
                'error_message' => "Could not save article!",
                'articles' => $articles
            ]);
        }
    }

    public function getDeleteInterface(CommonModifyArticleRequest $request){
        try {
            $name = $article = Article::find($request->id)->name;
            Article::destroy($request->id);
            $articles = Article::all();

            return view("info::manage", [
                'article_deleted' => [
                    'name' => $name
                ],
                'articles' => $articles
            ]);
        } catch (QueryException $e){
            $articles = Article::all();

            return view("info::manage", [
                'error_message' => "Could not delete article!",
                'articles' => $articles
            ]);
        }
    }

    public function getEditView(CommonModifyArticleRequest $request){
        $article = Article::find($request->id);

        if ($article===null){
            $articles = Article::all();

            return view("info::manage", [
                'error_message' => "Could not open article!",
                'articles' => $articles
            ]);
        }

        return view("info::edit", [
            "id" => $article->id,
            "name" => $article->name,
            "text" => $article->text
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

    public function getArticleView($id){
        try {
            $article = Article::find($id);

            if ($article===null){
                return view("info::view", [
                    'error_message' => "Could not find article!",
                ]);
            }

            return view("info::view", [
                "title" => $article->name,
                "content" => $article->text
            ]);
        } catch (QueryException $e){
            return view("info::view", [
                'error_message' => "Could not fetch article!",
            ]);
        }
    }
}