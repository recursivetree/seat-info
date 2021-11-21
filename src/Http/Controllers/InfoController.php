<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use RecursiveTree\Seat\InfoPlugin\Validation\GenericRequestWithID;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use RecursiveTree\Seat\InfoPlugin\Validation\UploadResource;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class InfoController extends Controller
{
    public function getHomeView(){

        $article = Article::where("home_entry",true)->first();

        if ($article===null){
            return redirect()->route('info.list')->with('message', [
                'title' => "Error",
                'message' => 'There is no start article configured, forwarding to the article list. Please contact the administrator about this.'
            ]);
        }


        return view("info::view", [
            "title" => $article->name,
            "content" => $article->text
        ]);

    }

    public function getSetHomeArticleInterface(GenericRequestWithID $request){
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

    public function getDeleteInterface(GenericRequestWithID $request){
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

    public function getEditView(GenericRequestWithID $request){
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
        $resources = Resource::all();
        return view("info::manage", compact('articles','resources'));
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

    public function uploadResource(UploadResource $request){
        $file = $request->file;
        $mime_type = $file->getMimeType();

        $path = $file->store('recursive_tree_info_module_resources');

        $resource = new Resource();
        $resource->mime = $mime_type;
        $resource->path = $path;
        $resource->save();

        return redirect()->route('info.manage')->with('message', [
            'title' => "Success",
            'message' => 'Successfully uploaded file!'
        ]);
    }

    public function viewResource($id){
        $db_entry = Resource::find($id);

        if ($db_entry==null){
            return abort(404);
        }

        $content = Storage::get($db_entry->path);
        $type = $db_entry->mime;

        return response($content)->header('Content-Type', $type);
    }

    public function deleteResource(GenericRequestWithID $request){
        $resource = Resource::find($request->id);
        if ($resource===null){
            return redirect()->route('info.manage')->with('message', [
                'title' => "Error",
                'message' => 'Could not find requested resource!'
            ]);
        }

        Storage::delete($resource->path);
        Resource::destroy($request->id);

        return redirect()->route('info.manage')->with('message', [
            'title' => "Success",
            'message' => 'Successfully deleted file!'
        ]);
    }
}