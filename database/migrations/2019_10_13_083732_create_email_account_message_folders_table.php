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
        Schema::create('email_account_message_folders', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages')->cascadeOnDelete();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountFolder::class, 'folder_id')->constrained('email_account_folders')->cascadeOnDelete();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->ulid('message_id');
                $table->foreign('message_id')->references('id')->on('email_account_messages')->cascadeOnDelete();
                $table->ulid('folder_id');
                $table->foreign('folder_id')->references('id')->on('email_account_folders')->cascadeOnDelete();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccountFolder::class, 'folder_id')->constrained('email_account_folders')->cascadeOnDelete();
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages')->cascadeOnDelete();
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_message_folders');
    }
};
