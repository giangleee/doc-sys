<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('branch_id')->nullable()->unsigned()->index();
            $table->integer('division_id')->nullable()->unsigned()->index();
            $table->integer('office_id')->nullable()->unsigned()->index();
            $table->integer('owner_id')->unsigned()->index();
            $table->integer('parent_id')->nullable()->unsigned()->index();
            $table->string('name', 64);
            $table->tinyInteger('is_system')->default(0)->comment('0: normal; 1: system');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders');
    }
}
