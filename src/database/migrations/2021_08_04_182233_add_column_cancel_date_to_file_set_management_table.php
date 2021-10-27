<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCancelDateToFileSetManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_set_management', function (Blueprint $table) {
            $table->timestamp('contract_cancel_date')->after('status_contract')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_set_management', function (Blueprint $table) {
            $table->dropColumn('contract_cancel_date');
        });
    }
}
