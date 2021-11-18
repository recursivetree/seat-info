<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Validation\GenericModifyArticleRequest;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Database\QueryException;


class InfoController extends Controller
{
    public function getHomeView(){

        $article = Article::where("home_entry",true)->first();

        if ($article===null){
            return view("info::view")->with('message', [
                'title' => "Error",
                'message' => 'There is no home article configured. Please contact the administrator about this.'
            ]);
        }


        return view("info::view", [
            "title" => $article->name,
            "content" => $article->text
        ]);

    }

    public function getSetHomeArticleInterface(GenericModifyArticleRequest $request){
        Article::query()->update(['home_entry' => false]);

        $article = Article::find($request->id);
        $article->home_entry = true;
        $article->save();

        return redirect()->route('info.manage')->with('message', [
            'title' => "Success",
            'message' => 'Successfully changed home article'
        ]);
    }

    public function getCreateView(){
        return view("info::edit", [
            "id" => 0,
            "name" => "",
            "text" => ""
        ]);
    }

    public function getSaveInterface(SaveArticle $request){
        $article = Article::find($request->id);

        if ($article===null){
            $article = new Article();
        }


        $article->name = $request->name;
        $article->text = $request->text;

        $article->save();

        return redirect()->route('info.manage')->with('message', [
            'title' => "Success",
            'message' => "Successfully saved article '$article->name'"
        ]);
    }

    public function getDeleteInterface(GenericModifyArticleRequest $request){
        $article = Article::find($request->id);

        if ($article !== null) {
            Article::destroy($request->id);

            return redirect()->route('info.manage')->with('message', [
                'title' => "Success",
                'message' => "Successfully deleted article '$article->name'"
            ]);
        } else {
            return redirect()->route('info.manage')->with('message', [
                'title' => "Error",
                'message' => "Could not find the requested article! Try to reload the management page."
            ]);
        }
    }

    public function getEditView(GenericModifyArticleRequest $request){
        $article = Article::find($request->id);

        if ($article===null){
            $articles = Article::all();

            return redirect()->route('info.manage')->with('message', [
                'title' => "Error",
                'message' => 'Could not find the requested article!'
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
        $article = Article::find($id);

        if ($article===null){
            return view("info::view")->with('message', [
                'title' => "Error",
                'message' => 'Could not find the requested article!'
            ]);
        }

        return view("info::view", [
            "title" => $article->name,
            "content" => $article->text
        ]);
    }
}