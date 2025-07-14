<template>
    <div class="min-h-full bg-gray-100 dark:bg-gray-900 flex flex-col">
      <!-- Header -->
      <!-- <header class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">🤖 AI Chat Assistant</h1>
      </header> -->
  
      <!-- Chat Window -->
      <main class="flex-1 flex flex-col items-center overflow-hidden">
        <div class="max-w-6xl w-full flex flex-col flex-1 px-8 py-8 max-h-full">
          <!-- Loading state -->
          <div v-if="isLoadingSession" class="flex-1 flex items-center justify-center">
            <div class="text-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
              <p class="text-gray-600 dark:text-gray-400">Creating new chat...</p>
            </div>
          </div>
          
          <div
            v-else
            ref="chatScroll"
            class="flex-1 w-full overflow-y-auto rounded-lg p-8 space-y-4 flex flex-col custom-scrollbar"
            style="min-height: 0; max-height: 85vh;"
          >
            <div v-for="(msg, index) in messages" :key="index" class="w-full">
              <div
          :class="msg.role === 'user'
            ? 'flex justify-end'
            : 'flex justify-start'"
              >
          <div
            :class="[
              'max-w-2xl md:max-w-3xl px-6 py-2 rounded-lg text-sm',
              msg.role === 'user'
                ? 'bg-teal-500 text-white rounded-br-none'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-bl-none'
            ]"

            style="line-height: 1.7rem;"
          >
                  <template v-if="msg.role === 'assistant' && msg.loading">
                    <span class="inline-flex items-center align-middle" style="height: 1.75em;">
                      <font-awesome-icon icon="fa-solid fa-ellipsis" class="animate-pulse text-gray-500 dark:text-gray-400 text-xl" style="vertical-align: middle;" />
                    </span>
                  </template>
                  <template v-else-if="msg.role === 'assistant' && msg.options">
                    <!-- Multiple options display -->
                    <div class="space-y-3">
                      <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        I have multiple responses for you. Please select the best one:
                      </div>
                      <div v-for="(option, optionIndex) in msg.options" :key="optionIndex" class="option-container">
                        <div class="flex items-start space-x-3">
                          <button
                            @click="selectOption(index, optionIndex)"
                            :class="[
                              'option-radio flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors',
                              msg.selectedOption === optionIndex
                                ? 'border-blue-500 bg-blue-500 text-white'
                                : 'border-gray-300 dark:border-gray-600 hover:border-blue-400'
                            ]"
                          >
                            <font-awesome-icon 
                              v-if="msg.selectedOption === optionIndex" 
                              icon="fa-solid fa-check" 
                              class="text-xs" 
                            />
                          </button>
                          <div class="flex-1">
                            <!-- <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                              Option {{ optionIndex + 1 }}
                            </div> -->
                            <div v-html="renderMarkdown(option || '')" class="text-sm multiple-options"></div>
                          </div>
                        </div>
                      </div>
                      <div class="flex space-x-2 mt-3">
                        <button
                          @click="confirmSelection(index)"
                          :disabled="msg.selectedOption === null"
                          class="confirm-button px-4 py-2 text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          Confirm Selection
                        </button>
                        <button
                          @click="regenerateOptions(index)"
                          class="regenerate-button px-4 py-2 text-white rounded-md text-sm font-medium"
                        >
                          Regenerate
                        </button>
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <div v-if="msg.role === 'assistant'" v-html="renderMarkdown(msg.content || '')"></div>
                    <span v-else>{{ msg.content }}</span>
                  </template>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Message input -->
          <form @submit.prevent="sendMessage" class="mt-4 flex w-full" v-if="!isLoadingSession">
            <div class="flex-1 relative">
              <input type="text"
                v-model="userInput"
                rows="1"
                style="overflow:hidden"
                :disabled="!currentSessionId"
                class="w-full resize-none p-2 pr-12 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                placeholder="Ask anything..."
              >
              <button
                type="submit"
                :disabled="!userInput.trim() || !currentSessionId"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-transparent border-transparent p-2 rounded-md transition flex items-center justify-center"
                :class="userInput.trim() && currentSessionId ? 'text-gray-500 hover:text-blue-600 cursor-pointer' : 'text-gray-300 cursor-not-allowed'"
              >
                <font-awesome-icon icon="fa-solid fa-paper-plane" />
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </template>
  
  <script setup>
  import { ref, onUpdated, onMounted, onUnmounted } from 'vue'
  import axios from 'axios'// Import Font Awesome core
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'// Import the faEllipsis icon
  import { faEllipsis, faCheck } from '@fortawesome/free-solid-svg-icons'// Add the icon to the library
  library.add(faEllipsis, faCheck);
  import { marked } from 'marked'
  import hljs from 'highlight.js'
  import 'highlight.js/styles/github.css' // Or another theme

  marked.setOptions({
    highlight: function(code, lang) {
      // Highlight code
      const highlighted = lang && hljs.getLanguage(lang)
        ? hljs.highlight(code, { language: lang }).value
        : hljs.highlightAuto(code).value
      // If lang is present and valid, add a label above the code block
      if (lang && hljs.getLanguage(lang)) {
        return `<div style="font-size:0.8em;color:#888;margin-bottom:2px;">${lang}</div><pre><code>${highlighted}</code></pre>`
      }
      // No language indicator
      return `<pre class="jf"><code>${highlighted}</code></pre>`
    }
  })
  
  const userInput = ref('')
  const messages = ref([])
  const chatScroll = ref(null)
  const currentSessionId = ref(null)
  const isLoadingSession = ref(true)

  // Create a new chat session by default
  const createNewSession = async () => {
  try {
    isLoadingSession.value = true
    
    // Create a new chat session
    const response = await axios.post('/api/chat/sessions', {
      title: 'New Chat',
      description: 'New chat session'
    })
    
    currentSessionId.value = response.data.id
    
    // Emit the new session data to update the sidebar
    window.dispatchEvent(new CustomEvent('new-session-created', { 
      detail: { session: response.data } 
    }))
    
    messages.value = [{
      role: 'assistant',
      content: 'Welcome! I\'m your AI assistant. Ask me anything and I\'ll help you out!',
      loading: false
    }]
  } catch (error) {
    if (error.response && error.response.status === 401) {
      messages.value = [{
        role: 'assistant',
        content: 'You must be logged in to start a chat session.',
        loading: false
      }]
    } else {
      console.error('Error creating new chat session:', error)
      // Fallback to empty chat
      messages.value = [{
        role: 'assistant',
        content: 'Welcome! I\'m your AI assistant. Ask me anything and I\'ll help you out!',
        loading: false
      }]
    }
  } finally {
    isLoadingSession.value = false
  }
}

  // Reset method to create new chat session
  const resetChat = async () => {
    try {
      // Create a new chat session
      const response = await axios.post('/api/chat/sessions', {
        title: 'New Chat',
        description: 'New chat session'
      })
      
      currentSessionId.value = response.data.id
      
      // Emit the new session data to update the sidebar
      window.dispatchEvent(new CustomEvent('new-session-created', { 
        detail: { session: response.data } 
      }))
      
      messages.value = [{
        role: 'assistant',
        content: 'Welcome! I\'m your AI assistant. Ask me anything and I\'ll help you out!',
        loading: false
      }]
      userInput.value = ''
    } catch (error) {
      if (error.response && error.response.status === 401) {
        messages.value = [{
          role: 'assistant',
          content: 'You must be logged in to start a chat session.',
          loading: false
        }]
      } else {
        console.error('Error creating new chat session:', error)
      }
    }
  }

  // Load a specific chat session
  const loadChatSession = async (sessionId) => {
    try {
      isLoadingSession.value = true
      
      // Fetch the specific chat session with messages
      const response = await axios.get(`/api/chat/sessions/${sessionId}`)
      const session = response.data
      
      currentSessionId.value = session.id
      messages.value = session.messages.map(msg => ({
        role: msg.role,
        content: msg.content,
        loading: false
      }))
      
      // If no messages, add welcome message
      if (messages.value.length === 0) {
        messages.value = [{
          role: 'assistant',
          content: 'Welcome! I\'m your AI assistant. Ask me anything and I\'ll help you out!',
          loading: false
        }]
      }
    } catch (error) {
      console.error('Error loading chat session:', error)
      // Fallback to new session
      await createNewSession()
    } finally {
      isLoadingSession.value = false
    }
  }

  // Expose the reset method to parent components
  defineExpose({
    resetChat,
    loadChatSession
  })

  // Listen for load-chat-session event
  const handleLoadChatSession = (event) => {
    loadChatSession(event.detail.sessionId)
  }

  // Listen for chat-session-deleted event
  const handleChatSessionDeleted = (event) => {
    const deletedSessionId = event.detail.sessionId
    
    // If the deleted session is the current one, check if we need to create a new session
    if (currentSessionId.value === deletedSessionId) {
      // Only create a new session if there are no other sessions available
      // We'll wait a moment for the sidebar to update, then check if we need a new session
      setTimeout(() => {
        // Emit an event to check if there are any remaining sessions
        window.dispatchEvent(new CustomEvent('check-remaining-sessions'))
      }, 100)
    }
  }

  // Listen for check-remaining-sessions event
  const handleCheckRemainingSessions = async () => {
    try {
      // Check if there are any remaining sessions
      const response = await axios.get('/api/chat/sessions')
      if (response.data.length === 0) {
        // No sessions left, create a new one
        createNewSession()
      } else {
        // Load the first available session
        loadChatSession(response.data[0].id)
      }
    } catch (error) {
      console.error('Error checking remaining sessions:', error)
      // Fallback to creating a new session
      createNewSession()
    }
  }

  // Initialize chat session on component mount
  onMounted(async () => {
    try {
      // Try to get the active session first
      const response = await axios.get('/api/chat/sessions/active')
      const activeSession = response.data
      
      // Check if the active session has any messages at all
      const hasMessages = activeSession.messages && activeSession.messages.length === 0
      
      if (hasMessages) {
        // Load the active session if it has any messages
        loadChatSession(activeSession.id)
      } else {
        // Create a new session only if the active session is completely empty
        createNewSession()
      }
    } catch (error) {
      console.error('Error loading active session:', error)
      // Fallback to creating a new session
      createNewSession()
    }
    
    // Add event listener for loading specific chat sessions
    window.addEventListener('load-chat-session', handleLoadChatSession)
    
    // Add event listener for deleted chat sessions
    window.addEventListener('chat-session-deleted', handleChatSessionDeleted)
    
    // Add event listener for checking remaining sessions
    window.addEventListener('check-remaining-sessions', handleCheckRemainingSessions)
  })

  onUnmounted(() => {
    // Clean up event listeners
    window.removeEventListener('load-chat-session', handleLoadChatSession)
    window.removeEventListener('chat-session-deleted', handleChatSessionDeleted)
    window.removeEventListener('check-remaining-sessions', handleCheckRemainingSessions)
  })

  // Select an option from multiple responses
  const selectOption = (messageIndex, optionIndex) => {
    messages.value[messageIndex].selectedOption = optionIndex
  }

  // Confirm the selected option
  const confirmSelection = async (messageIndex) => {
    const message = messages.value[messageIndex]
    if (message.selectedOption === null) return

    const selectedContent = message.options[message.selectedOption]
    
    // Replace the options message with the selected content
    messages.value[messageIndex] = {
      role: 'assistant',
      content: selectedContent,
      loading: false
    }

    // Store the selected response in database
    try {
      await axios.post(`/api/chat/sessions/${currentSessionId.value}/messages`, {
        role: 'assistant',
        content: selectedContent,
        tokens_used: Math.ceil(selectedContent.length / 4), // Rough estimate
        model_used: 'gpt-3.5-turbo',
        metadata: {
          was_selected_from_options: true,
          original_options_count: message.options.length
        }
      })
      
      // Emit event to refresh chat sessions list
      window.dispatchEvent(new CustomEvent('refresh-chat-sessions'))
    } catch (error) {
      console.error('Error storing selected assistant message:', error)
    }
  }

  // Regenerate options for a message
  const regenerateOptions = async (messageIndex) => {
    // Find the original user message that prompted this response
    const userMessageIndex = messageIndex - 1
    if (userMessageIndex < 0 || messages.value[userMessageIndex].role !== 'user') return

    const originalPrompt = messages.value[userMessageIndex].content
    
    // Replace the current message with loading state
    messages.value[messageIndex] = { role: 'assistant', content: '', loading: true }

    try {
      const { data } = await axios.post('/api/llm/message', {
        prompt: originalPrompt
      })
      
      // Check if we got multiple options
      if (data.options && Array.isArray(data.options) && data.options.length >= 2) {
        messages.value[messageIndex] = {
          role: 'assistant',
          options: data.options,
          selectedOption: null,
          loading: false
        }
      } else {
        // Fallback to single response
        messages.value[messageIndex] = {
          role: 'assistant',
          content: data.response,
          loading: false
        }
      }
    } catch (error) {
      messages.value[messageIndex] = {
        role: 'assistant',
        content: 'Sorry, I couldn\'t regenerate the options.',
        loading: false
      }
    }
  }

  const sendMessage = async () => {
    if (!userInput.value.trim() || !currentSessionId.value) return
  
    const input = userInput.value
    userInput.value = ''
  
    // Add user message to UI
    messages.value.push({ role: 'user', content: input, loading: false })
  
    // Store user message in database
    try {
      await axios.post(`/api/chat/sessions/${currentSessionId.value}/messages`, {
        role: 'user',
        content: input,
        tokens_used: Math.ceil(input.length / 4), // Rough estimate
        model_used: 'user-input'
      })
    } catch (error) {
      console.error('Error storing user message:', error)
    }
  
    // Generate title for the session if this is the first user message
    if (messages.value.filter(msg => msg.role === 'user').length === 1) {
      generateSessionTitle(input)
    }
  
    // Push loading assistant message
    messages.value.push({ role: 'assistant', content: '', loading: true })

    try {
      const startTime = Date.now()
      const { data } = await axios.post('/api/llm/message', {
        prompt: input
      })
      const responseTime = (Date.now() - startTime) / 1000
  
      // Check if we got multiple options
      if (data.options && Array.isArray(data.options) && data.options.length >= 2) {
        // Replace the last assistant message (loading) with options
        messages.value[messages.value.length - 1] = {
          role: 'assistant',
          options: data.options,
          selectedOption: null,
          loading: false
        }
      } else {
        // Replace the last assistant message (loading) with the actual response
        messages.value[messages.value.length - 1] = { 
          role: 'assistant', 
          content: data.response, 
          loading: false 
        }
      }
  
      // Store assistant response in database (only for single responses)
      if (!data.options) {
        try {
          await axios.post(`/api/chat/sessions/${currentSessionId.value}/messages`, {
            role: 'assistant',
            content: data.response,
            tokens_used: Math.ceil(data.response.length / 4), // Rough estimate
            model_used: 'gpt-3.5-turbo', // Or whatever model you're using
            response_time: responseTime,
            metadata: {
              context_text: data.context_text,
              debug_info: data.debug_info
            }
          })
          
          // Emit event to refresh chat sessions list
          window.dispatchEvent(new CustomEvent('refresh-chat-sessions'))
        } catch (error) {
          console.error('Error storing assistant message:', error)
        }
      }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        // User is not authenticated, show a message about logging in
        messages.value[messages.value.length - 1] = {
          role: 'assistant',
          content: '⚠️ ' + error.response.data.message,
          loading: false
        }
        return
      }
      
      messages.value[messages.value.length - 1] = {
        role: 'assistant',
        content: '⚠️ Sorry, the assistant is currently unavailable.',
        loading: false
      }
    }
  }

  // Generate a title for the chat session based on the first user message
  const generateSessionTitle = async (firstMessage) => {
    try {
      const titlePrompt = `Generate a short, descriptive title (max 50 characters) for a chat session based on this first message: "${firstMessage}". 
      
      Rules:
      - Keep it under 50 characters
      - Make it descriptive but concise
      - Use title case
      - Don't include quotes or special formatting
      - Focus on the main topic or question
      
      Examples:
      - "How to implement authentication"
      - "Python web scraping tutorial"
      - "Database optimization tips"
      - "React component best practices"
      
      Title:`
      
      const { data } = await axios.post('/api/llm/message', {
        prompt: titlePrompt
      })
      
      // Clean up the response and update the session title
      const response = data.response || data.options?.[0] || ''
      const title = response.trim().replace(/^["']|["']$/g, '') // Remove quotes if present
      
      if (title && title.length <= 50) {
        await axios.put(`/api/chat/sessions/${currentSessionId.value}`, {
          title: title
        })
        
        // Emit event to refresh chat sessions list
        window.dispatchEvent(new CustomEvent('refresh-chat-sessions'))
      }
    } catch (error) {
      console.error('Error generating session title:', error)
      // Don't fail the main message flow if title generation fails
    }
  }

  onUpdated(() => {
    if (chatScroll.value) {
      chatScroll.value.scrollTop = chatScroll.value.scrollHeight
    }
  })

  const autoGrow = (event) => {
    event.target.style.height = 'auto'
    event.target.style.height = event.target.scrollHeight + 'px'
  }

  const renderMarkdown = (text) => {
    if (!text || typeof text !== 'string') {
      return ''
    }
    return marked.parse(text)
  }
  </script>

  <style>
  /* Custom scrollbar styles */
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
    transition: background-color 0.2s ease;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
  }

  /* Dark mode scrollbar */
  .dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
  }

  .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
  }

  /* Firefox scrollbar */
  .custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
  }

  .dark .custom-scrollbar {
    scrollbar-color: #4b5563 transparent;
  }

  /* Add this to your CSS (e.g., in app.css or <style> block in LlmmChat.vue) */
  /* Add this to your CSS (e.g., in app.css or <style> block in LlmmChat.vue) */
