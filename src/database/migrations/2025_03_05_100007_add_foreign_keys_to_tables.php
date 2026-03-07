<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('set null');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('breaks', function (Blueprint $table) {
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
        });

        Schema::table('correction_applications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('correction_status_id')->references('id')->on('correction_statuses')->onDelete('cascade');
            $table->foreign('approved_admin_id')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::table('correction_breaks', function (Blueprint $table) {
            $table->foreign('correction_application_id')->references('id')->on('correction_applications')->onDelete('cascade');
            $table->foreign('break_id')->references('id')->on('breaks')->onDelete('cascade');
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
            $table->dropForeign(['status_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('breaks', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
        });

        Schema::table('correction_applications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['attendance_id']);
            $table->dropForeign(['correction_status_id']);
            $table->dropForeign(['approved_admin_id']);
        });

        Schema::table('correction_breaks', function (Blueprint $table) {
            $table->dropForeign(['correction_application_id']);
            $table->dropForeign(['break_id']);
        });
    }
}
