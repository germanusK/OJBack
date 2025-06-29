<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->integer('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('used')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_tracks');
    }
};
