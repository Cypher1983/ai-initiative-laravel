<template>
    <div class="min-h-full bg-gray-100 dark:bg-gray-900 flex flex-col">
      <!-- Header -->
      <!-- <header class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">🤖 AI Chat Assistant</h1>
      </header> -->
  
      <!-- Chat Window -->
      <main class="flex-1 flex flex-col items-center overflow-hidden">
        <div class="max-w-6xl w-full flex flex-col flex-1 px-8 py-8 max-h-full">
          <div
            ref="chatScroll"
            class="flex-1 w-full overflow-y-auto rounded-lg p-8 space-y-4 flex flex-col"
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
                ? 'bg-blue-600 text-white rounded-br-none'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-bl-none'
            ]"

            style="line-height: 1.7rem;"
          >
                  <template v-if="msg.role === 'assistant' && msg.loading">
                    <span class="inline-flex items-center align-middle" style="height: 1.75em;">
                      <font-awesome-icon icon="fa-solid fa-ellipsis" class="animate-pulse text-gray-500 dark:text-gray-400 text-xl" style="vertical-align: middle;" />
                    </span>
                  </template>
                  <template v-else>
                    <div v-if="msg.role === 'assistant'" v-html="renderMarkdown(msg.content)"></div>
                    <span v-else>{{ msg.content }}</span>
                  </template>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Message input -->
          <form @submit.prevent="sendMessage" class="mt-4 flex w-full gap-3">
            <input type="text"
              v-model="userInput"
              rows="1"
              style="overflow:hidden"
              class="flex-1 resize-none p-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
              placeholder="Ask anything..."
            >
            <button
              type="submit"
              class="bg-blue-600 text-white px-5 py-3 rounded-md shadow hover:bg-blue-700 transition self-start flex items-center justify-center"
              style="align-self: flex-start;"
            >
              <font-awesome-icon icon="fa-solid fa-paper-plane" />
            </button>
          </form>
        </div>
      </main>
    </div>
  </template>
  
  <script setup>
  import { ref, onUpdated } from 'vue'
  import axios from 'axios'// Import Font Awesome core
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'// Import the faEllipsis icon
  import { faEllipsis } from '@fortawesome/free-solid-svg-icons'// Add the icon to the library
  library.add(faEllipsis);
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
  const messages = ref([
    {
      role: 'assistant',
      content: '👋 Welcome! I\'m your AI assistant. Ask me anything and I\'ll help you out!',
      loading: false
    }
  ])
  const chatScroll = ref(null)
  
  const sendMessage = async () => {
    if (!userInput.value.trim()) return
  
    const input = userInput.value
    messages.value.push({ role: 'user', content: input, loading: false })
    userInput.value = ''
  
    // Push loading assistant message
    messages.value.push({ role: 'assistant', content: '', loading: true })

    try {
      const { data } = await axios.post('/api/llm/message', {
        prompt: input
      })
  
      // Replace the last assistant message (loading) with the actual response
      messages.value[messages.value.length - 1] = { role: 'assistant', content: data.response, loading: false }
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
  
  onUpdated(() => {
    if (chatScroll.value) {
      chatScroll.value.scrollTop = chatScroll.value.scrollHeight
    }
  })

  const autoGrow = (event) => {
    event.target.style.height = 'auto'
    event.target.style.height = event.target.scrollHeight + 'px'
  }

  const renderMarkdown = (text) => marked.parse(text)
  </script>

  <style>
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
  </style>
