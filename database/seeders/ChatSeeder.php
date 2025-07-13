<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (or create one if none exists)
        $user = User::first();
        
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create a sample chat session
        $chatSession = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Sample Chat Session',
            'description' => 'A sample chat session for testing',
            'is_active' => true,
            'last_activity' => now(),
        ]);

        // Create sample messages
        $messages = [
            [
                'role' => 'user',
                'content' => 'Hello! Can you help me with a programming question?',
                'tokens_used' => 15,
                'model_used' => 'gpt-3.5-turbo',
                'response_time' => 0.5,
            ],
            [
                'role' => 'assistant',
                'content' => 'Of course! I\'d be happy to help you with your programming question. What would you like to know?',
                'tokens_used' => 25,
                'model_used' => 'gpt-3.5-turbo',
                'response_time' => 1.2,
            ],
            [
                'role' => 'user',
                'content' => 'How do I create a migration in Laravel?',
                'tokens_used' => 12,
                'model_used' => 'gpt-3.5-turbo',
                'response_time' => 0.3,
            ],
            [
                'role' => 'assistant',
                'content' => 'To create a migration in Laravel, you can use the Artisan command:\n\n```bash\nphp artisan make:migration create_table_name_table\n```\n\nThis will create a new migration file in the `database/migrations` directory. You can then define the table structure in the `up()` method and specify how to rollback the migration in the `down()` method.',
                'tokens_used' => 85,
                'model_used' => 'gpt-3.5-turbo',
                'response_time' => 2.1,
            ],
        ];

        foreach ($messages as $messageData) {
            ChatMessage::create([
                'chat_session_id' => $chatSession->id,
                ...$messageData,
            ]);
        }

        // Create another inactive session
        $inactiveSession = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Previous Chat Session',
            'description' => 'An older chat session',
            'is_active' => false,
            'last_activity' => now()->subDays(2),
        ]);

        // Add a few messages to the inactive session
        ChatMessage::create([
            'chat_session_id' => $inactiveSession->id,
            'role' => 'user',
            'content' => 'This is from an older session',
            'tokens_used' => 10,
            'model_used' => 'gpt-3.5-turbo',
            'response_time' => 0.4,
        ]);
    }
} 