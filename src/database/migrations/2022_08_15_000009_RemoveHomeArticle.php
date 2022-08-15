<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use RecursiveTree\Seat\InfoPlugin\Acl\RoleHelper;
use RecursiveTree\Seat\InfoPlugin\Model\AclRole;
use RecursiveTree\Seat\InfoPlugin\Model\Article;
use RecursiveTree\Seat\InfoPlugin\Model\ArticleAccessRole;
use Seat\Web\Models\Acl\Permission;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;

class RemoveHomeArticle extends Migration
{
    public function up()
    {
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("home_entry");
        });
    }


    public function down()
    {
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("home_entry")->default(false);
        });
    }
}