pre, code {
  background: #313131;
  color: #fff;
  border-radius: 6px;
  padding: 0.45em 0.7em;
  font-size: 0.95em;
  font-family: 'Fira Mono', 'Consolas', 'Menlo', monospace;
  overflow-x: auto;
}

/* Dark mode code blocks */
.dark pre, .dark code {
  background: #1f2937;
  color: #f3f4f6;
}

p{
  line-height: 1.25rem;
  margin-top: 1.5rem !important;
}

.multiple-options p:first-child, .option-radio{
  margin-top: 0.25rem !important;
}

pre, p {
  margin: 0.5em 0;
}

ol{
  list-style: decimal;
  padding-left: 1.5em;
}

ol li{
  padding-bottom: .25rem;;
}

/* Dark mode list styling */
.dark ol {
  color: #f3f4f6;
}

.dark ol li {
  color: #f3f4f6;
}

  /* Multiple options styling */
  .option-container {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
  }

  .dark .option-container {
    border-color: #4b5563;
  }

  .option-container:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
  }

  .dark .option-container:hover {
    background-color: #374151;
    border-color: #60a5fa;
  }

  .option-radio {
    transition: all 0.2s ease;
  }

  .option-radio:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
  }

  .option-radio:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }

  .confirm-button {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    transition: all 0.2s ease;
  }

  .confirm-button:hover:not(:disabled) {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  .regenerate-button {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    transition: all 0.2s ease;
  }

  .regenerate-button:hover {
    background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
  }
  </style>
