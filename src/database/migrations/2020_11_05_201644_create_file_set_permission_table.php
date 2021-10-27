<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileSetPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_set_permission', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('service_user_id')->unsigned()->index();
            $table->integer('office_id')->unsigned()->index();
            $table->string('positions_id', 100);
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
        Schema::dropIfExists('file_set_permission');
    }
}
