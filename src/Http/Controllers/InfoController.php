<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use RecursiveTree\Seat\InfoPlugin\Validation\ConfirmModalRequest;
use RecursiveTree\Seat\InfoPlugin\Validation\GenericRequestWithID;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use RecursiveTree\Seat\InfoPlugin\Validation\UploadResource;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class InfoController extends Controller
{
    public function getHomeView(Request $request){

        $article = Article::where("home_entry",true)->first();

        if ($article===null){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'There is no start article configured, forwarding to the article list. Please contact the administrator about this.'
            ]);
            return redirect()->route('info.list');
        }

        if(!$article->public){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'You are not allowed to see this article, forwarding to the article list. Please contact the administrator about this.'
            ]);
            return redirect()->route('info.list');
        }


        return view("info::view", [
            "title" => $article->name,
            "content" => $article->text
        ]);

    }

    public function setHomeArticle(ConfirmModalRequest $request){
        Article::query()->update(['home_entry' => false]);

        $article = Article::find($request->data);

        if($article == null){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'Could not find home article home article'
            ]);
            return redirect()->route('info.manage');
        }

        if(!$article->public){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'You can only set public articles as your home article!'
            ]);
            return redirect()->route('info.manage');
        }

        $article->home_entry = true;
        $article->save();

        $request->session()->flash('message', [
            'title' => "Success",
            'message' => 'Successfully changed home article'
        ]);
        return redirect()->route('info.manage');
    }

    public function unsetHomeArticle(ConfirmModalRequest $request){
        Article::query()->update(['home_entry' => false]);

        $request->session()->flash('message', [
            'title' => "Success",
            'message' => 'Successfully removed home article'
        ]);
        return redirect()->route('info.manage');
    }

    public function deleteArticle(ConfirmModalRequest $request){
        $article = Article::find($request->data);

        if ($article !== null) {
            Article::destroy($request->data);

            $request->session()->flash('message', [
                'title' => "Success",
                'message' => "Successfully deleted article '$article->name'"
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => "Could not find the requested article! Try to reload the management page."
            ]);

            return redirect()->route('info.manage');
        }
    }

    public function setArticlePublic(ConfirmModalRequest $request){
        $article = Article::find($request->data);

        if ($article !== null) {
            $article->public = true;
            $article->save();

            $request->session()->flash('message', [
                'title' => "Success",
                'message' => "Successfully published article '$article->name'"
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => "Could not find the requested article! Try to reload the management page."
            ]);

            return redirect()->route('info.manage');
        }
    }

    public function setArticlePrivate(ConfirmModalRequest $request){
        $article = Article::find($request->data);

        if ($article !== null) {

            if($article->home_entry){
                $article->home_entry = false;
            }

            $article->public = false;
            $article->save();

            $request->session()->flash('message', [
                'title' => "Success",
                'message' => "Successfully made article private article '$article->name'"
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => "Could not find the requested article! Try to reload the management page."
            ]);

            return redirect()->route('info.manage');
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
        $article = Article::find($request->id);

        if ($article===null){
            $article = new Article();
        }


        $article->name = $request->name;
        $article->text = $request->text;
        $article->public = isset($request->public);

        $article->save();

        $request->session()->flash('message', [
            'title' => "Success",
            'message' => "Successfully saved article '$article->name'"
        ]);
        return redirect()->route('info.manage');
    }

    public function getEditView(Request $request,$id){
        $article = Article::find($id);

        if ($article===null){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'Could not find the requested article!'
            ]);
            return redirect()->route('info.manage');
        }

        return view("info::edit", [
            "id" => $article->id,
            "name" => $article->name,
            "text" => $article->text
        ]);
    }

    public function getListView(){
        if (auth()->user()->can("info.edit_article")){
            $articles = Article::all();
        } else {
            $articles = Article::where("public", true)->get();
        }
        return view("info::list", compact('articles'));
    }

    public function getManageView(){
        $articles = Article::all();
        $resources = Resource::all();
        $noHomeArticle = !Article::where("home_entry",true)->exists();
        return view("info::manage", compact('articles','resources','noHomeArticle'));
    }

    public function getArticleView(Request $request,$id){
        $article = Article::find($id);

        if ($article===null){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'Could not find the requested article!'
            ]);
            return view("info::view");
        }

        if(!$article->public && !auth()->user()->can("info.edit_article")){
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'This article is private!'
            ]);
            return view("info::view");
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
        $resource->name = $file->getClientOriginalName();;
        $resource->save();

        $request->session()->flash('message', [
            'title' => "Success",
            'message' => 'Successfully uploaded file!'
        ]);
        return redirect()->route('info.manage');
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
            $request->session()->flash('message', [
                'title' => "Error",
                'message' => 'Could not find requested resource!'
            ]);
            return redirect()->route('info.manage');
        }

        Storage::delete($resource->path);
        Resource::destroy($request->id);

        $request->session()->flash('message', [
            'title' => "Success",
            'message' => 'Successfully deleted file!'
        ]);
        return redirect()->route('info.manage');
    }
}