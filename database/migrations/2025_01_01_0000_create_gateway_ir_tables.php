<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rapid\GatewayIR\Enums\TransactionStatuses;

return new class extends Migration
{

    public function up()
    {
        Schema::create(config('gateway-ir.table.table'), function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('authority')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->enum('status', [
                TransactionStatuses::Pending,
                TransactionStatuses::Cancelled,
                TransactionStatuses::Paid,
            ]);
            $table->text('handler');
            $table->string('gateway');
            $table->nullableMorphs('user');
            $table->nullableMorphs('model');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop(config('gateway-ir.table.table'));
    }

};