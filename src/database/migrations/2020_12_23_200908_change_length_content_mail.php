<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLengthContentMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->dropColumn('body');
        });

        Schema::table('mail_templates', function (Blueprint $table) {
            $table->longText('body')->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->dropColumn('body');
        });

        Schema::table('mail_templates', function (Blueprint $table) {
            $table->text('body')->after('subject');
        });
    }
}
