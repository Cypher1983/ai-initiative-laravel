<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all chat sessions for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $sessions = Auth::user()->chatSessions()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1); // Get only the latest message for preview
            }])
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Get a specific chat session with all its messages.
     */
    public function show(ChatSession $chatSession): JsonResponse
    {
        // Ensure the user owns this chat session
        if ($chatSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chatSession->load('messages');

        return response()->json($chatSession);
    }

    /**
     * Create a new chat session.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $chatSession = ChatSession::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => true,
            'last_activity' => now(),
        ]);

        return response()->json($chatSession, 201);
    }

    /**
     * Add a message to a chat session.
     */
    public function addMessage(Request $request, ChatSession $chatSession): JsonResponse
    {
        // Ensure the user owns this chat session
        if ($chatSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'role' => 'required|in:user,assistant,system',
            'content' => 'required|string',
            'tokens_used' => 'nullable|integer',
            'model_used' => 'nullable|string',
            'response_time' => 'nullable|numeric',
            'metadata' => 'nullable|array',
        ]);

        $message = ChatMessage::create([
            'chat_session_id' => $chatSession->id,
            'role' => $request->role,
            'content' => $request->content,
            'tokens_used' => $request->tokens_used,
            'model_used' => $request->model_used,
            'response_time' => $request->response_time,
            'metadata' => $request->metadata,
        ]);

        // Update the session's last activity
        $chatSession->updateLastActivity();

        return response()->json($message, 201);
    }

    /**
     * Get the active chat session for the user.
     */
    public function getActiveSession(): JsonResponse
    {
        $activeSession = Auth::user()->activeChatSession();

        if (!$activeSession) {
            // Create a new active session if none exists
            $activeSession = ChatSession::create([
                'user_id' => Auth::id(),
                'title' => 'New Chat',
                'is_active' => true,
                'last_activity' => now(),
            ]);
        }

        $activeSession->load('messages');

        return response()->json($activeSession);
    }

    /**
     * Update a chat session (e.g., title, description).
     */
    public function update(Request $request, ChatSession $chatSession): JsonResponse
    {
        // Ensure the user owns this chat session
        if ($chatSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $chatSession->update($request->only(['title', 'description', 'is_active']));

        return response()->json($chatSession);
    }

    /**
     * Delete a chat session.
     */
    public function destroy(ChatSession $chatSession): JsonResponse
    {
        // Ensure the user owns this chat session
        if ($chatSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chatSession->delete();

        return response()->json(['message' => 'Chat session deleted successfully']);
    }
} 