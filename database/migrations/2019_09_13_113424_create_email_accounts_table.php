<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Turahe\MailClient\Enums\SyncState;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->nullableMorphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->nullableUlidMorphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->nullableUuidMorphs('model');
            }
            $table->string('email')->unique();
            $table->string('alias_email')->nullable();
            $table->enum('connection_type', array_column(\Turahe\MailClient\Enums\ConnectionType::cases(), 'value'))->nullable();
            $table->unsignedBigInteger('access_token_id')->nullable();
            $table->unsignedBigInteger('sent_folder_id')->nullable();
            $table->unsignedBigInteger('trash_folder_id')->nullable();
            $table->boolean('create_contact')
                ->default(false)
                ->comment('Whether to create contact if the message sender does not exists.');
            $table->dateTime('initial_sync_from')->nullable();
            $table->dateTime('last_sync_at')->nullable();
            $table->string('sync_state', 30)->default(SyncState::Enabled->value);
            $table->text('sync_state_comment')->nullable();
            $table->boolean('requires_auth')->default(false);

            // IMAP
            $table->text('password')->nullable()->comment('IMAP');
            $table->boolean('validate_cert')->nullable()->comment('IMAP');
            $table->string('username')->nullable()->comment('IMAP');
            $table->string('imap_server')->nullable()->comment('IMAP');
            $table->unsignedInteger('imap_port')->nullable()->comment('IMAP');
            $table->string('imap_encryption', 8)->nullable()->comment('IMAP');
            $table->string('smtp_server')->nullable()->comment('IMAP');
            $table->unsignedInteger('smtp_port')->nullable()->comment('IMAP');
            $table->string('smtp_encryption', 8)->nullable()->comment('IMAP');

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
        Schema::dropIfExists('email_accounts');
    }
};
