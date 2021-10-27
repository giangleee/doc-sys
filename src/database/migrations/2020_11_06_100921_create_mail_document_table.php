<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_document', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('document_id')->unsigned()->index();
            $table->integer('mail_template_id')->unsigned()->index();
            $table->text('to')->nullable();
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->tinyInteger('type')->unsigned()->default(1)->comment('1: auto; 2: manual');
            $table->tinyInteger('is_repeated')->unsigned()->default(0)->comment('0: once; 1: repeated');
            $table->tinyInteger('repeat_unit')->nullable()->unsigned()->comment('1: week; 2: month; 3: year');
            $table->integer('repeat_value')->nullable()->unsigned();
            $table->timestamp('send_at')->nullable();
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
        Schema::dropIfExists('mail_document');
    }
}
