<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHomeEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->boolean("home_entry")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recursive_tree_seat_info_articles', function (Blueprint $table) {
            $table->dropColumn("home_entry");
        });
    }
}

