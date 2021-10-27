<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToServiceUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_users', function (Blueprint $table) {
            $table->integer('office_id')->after('id')->unsigned()->index();
            $table->bigInteger('user_created')->after('office_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_users', function (Blueprint $table) {
            $table->dropColumn(['office_id', 'user_created']);
        });
    }
}
