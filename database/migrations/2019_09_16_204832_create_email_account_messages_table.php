<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_account_messages', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id')->constrained('email_accounts');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id')->constrained('email_accounts');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->foreignIdFor(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id')->constrained('email_accounts');
            }

            $table->string('remote_id')->index()->comment('Remote Message Identifier (uuid, id)');

            // https://stackoverflow.com/questions/30079128/maximum-internet-email-message-id-length
            if (DB::getDriverName() !== 'sqlite') {
                $table->string('message_id', 995)->fullText()->nullable()->comment('Internet Message ID');
            } else {
                $table->string('message_id', 995)->nullable()->comment('Internet Message ID');
            }

            $table->char('hash', 32)->index()->nullable();
            $table->string('subject')->index()->nullable();
            $table->mediumText('html_body')->nullable();
            $table->mediumText('text_body')->nullable();
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_read')->index()->default(true);
            $table->boolean('is_sent_via_app')->default(true);
            $table->integer('opens')->nullable();
            $table->datetime('opened_at')->nullable();
            $table->integer('clicks')->nullable();
            $table->datetime('clicked_at')->nullable();
            $table->dateTime('date');

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
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_messages');
    }
};
