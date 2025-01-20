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
        Schema::create('predefined_mail_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_shared')->default(true);
            $table->foreignId('user_id')->nullable()->comment('Owner')->constrained('users');

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
        Schema::dropIfExists('predefined_mail_templates');
    }
};
