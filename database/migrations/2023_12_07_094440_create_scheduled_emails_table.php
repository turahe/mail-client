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
        Schema::create('scheduled_emails', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id')->nullable()->constrained('email_accounts');
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'related_message_id')->nullable()->constrained('email_account_messages');  // for reply and forward
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->ulid('email_account_id')->nullable();
                $table->foreign('email_account_id')->references('id')->on('email_accounts');
                $table->ulid('related_message_id')->nullable();
                $table->foreign('related_message_id')->references('id')->on('email_account_messages');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id')->nullable()->constrained('email_accounts');
                $table->foreignUuidFor(\Turahe\MailClient\Models\EmailAccountMessage::class, 'related_message_id')->nullable()->constrained('email_account_messages');  // for reply and forward
            }
            $table->string('subject');
            $table->mediumText('html_body');
            $table->text('to');
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('type');
            $table->text('associations')->nullable();
            $table->string('status')->index();
            $table->dateTime('failed_at')->index()->nullable(); // the final fail date
            $table->text('fail_reason')->nullable();
            $table->dateTime('retry_after')->index()->nullable();
            $table->unsignedInteger('retries')->index()->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('scheduled_at')->index();

            // Create userstamp columns with correct data types
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->unsignedBigInteger('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('created_by')->nullable()->index();
                $table->ulid('updated_by')->nullable()->index();
                $table->ulid('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('updated_by')->nullable()->index();
                $table->uuid('deleted_by')->nullable()->index();
            }

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints for userstamps
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }

            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
