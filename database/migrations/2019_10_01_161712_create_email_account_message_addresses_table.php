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
        Schema::create('email_account_message_addresses', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages')->cascadeOnDelete();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->ulid('message_id');
                $table->foreign('message_id')->references('id')->on('email_account_messages')->cascadeOnDelete();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages')->cascadeOnDelete();
            }
            $table->string('address')->index()->nullable(); // For drafts without address
            $table->string('name')->index()->nullable();
            $table->string('address_type')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_message_addresses');
    }
};
