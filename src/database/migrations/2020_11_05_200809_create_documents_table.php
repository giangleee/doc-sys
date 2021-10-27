<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('office_id')->unsigned()->index();
            $table->integer('owner_id')->unsigned()->index();
            $table->integer('folder_id')->nullable()->unsigned()->index();
            $table->integer('document_type_id')->unsigned()->index();
            $table->integer('service_user_id')->unsigned()->index()->nullable();
            $table->string('partner_name', 50)->nullable();
            $table->string('name', 50);
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
        Schema::dropIfExists('documents');
    }
}
