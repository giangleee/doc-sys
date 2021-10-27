<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFileSetManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_set_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('service_user_id');
            $table->integer('office_id');
            $table->integer('document_type_id');
            $table->tinyInteger('status_contract')->default(1)->comment('1: ACTIVE, 0: DISABLE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_set_management');
    }
}
