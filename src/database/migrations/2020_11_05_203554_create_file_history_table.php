<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('file_id')->unsigned()->index();
            $table->tinyInteger('file_format')->comment('1: word, 2: pdf, 3: image');
            $table->string('original_name', 64)->collation('utf8mb4_unicode_ci');
            $table->string('url');
            $table->string('size', 100);
            $table->integer('version');
            $table->tinyInteger('action')->unsigned()->default(1)->comment('1: insert; 2: update; 3: delete; 4: revert');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_history');
    }
}
