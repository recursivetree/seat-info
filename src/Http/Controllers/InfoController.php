<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Acl\RoleHelper;
use RecursiveTree\Seat\InfoPlugin\Model\AclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\Resource;
use RecursiveTree\Seat\InfoPlugin\Validation\ConfirmModalRequest;
use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use RecursiveTree\Seat\InfoPlugin\Validation\UploadResource;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\User;


class InfoController extends Controller
{

    public function deleteArticle(ConfirmModalRequest $request){
        Gate::authorize("info.article.edit", $request->data);

        $article = Article::find($request->data);

        if ($article !== null) {
            Article::destroy($request->data);
            AclRole::where("article", $request->data)->delete();

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

    public function setArticlePinned(Request $request){
        $request->validate([
           "data" => "required|integer"
        ]);

        $article = Article::find($request->data);

        if($article === null){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);
            return redirect()->back();
        } else {
            $article->pinned = true;
            $article->save();

            $request->session()->flash('message', [
                'message' => trans("info::info.manage_pin_article_success"),
                'type' => 'success'
            ]);

            return redirect()->back();
        }
    }

    public function setArticleUnpinned(Request $request){
        $request->validate([
            "data" => "required|integer"
        ]);

        $article = Article::find($request->data);

        if($article === null){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);
            return redirect()->back();
        } else {
            $article->pinned = false;
            $article->save();

            $request->session()->flash('message', [
                'message' => trans("info::info.manage_unpin_article_success"),
                'type' => 'success'
            ]);

            return redirect()->back();
        }
    }

    public function getCreateView(){
        $article = new Article();

        $editAclRole = new AclRole();
        $editAclRole->role =  RoleHelper::getDefaultEditRole();
        $editAclRole->allows_edit = true;

        $viewAclRole = new AclRole();
        $viewAclRole->role =  RoleHelper::getDefaultViewRole();
        $viewAclRole->allows_view = true;

        //fake the relation
        $roles = collect([$editAclRole, $viewAclRole]);

        $other_roles = Role::whereNotIn("id",$roles->pluck("role"))
            ->get()
            ->map(function ($role){
                $aclRole = new AclRole();
                $aclRole->role = $role->id;
                return $aclRole;
            });

        $roles = $roles->merge($other_roles);

        return view("info::edit", compact('article','roles'));
    }

    public function getSaveInterface(User $user,SaveArticle $request){
        $article = Article::find($request->id);

        //if the article exists, check for edit access, otherwise for article creation access
        if($request->id){
            Gate::authorize("info.article.edit", $request->id);
        } else {
            Gate::authorize("info.create_article");
        }

        if ($article===null){
            $article = new Article();
        }

        //add a user for new articles, or when saving old articles
        if ($article->owner === null){
            $article->owner = auth()->user()->id;
        }

        $article->name = $request->name;
        $article->text = $request->text;

        if(Gate::allows("info.make_public")){
            $article->public = isset($request->public);
        }

        if($article->public === null){
            $article->public = false;
        }

        $article->save();

        $article->aclRoles()->delete();

        foreach ($request->aclAccessType as $id=>$value){
            if($value === "nothing") continue;
            $aclRole = new AclRole();
            $aclRole->article = $article->id;
            if($value==="edit") {
                $aclRole->role = RoleHelper::checkForExistenceOrDefault($id, RoleHelper::getDefaultEditRole());
                $aclRole->allows_edit = true;
            }
            if($value==="view") {
                $aclRole->role = RoleHelper::checkForExistenceOrDefault($id, RoleHelper::getDefaultViewRole());
                $aclRole->allows_view = true;
            }
            $aclRole->save();
        }

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_save_article_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.view',["id"=>$article->id]);
    }

    public function getEditView(Request $request,$id){
        Gate::authorize("info.article.edit", $id);

        $article = Article::find($id);

        if ($article===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.manage_article_not_found"),
                'type' => 'error'
            ]);
            return redirect()->route('info.manage');
        }

        $roles = $article->aclRoles;

        $other_roles = Role::whereNotIn("id",$roles->pluck("role"))
            ->get()
            ->map(function ($role){
                $aclRole = new AclRole();
                $aclRole->role = $role->id;
                return $aclRole;
            });

        $roles = $roles->toBase()->merge($other_roles->toBase());

        return view("info::edit", compact('article', 'roles'));
    }

    public function getListView(){

        $articles = Article::orderBy("pinned","desc")->get()->filter(function ($article){
            return Gate::allows("info.article.view",$article->id);
        });
        return view("info::list", compact('articles'));
    }

    public function getManageView(){
        $articles = Article::all()->filter(function ($article){
            return Gate::allows("info.article.edit",$article->id);
        });

        $resources = Resource::all()->filter(function ($resource){
            return Gate::allows("info.resource.edit",$resource->id);
        });

        return view("info::manage", compact('articles','resources'));
    }

    public function getArticleView(Request $request,$id){
        Gate::authorize("info.article.view", $id);

        $article = Article::find($id);
        $can_edit = Gate::allows("info.article.edit", $article->id);

        if ($article===null){
            $request->session()->flash('message', [
                'message' => trans("info::info.view_article_not_found"),
                'type' => 'error'
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
        $resource->name = $file->getClientOriginalName();
        $resource->owner = auth()->user()->id;
        $resource->save();

        $request->session()->flash('message', [
            'message' => trans("info::info.manage_resource_upload_success"),
            'type' => 'success'
        ]);
        return redirect()->route('info.manage');
    }

    public function viewResource($id){
        Gate::authorize("info.resource.view", $id);

        $db_entry = Resource::find($id);

        if($db_entry === null){
            $db_entry = Resource::where("name",$id)->first();
        }

        if ($db_entry===null){
            return abort(404);
        }

        if(!Storage::exists($db_entry->path)){
            abort(500);
        }

        $size = Storage::size($db_entry->path);
        $type = $db_entry->mime;

        return Storage::download($db_entry->path, $db_entry->name, [
            'Content-Type'=> $type,
            'Accept-Ranges'=>'bytes',
            'Content-Length',strlen($size)
        ]);
    }

    public function deleteResource(ConfirmModalRequest $request){
        Gate::authorize("info.resource.edit", $request->data);

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