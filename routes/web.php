<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Chat routes
    Route::prefix('api/chat')->group(function () {
        Route::get('/sessions', [ChatController::class, 'index'])->name('chat.sessions.index');
        Route::get('/sessions/active', [ChatController::class, 'getActiveSession'])->name('chat.sessions.active');
        Route::post('/sessions', [ChatController::class, 'store'])->name('chat.sessions.store');
        Route::get('/sessions/{chatSession}', [ChatController::class, 'show'])->name('chat.sessions.show');
        Route::put('/sessions/{chatSession}', [ChatController::class, 'update'])->name('chat.sessions.update');
        Route::delete('/sessions/{chatSession}', [ChatController::class, 'destroy'])->name('chat.sessions.destroy');
        Route::post('/sessions/{chatSession}/messages', [ChatController::class, 'addMessage'])->name('chat.messages.store');
    });
});

Route::post('/api/llm/message', function (Request $request) {
    $request->validate([
        'prompt' => 'required|string',
    ]);

    try {
        // For testing purposes, simulate multiple options for specific prompts
        $prompt = $request->input('prompt');
        
        // Test mode: If the prompt contains "test options", return multiple options
        if (str_contains(strtolower($prompt), 'test options')) {
            return response()->json([
                'options' => [
                    "Here's the first option for your question. This approach focuses on simplicity and ease of implementation.",
                    "Here's an alternative approach that prioritizes performance and scalability. This method might be more complex but offers better long-term benefits."
                ],
                'context_text' => 'Test context for multiple options',
                'debug_info' => 'Simulated multiple options response'
            ]);
        }

        // Call the FastAPI backend (or LM Studio directly if needed)
        $ragResponse = Http::timeout(3600)->post('http://127.0.0.1:8001/llm/query', [
            'prompt' => $request->input('prompt'),
        ]);

        if ($ragResponse->failed()) {
            return response()->json(['error' => 'RAG backend failed'], 502);
        }

        $data = $ragResponse->json();
        $reply = $data['content'] ?? 'No response content found.';

        // Check if the backend returned multiple options
        if (isset($data['options']) && is_array($data['options']) && count($data['options']) > 1) {
            return response()->json([
                'options' => $data['options'],
                'context_text' => $data['context_text'] ?? 'No context text found.',
                'debug_info' => $data['debug_info'] ?? 'No debug info found.'
            ]);
        } else {
            return response()->json([
                'response' => $reply,
                'options' => $data['options'],
                'context_text' => $data['context_text'] ?? 'No context text found.',
                'debug_info' => $data['debug_info'] ?? 'No debug info found.'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'LLM request error: ' . $e->getMessage()], 500);
    }
});

require __DIR__.'/auth.php';
