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
        Schema::create('email_account_message_headers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('value')->nullable();
            $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages')->cascadeOnDelete();
            $table->string('header_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_message_headers');
    }
};
