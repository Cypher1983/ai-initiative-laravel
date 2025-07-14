# RA AI Chat Application

A Laravel-based chat application with AI assistant capabilities, built with Inertia.js and Vue.js.

## Features

- **Real-time Chat Interface**: Modern, responsive chat UI with dark mode support
- **AI Assistant Integration**: Powered by LLM backend for intelligent responses
- **Multiple Response Options**: When the AI returns 2 or more options, users can select the best answer
- **Session Management**: Persistent chat sessions with automatic title generation
- **Markdown Support**: Rich text rendering with syntax highlighting for code blocks
- **Authentication**: User authentication and session management

## Multiple Options Feature

The chat application now supports displaying multiple response options when the AI backend returns 2 or more alternatives. Here's how it works:

### For Users:
1. **Send a message** that triggers multiple options
2. **View the options** displayed as separate cards with radio buttons
3. **Select your preferred option** by clicking the radio button
4. **Confirm your selection** or regenerate new options
5. **Continue the conversation** with your selected response

### For Developers:
- The backend should return an `options` array when multiple responses are available
- Each option should be a string containing the response text
- The frontend will automatically detect and display multiple options
- Users can select, confirm, or regenerate options

### Testing the Feature:
To test the multiple options feature, send a message containing "test options" (case-insensitive). This will trigger a simulated response with 2 options.

## Installation

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install Node.js dependencies: `npm install`
4. Copy `.env.example` to `.env` and configure your environment
5. Generate application key: `php artisan key:generate`
6. Run database migrations: `php artisan migrate`
7. Start the development server: `php artisan serve`
8. Build assets: `npm run dev`

## Configuration

### Backend Integration
The application expects a FastAPI backend at `http://127.0.0.1:8001/llm/query`. Configure your backend to return responses in the following format:

**Single Response:**
```json
{
  "content": "Your response here",
  "context_text": "Optional context",
  "debug_info": "Optional debug info"
}
```

**Multiple Options:**
```json
{
  "options": [
    "First option text",
    "Second option text"
  ],
  "context_text": "Optional context",
  "debug_info": "Optional debug info"
}
```

## Usage

1. Register or log in to the application
2. Start a new chat session
3. Send messages to interact with the AI assistant
4. When multiple options are available, select your preferred response
5. View chat history and manage sessions

## Development

- **Frontend**: Vue.js 3 with Composition API
- **Backend**: Laravel 12 with Inertia.js
- **Styling**: Tailwind CSS with custom dark mode support
- **Icons**: Font Awesome
- **Markdown**: Marked.js with syntax highlighting

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
