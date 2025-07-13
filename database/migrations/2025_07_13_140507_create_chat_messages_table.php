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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->longText('content'); // The actual message content
            $table->json('metadata')->nullable(); // Store additional data like tokens used, model used, etc.
            $table->integer('tokens_used')->nullable(); // Track token usage
            $table->string('model_used')->nullable(); // Which AI model was used
            $table->decimal('response_time', 8, 3)->nullable(); // Response time in seconds
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['chat_session_id', 'created_at']);
            $table->index(['role', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
