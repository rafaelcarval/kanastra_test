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
        Schema::create('debts', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('government_id')->unique(); 
            $table->string('email'); 
            $table->decimal('debt_amount', 15, 2); 
            $table->date('debt_due_date'); 
            $table->uuid('debt_id')->unique(); 
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
        Schema::dropIfExists('debts');
    }
};
