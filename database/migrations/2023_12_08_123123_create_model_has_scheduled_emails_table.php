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
        Schema::create('model_has_scheduled_emails', function (Blueprint $table) {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreignIdFor(\Turahe\MailClient\Models\ScheduledEmail::class, 'scheduled_email_id')->constrained('scheduled_emails')->cascadeOnDelete();
                $table->morphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('scheduled_email_id');
                $table->foreign('scheduled_email_id')->references('id')->on('scheduled_emails')->cascadeOnDelete();
                $table->ulidMorphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreignUuidFor(\Turahe\MailClient\Models\ScheduledEmail::class, 'scheduled_email_id')->constrained('scheduled_emails')->cascadeOnDelete();
                $table->uuidMorphs('model');
            }

            $table->primary(['scheduled_email_id', 'model_id',  'model_type'], 'model_has_scheduled_emails_email_model_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_scheduled_emails');
    }
};
