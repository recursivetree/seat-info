<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;

class HomeArticleToPins extends Migration
{
    public function up()
    {
        //step 1: add field
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("pinned")->default(false);
        });

        //migrate home article to a pinned article
        try {
            $article = RecursiveTree\Seat\InfoPlugin\Model\Article::where("home_entry",true)->first();
            $article->pinned = true;
            $article->save();
        } catch (Exception $e){
            //ignore
        }

        //delete home article property
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("home_entry");
        });
    }


    public function down()
    {
        //downgrades are rare, no need to implement pins->home article conversion
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("home_entry")->default(false);
            $table->dropColumn("pinned");
        });
    }
}

