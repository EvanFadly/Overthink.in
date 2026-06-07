<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `shared_results` table which stores AI-generated stress
     * analysis results that users wish to share publicly. The UUID column
     * is used for public-facing share URLs so that internal auto-increment
     * IDs are never exposed.
     */
    public function up(): void
    {
        Schema::create('shared_results', function (Blueprint $table) {
            // Internal primary key — never exposed publicly.
            $table->id();

            // Public-facing identifier for shareable URLs.
            // Uses a secure, non-sequential UUID string.
            $table->string('uuid')->unique();
            $table->index('uuid');

            // Anonymous session identifier — nullable because the user
            // may choose not to pass a session token at share time.
            $table->string('session_id')->nullable();

            // AI-generated result content.
            $table->string('result_title');
            $table->text('result_text');

            // Calculated stress score from 0–100.
            $table->integer('stress_score');

            // Flexible JSON bag for extra AI-generated metrics, e.g.
            // mental_battery, delusion_level, recommended_action.
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_results');
    }
};
