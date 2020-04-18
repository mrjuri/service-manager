<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomersServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_services', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('customer_id');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->timestamp('expiration');

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
        Schema::dropIfExists('customers_services');
    }
}
