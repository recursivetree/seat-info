<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recursive_tree_seat_info_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('image_data');
            $table->text('mime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recursive_tree_seat_info_resources');
    }
}

