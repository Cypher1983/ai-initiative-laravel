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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable(); // Optional title for the chat session
            $table->text('description')->nullable(); // Optional description
            $table->boolean('is_active')->default(true); // Whether the session is still active
            $table->timestamp('last_activity')->nullable(); // Track when the session was last used
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
