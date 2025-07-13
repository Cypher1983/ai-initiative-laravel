<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const mobileSidebarOpen = ref(false)
const userDropdownOpen = ref(false)
const isDarkMode = ref(false)
const sidebarCollapsed = ref(false)
const chatSessions = ref([])
const isLoadingSessions = ref(false)
const $page = usePage()

const logout = () => {
  router.post('/logout')
}

const toggleUserDropdown = () => {
  userDropdownOpen.value = !userDropdownOpen.value
}

const closeUserDropdown = () => {
  userDropdownOpen.value = false
}

const applyDarkMode = (darkMode) => {
  if (darkMode) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  applyDarkMode(isDarkMode.value)
  localStorage.setItem('darkMode', isDarkMode.value.toString())
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString())
}

const fetchChatSessions = async () => {
  if (!$page.props.auth.user) return
  
  try {
    isLoadingSessions.value = true
    const response = await axios.get('/api/chat/sessions')
    chatSessions.value = response.data
  } catch (error) {
    console.error('Error fetching chat sessions:', error)
  } finally {
    isLoadingSessions.value = false
  }
}

const loadChatSession = async (sessionId) => {
  // Emit a custom event to load the specific chat session
  window.dispatchEvent(new CustomEvent('load-chat-session', { 
    detail: { sessionId } 
  }))
  
  // Close mobile sidebar if open
  mobileSidebarOpen.value = false
}

const handleNewChat = () => {
  // Emit a custom event that the Dashboard can listen to
  window.dispatchEvent(new CustomEvent('reset-chat'))
  
  // Create a new session object to prepend to the list
  const newSession = {
    id: Date.now(), // Temporary ID until we get the real one
    title: 'New Chat',
    description: 'New chat session',
    is_active: true,
    last_activity: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    created_at: new Date().toISOString(),
    messages: []
  }
  
  // Prepend the new session to the list
  chatSessions.value.unshift(newSession)
}

const formatSessionTitle = (session) => {
  if (session.title && session.title !== 'New Chat') {
    return session.title
  }
  
  // If no title, use the first user message content with smart truncation
  const firstUserMessage = session.messages?.find(msg => msg.role === 'user')
  if (firstUserMessage) {
    const content = firstUserMessage.content.trim()
    
    // Try to find a good breaking point
    if (content.length <= 40) {
      return content
    }
    
    // Look for natural break points
    const breakPoints = ['.', '!', '?', '\n', ';', ',']
    let bestBreak = 40
    
    for (const breakPoint of breakPoints) {
      const index = content.indexOf(breakPoint, 20)
      if (index > 0 && index <= 40) {
        bestBreak = index
        break
      }
    }
    
    // If no good break point, try to break at word boundary
    if (bestBreak === 40) {
      const lastSpace = content.lastIndexOf(' ', 40)
      if (lastSpace > 20) {
        bestBreak = lastSpace
      }
    }
    
    return content.substring(0, bestBreak).trim() + '...'
  }
  
  return 'New Chat'
}

const formatSessionDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) {
    return 'Today'
  } else if (diffDays === 2) {
    return 'Yesterday'
  } else if (diffDays <= 7) {
    return `${diffDays - 1} days ago`
  } else {
    return date.toLocaleDateString()
  }
}

// Handle new session created event
const handleNewSessionCreated = (event) => {
  const newSession = event.detail.session
  // Find and replace the temporary session with the real one
  const tempIndex = chatSessions.value.findIndex(session => 
    session.id === Date.now() || session.title === 'New Chat' && session.messages?.length === 0
  )
  if (tempIndex !== -1) {
    chatSessions.value[tempIndex] = newSession
  } else {
    // If no temporary session found, prepend the new one
    chatSessions.value.unshift(newSession)
  }
}

