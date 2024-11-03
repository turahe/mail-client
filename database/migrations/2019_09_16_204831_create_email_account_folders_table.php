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
        Schema::create('email_account_folders', function (Blueprint $table) {
            $table->ulid('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreignId('email_account_id')->constrained('email_accounts');
            $table->string('remote_id')->nullable()
                ->comment('API ID, uidvalidity etc...');
            $table->boolean('support_move')->default(true);
            $table->boolean('syncable')->index()->default(false);
            $table->boolean('selectable')->default(false);
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('display_name');

            $table->userstamps();
            $table->softUserstamps();

            $table->integer('deleted_at')->index()->nullable();
            $table->integer('created_at')->index()->nullable();
            $table->integer('updated_at')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('email_account_folders');
    }
};
