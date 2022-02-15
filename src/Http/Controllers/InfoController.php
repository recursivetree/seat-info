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
        $can_edit = auth()->user()->can("info.edit_article");

        if ($article===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.home_no_article"),
                'type' => 'warning'
            ]);
            return redirect()->route('info.list');
        }

        if(!$article->public && !$can_edit){
            $request->session()->flash('message', [
                'message' => trans("info::info.home_insufficient_permissions"),
                'type' => 'warning'
            ]);
            return redirect()->route('info.list');
        }

        return view("info::view", compact('can_edit','article'));

    }

    public function setHomeArticle(ConfirmModalRequest $request){
        Article::query()->update(['home_entry' => false]);

        $article = Article::find($request->data);

        if($article == null){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);
            return redirect()->route('info.manage');
        }

        if(!$article->public){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_only_public_home_article_error"),
                'type' => 'error'
            ]);
            return redirect()->route('info.manage');
        }

        $article->home_entry = true;
        $article->save();

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_set_home_article_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function unsetHomeArticle(ConfirmModalRequest $request){
        Article::query()->update(['home_entry' => false]);

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_unset_home_article_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function deleteArticle(ConfirmModalRequest $request){
        $article = Article::find($request->data);

        if ($article !== null) {
            Article::destroy($request->data);

            $request->session()->flash('message', [
                'message' => trans("info::info.manage_delete_article_success"),
                'type' => 'success'
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
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
                'message' => trans("info::info.manage_publish_article_success"),
                'type' => 'success'
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
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
                'message' => trans("info::info.manage_set_article_private_success"),
                'type' => 'success'
            ]);

            return redirect()->route('info.manage');
        } else {
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);

            return redirect()->route('info.manage');
        }
    }

    public function getCreateView(){
        $article = new Article();
        return view("info::edit", compact('article'));
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
            'message' => trans("info::info.manage_save_article_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function getEditView(Request $request,$id){
        $article = Article::find($id);

        if ($article===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);
            return redirect()->route('info.manage');
        }

        return view("info::edit", compact('article'));
    }

    public function getListView(){

        $articles = Article::all();
        $can_edit = auth()->user()->can("info.edit_article");
        return view("info::list", compact('articles','can_edit'));
    }

    public function getManageView(){
        $articles = Article::all();
        $resources = Resource::all();
        $noHomeArticle = !Article::where("home_entry",true)->exists();

        return view("info::manage", compact('articles','resources','noHomeArticle'));
    }

    public function getArticleView(Request $request,$id){
        $article = Article::find($id);
        $can_edit = auth()->user()->can("info.edit_article");

        if ($article===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.view_article_not_found"),
                'type' => 'error'
            ]);
            return view("info::view");
        }

        if(!$article->public && !$can_edit){
            $request->session()->flash('message', [
                'message' => trans("info::info.view_article_insufficient_permissions"),
                'type' => 'warning'
            ]);
            return view("info::view");
        }

        return view("info::view", compact('can_edit','article'));
    }

    public function uploadResource(UploadResource $request){
        $file = $request->file;

        if($request->mime_src_client){
            $mime_type = $file->getClientMimeType();
        } else {
            $mime_type = $file->getMimeType();
        }

        $path = $file->store('recursive_tree_info_module_resources');

        $resource = new Resource();
        $resource->mime = $mime_type;
        $resource->path = $path;
        $resource->name = $file->getClientOriginalName();;
        $resource->save();

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_resource_upload_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function viewResource($id){
        $db_entry = Resource::find($id);

        if ($db_entry==null){
            return abort(404);
        }

        if(!Storage::exists($db_entry->path)){
            abort(500);
        }

        $content = Storage::get($db_entry->path);
        $type = $db_entry->mime;

        return response($content)->header('Content-Type', $type);
    }

    public function deleteResource(ConfirmModalRequest $request){
        $resource = Resource::find($request->data);
        if ($resource===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.resource_not_found"),
                'type' => 'error'
            ]);
            return redirect()->route('info.manage');
        }

        Storage::delete($resource->path);
        Resource::destroy($request->data);

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_resource_delete_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function about(){
        return view("info::about");
    }
}