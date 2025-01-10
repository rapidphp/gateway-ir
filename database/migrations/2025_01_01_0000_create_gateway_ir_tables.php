<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rapid\GatewayIR\Enums\TransactionStatuses;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create(config('gateway-ir.database.table'), function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('authority')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->enum('status', [
                TransactionStatuses::Pending,
                TransactionStatuses::Cancelled,
                TransactionStatuses::Success,
                TransactionStatuses::InternalError,
                TransactionStatuses::PendInQueue,
                TransactionStatuses::Reverted,
            ]);
            $table->text('handler');
            $table->string('gateway');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop(config('gateway-ir.database.table'));
    }

};