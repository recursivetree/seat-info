<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;

class UpdateOnePointSix extends Migration
{
    public function up()
    {
        //home article to pin migration
        //step 1: add field
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("pinned")->default(false);
        });
        //migrate home article to a pinned article
        try {
            $article = RecursiveTree\Seat\InfoPlugin\Model\Article::where("home_entry",true)->first();
            if($article) {
                $article->pinned = true;
                $article->save();
            }
        } catch (Exception $e){
            //ignore
        }
        //delete home article property
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("home_entry");
        });

        //article owner migration
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->bigInteger("owner")->unsigned()->nullable();
        });

        //resource owner migration
        Schema::table('recursive_tree_seat_info_resources', function (Blueprint $table) {
            $table->bigInteger("owner")->unsigned()->nullable();
        });

        //resource acl migration
        Schema::create('recursive_tree_seat_info_resources_acl_roles', function (Blueprint $table) {
            $table->bigInteger("resource")->index();
            $table->bigInteger("role");
            $table->boolean("allows_edit")->default(false);
            $table->boolean("allows_view")->default(false);
        });
    }


    public function down()
    {
        //downgrades are rare, no need to implement pins->home article conversion
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("home_entry")->default(false);
            $table->dropColumn("pinned");
            $table->dropColumn("owner");
        });

        Schema::table('recursive_tree_seat_info_resources', function (Blueprint $table) {
            $table->dropColumn("owner");
        });

        Schema::drop('recursive_tree_seat_info_resources_acl_roles');
    }
}

