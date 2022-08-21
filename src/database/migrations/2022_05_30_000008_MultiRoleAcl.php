<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Acl\RoleHelper;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;
use Seat\Web\Models\Acl\Permission;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;

class MultiRoleAcl extends Migration
{
    public function up()
    {

        Schema::create('recursive_tree_seat_info_articles_acl_roles', function (Blueprint $table) {
            $table->bigInteger("article");
            $table->bigInteger("role");
            $table->boolean("allows_edit")->default(false);
            $table->boolean("allows_view")->default(false);
        });

        $articles = Article::all();
        foreach ($articles as $article){
            if($article->view_role !== null){
                $role = new ArticleAclRole();
                $role->article = $article->id;
                $role->allows_view = true;
                $role->role = $article->view_role;
                $role->save();
            }
            if($article->edit_role !== null){
                $role = new ArticleAclRole();
                $role->article = $article->id;
                $role->allows_edit = true;
                $role->role = $article->edit_role;
                $role->save();
            }
        }

        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("view_role");
            $table->dropColumn("edit_role");
        });
    }


    public function down()
    {

        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->bigInteger("view_role")->nullable();
            $table->bigInteger("edit_role")->nullable();
        });

        Schema::dropIfExists('recursive_tree_seat_info_articles_acl_roles');
    }
}

