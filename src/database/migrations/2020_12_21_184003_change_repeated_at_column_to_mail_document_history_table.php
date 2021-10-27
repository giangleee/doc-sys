<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRepeatedAtColumnToMailDocumentHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_document_history', function (Blueprint $table) {
            $table->renameColumn('repeated_at', 'last_send_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_document_history', function (Blueprint $table) {
            $table->renameColumn('last_send_at', 'repeated_at');
        });
    }
}
