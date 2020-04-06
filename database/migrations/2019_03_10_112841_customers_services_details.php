<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomersServicesDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_services_details', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('customer_id');
            $table->integer('service_id');
            $table->integer('customer_service_id');
            $table->string('reference')->nullable();
            $table->float('price_sell')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_services_details');
    }
}
