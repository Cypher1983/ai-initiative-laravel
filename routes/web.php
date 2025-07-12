<?php

use App\Http\Controllers\ProfileController;
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
});

Route::post('/api/llm/message', function (Request $request) {
    $request->validate([
        'prompt' => 'required|string',
    ]);

    try {
        // Call the FastAPI backend (or LM Studio directly if needed)
        $ragResponse = Http::timeout(3600)->post('http://127.0.0.1:8001/llm/query', [
            'prompt' => $request->input('prompt'),
        ]);

        if ($ragResponse->failed()) {
            return response()->json(['error' => 'RAG backend failed'], 502);
        }

        $data = $ragResponse->json();
        $reply = $data['content'] ?? 'No response content found.';

        return response()->json(['response' => $reply,'context_text' => $data['context_text'] ?? 'No context text found.','debug_info' => $data['debug_info'] ?? 'No debug info found.']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'LLM request error: ' . $e->getMessage()], 500);
    }
});

require __DIR__.'/auth.php';
