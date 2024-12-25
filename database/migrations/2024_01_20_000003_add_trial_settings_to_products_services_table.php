

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products_services', function (Blueprint $table) {
            $table->foreignId('product_type_id')->constrained();
            $table->foreignId('hosting_server_id')->nullable()->constrained();
            $table->integer('trial_days')->default(0);
            $table->boolean('trial_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('products_services', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['hosting_server_id']);
            $table->dropColumn(['product_type_id', 'hosting_server_id', 'trial_days', 'trial_enabled']);
        });
    }
};