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
        Schema::create('email_account_messageables', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('message_id');
                $table->foreign('message_id')->references('id')->on('email_account_messages');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'message_id')->constrained('email_account_messages');
            }
            $table->morphs('messageable', 'email_account_messageables_index');
            $table->primary(
                ['message_id', 'messageable_id', 'messageable_type'],
                'messageable_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_messageables');
    }
};
