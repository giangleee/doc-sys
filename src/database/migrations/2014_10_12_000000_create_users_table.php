<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('role_id')->unsigned()->index();
            $table->integer('office_id')->unsigned()->index();
            $table->integer('position_id')->unsigned()->index()->nullable();
            $table->string('employee_id', 20)->unique()->index();
            $table->string('name', 64)->collation('utf8mb4_unicode_ci');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->tinyInteger('is_first_login')->default(1)->comment('0: is not first login, 1: is first login');
            $table->tinyInteger('status')->default(1)->comment('0: inactive, 1: active');
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
        Schema::dropIfExists('users');
    }
}
