<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeColoumnsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable();
            $table->string('subscription_id')->nullable();
            $table->string('charges_amount')->nullable();
            $table->string('plan_price')->nullable();
            $table->string('subscription')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id','subscription_id', 'charges_amount','plan_price','subscription']);
        });
    }
}
