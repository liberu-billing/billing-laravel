

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hosting_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname');
            $table->enum('control_panel', ['cpanel', 'plesk', 'directadmin', 'virtualmin']);
            $table->string('api_token');
            $table->string('api_url');
            $table->boolean('is_active')->default(true);
            $table->integer('max_accounts')->default(0);
            $table->integer('active_accounts')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hosting_servers');
    }
};