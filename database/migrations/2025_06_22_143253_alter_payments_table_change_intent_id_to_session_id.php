<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn('stripe_payment_intent_id');
        $table->string('stripe_session_id')->after('user_id');
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn('stripe_session_id');
        $table->string('stripe_payment_intent_id')->nullable(false);
    });
}

};