onMounted(() => {
  // Check for saved dark mode preference and apply immediately
  const savedDarkMode = localStorage.getItem('darkMode')
  if (savedDarkMode === 'true' || (!savedDarkMode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDarkMode.value = true
    applyDarkMode(true)
  } else {
    isDarkMode.value = false
    applyDarkMode(false)
  }

  // Check for saved sidebar collapsed preference
  const savedSidebarCollapsed = localStorage.getItem('sidebarCollapsed')
  if (savedSidebarCollapsed === 'true') {
    sidebarCollapsed.value = true
  }

  // Fetch chat sessions if user is authenticated
  if ($page.props.auth.user) {
    fetchChatSessions()
  }

  // Listen for refresh-chat-sessions event
  window.addEventListener('refresh-chat-sessions', fetchChatSessions)
  
  // Listen for new session created event
  window.addEventListener('new-session-created', handleNewSessionCreated)

  document.addEventListener('click', (e) => {
    const dropdown = document.querySelector('[data-dropdown]')
    if (dropdown && !dropdown.contains(e.target)) {
      closeUserDropdown()
    }
  })
})

onUnmounted(() => {
  // Clean up event listeners
  window.removeEventListener('refresh-chat-sessions', fetchChatSessions)
  window.removeEventListener('new-session-created', handleNewSessionCreated)
})
</script>

<style scoped>
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
</style>

<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Sidebar - Only show when authenticated -->
    <aside v-if="$page.props.auth.user" :class="[
      'hidden md:flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300',
      sidebarCollapsed ? 'w-16' : 'w-64'
    ]">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h1 v-if="!sidebarCollapsed" class="text-xl font-semibold text-gray-800 dark:text-gray-200 w-fit">The AI Initiative</h1>
        <h1 v-else class="text-xl font-semibold text-gray-800 dark:text-gray-200">AI</h1>
        <button 
          @click="toggleSidebar" 
          class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors"
          :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
        >
          <font-awesome-icon :icon="['fas', sidebarCollapsed ? 'chevron-right' : 'chevron-left']" class="text-sm" />
        </button>
      </div>
      <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto custom-scrollbar">
        <!-- New Chat Button -->
        <button 
          @click="handleNewChat"
          :class="[
            'block w-full px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors',
            sidebarCollapsed ? 'text-center' : ''
          ]"
          :title="sidebarCollapsed ? 'New Chat' : ''"
        >
          <span v-if="!sidebarCollapsed" class="flex items-center">
            <span class="mr-2 text-sm font-bold">+</span>
            New Chat
          </span>
          <font-awesome-icon v-else :icon="['fas', 'plus']" class="text-lg" />
        </button>
        
        <!-- Chat History -->
        <div v-if="chatSessions.length > 0" class="mt-4">
          <h3 v-if="!sidebarCollapsed" class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Chat History</h3>
          <div v-if="isLoadingSessions" class="text-sm text-gray-500 dark:text-gray-400">Loading history...</div>
          <div v-else class="space-y-1">
            <button 
              v-for="session in chatSessions" 
              :key="session.id" 
              @click="loadChatSession(session.id)"
              :class="[
                'block w-full text-left px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors',
                sidebarCollapsed ? 'text-center' : ''
              ]"
              :title="sidebarCollapsed ? formatSessionTitle(session) : ''"
            >
              <div v-if="!sidebarCollapsed" class="flex flex-col">
                <span class="text-sm font-medium truncate">{{ formatSessionTitle(session) }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatSessionDate(session.updated_at) }}</span>
              </div>
              <font-awesome-icon v-else :icon="['fas', 'history']" class="text-lg" />
            </button>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main Area -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
      <!-- Mobile Menu Button - Only show when authenticated -->
      <button v-if="$page.props.auth.user" @click="mobileSidebarOpen = !mobileSidebarOpen" class="absolute top-4 left-4 z-50 md:hidden bg-white dark:bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
        ☰
      </button>

      <!-- User Avatar Dropdown or Login Button - Absolute Positioned -->
      <div class="absolute top-4 right-4 z-50" data-dropdown>
        <!-- Show login button for unauthenticated users -->
        <Link 
          v-if="!$page.props.auth.user" 
          href="/login" 
          class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 transition duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 active:bg-gray-100 dark:active:bg-gray-600"
        >
          <span class="text-sm font-medium">Login</span>
        </Link>
        
        <!-- Show user avatar dropdown for authenticated users -->
        <template v-else>
          <button @click="toggleUserDropdown" class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center shadow-lg">
              <font-awesome-icon :icon="['fas', 'user']" class="text-gray-600 dark:text-gray-400 text-sm" />
            </div>
          </button>
          
          <!-- Dropdown Menu -->
          <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
            <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
              Profile
            </Link>
            <hr class="my-1 border-gray-200 dark:border-gray-600">
            <button @click="toggleDarkMode" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
              <span class="flex items-center">
                <font-awesome-icon :icon="['fas', isDarkMode ? 'sun' : 'moon']" class="mr-2" />
                {{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}
              </span>
            </button>
            <hr class="my-1 border-gray-200 dark:border-gray-600">
            <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
              Logout
            </button>
          </div>
        </template>
      </div>

      <!-- Content -->
      <main class="flex-1 overflow-auto">
        <slot />
      </main>

      <!-- Footer -->
      <!-- <footer class="p-4 text-center text-xs text-gray-500 bg-white border-t">
        Built with Laravel + Vue + Tailwind
      </footer> -->
    </div>

    <!-- Mobile Sidebar Overlay - Only show when authenticated -->
    <div v-if="mobileSidebarOpen && $page.props.auth.user" class="fixed inset-0 bg-black bg-opacity-40 z-50 md:hidden" @click="mobileSidebarOpen = false">
      <div class="bg-white dark:bg-gray-800 w-64 h-full p-4 custom-scrollbar overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Menu</h2>
        <!-- New Chat Button for Mobile -->
        <button 
          @click="handleNewChat"
          class="block w-full px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
        >
          <span class="flex items-center">
            <font-awesome-icon :icon="['fas', 'plus']" class="mr-2 text-sm" />
            New Chat
          </span>
        </button>
        
        <!-- Chat History for Mobile -->
        <div v-if="chatSessions.length > 0" class="mt-4">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Chat History</h3>
          <div v-if="isLoadingSessions" class="text-sm text-gray-500 dark:text-gray-400">Loading history...</div>
          <div v-else class="space-y-1">
            <button 
              v-for="session in chatSessions" 
              :key="session.id" 
              @click="loadChatSession(session.id)"
              class="block w-full text-left px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
            >
              <div class="flex flex-col">
                <span class="text-sm font-medium truncate">{{ formatSessionTitle(session) }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatSessionDate(session.updated_at) }}</span>
              </div>
            </button>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>
