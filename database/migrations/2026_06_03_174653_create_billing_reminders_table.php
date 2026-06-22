<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->date('reminder_date');
            $table->unsignedTinyInteger('days_before_due');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['invoice_id', 'channel', 'reminder_date', 'days_before_due'], 'billing_reminders_unique_send');
            $table->index(['reminder_date', 'days_before_due']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_reminders');
    }
};
