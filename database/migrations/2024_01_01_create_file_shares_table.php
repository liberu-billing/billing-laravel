<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->integer('file_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('permission')->nullable()->comment('read, write, delete');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_shares');
    }
};