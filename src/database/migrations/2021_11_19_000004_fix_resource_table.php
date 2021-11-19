<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recursive_tree_seat_info_resources', function (Blueprint $table) {
            $table->dropColumn('image_data');

            $table->text("path");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recursive_tree_seat_info_resources', function (Blueprint $table) {
            $table->binary('image_data');
            $table->dropColumn('path');
        });
    }
}

