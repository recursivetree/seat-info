<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Acl\RoleHelper;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;
use Seat\Web\Models\Acl\Permission;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;

class PerArticleAcl extends Migration
{
    public function up()
    {

        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->bigInteger("view_role")->nullable();
            $table->bigInteger("edit_role")->nullable();
        });


        $default_view_role = RoleHelper::getDefaultViewRole();
        $default_edit_role = RoleHelper::getDefaultEditRole();

        $articles = Article::all();
        foreach ($articles as $article){
            $article->view_role = $default_view_role;
            $article->edit_role = $default_edit_role;
            $article->save();
        }

        $view_permission = Permission::where("title","info.view_article")->first();
        if($view_permission){
            $user_ids = $view_permission->roles->flatMap(function ($role){
                return $role->users->pluck("id");
            })->unique();

            Role::find($default_view_role)->users()->sync($user_ids);
        }

        $edit_permission = Permission::where("title","info.edit_article")->first();
        if($edit_permission){
            $user_ids = $edit_permission->roles->flatMap(function ($role){
                return $role->users->pluck("id");
            })->unique();

            Role::find($default_edit_role)->users()->sync($user_ids);
        }
    }


    public function down()
    {
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("view_role");
            $table->dropColumn("edit_role");
        });
    }
}

