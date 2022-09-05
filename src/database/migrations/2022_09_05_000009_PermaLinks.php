<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;

class PermaLinks extends Migration
{
    public function up()
    {
        //permalinks
        Schema::create('recursive_tree_seat_info_permalinks', function (Blueprint $table) {
            $table->string("permalink")->primary();
            $table->bigInteger("article")->index();
        });
    }


    public function down()
    {
        Schema::drop('recursive_tree_seat_info_permalinks');
    }
}

