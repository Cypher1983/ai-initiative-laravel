<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'metadata',
        'tokens_used',
        'model_used',
        'response_time',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_time' => 'decimal:3',
    ];

    /**
     * Get the chat session that owns the message.
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    /**
     * Get the user who owns this message through the chat session.
     */
    public function user(): BelongsTo
    {
        return $this->chatSession->user();
    }
} 