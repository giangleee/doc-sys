<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('office_id')->nullable()->change();
            $table->integer('branch_id')->nullable()->unsigned()->index()->after('role_id');
            $table->integer('division_id')->nullable()->unsigned()->index()->after('branch_id');
            $table->integer('store_id')->nullable()->unsigned()->index()->after('office_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('office_id')->nullable(false)->change();
            $table->dropColumn('branch_id');
            $table->dropColumn('division_id');
            $table->dropColumn('store_id');
        });
    }
}
