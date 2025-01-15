<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('team_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->integer('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};