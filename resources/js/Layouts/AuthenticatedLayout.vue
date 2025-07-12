<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const mobileSidebarOpen = ref(false)
const userDropdownOpen = ref(false)
const isDarkMode = ref(false)
const sidebarCollapsed = ref(false)
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

const handleNewChat = () => {
  // Emit a custom event that the Dashboard can listen to
  window.dispatchEvent(new CustomEvent('reset-chat'))
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

  document.addEventListener('click', (e) => {
    const dropdown = document.querySelector('[data-dropdown]')
    if (dropdown && !dropdown.contains(e.target)) {
      closeUserDropdown()
    }
  })
})
</script>

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
      <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
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
        
        <!-- <Link 
          href="/" 
          :class="[
            'block px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors',
            sidebarCollapsed ? 'text-center' : ''
          ]"
          :title="sidebarCollapsed ? 'Home' : ''"
        >
          <span v-if="!sidebarCollapsed">Home</span>
          <font-awesome-icon v-else :icon="['fas', 'home']" class="text-lg" />
        </Link> -->
        <!-- Add more nav items here -->
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
      <div class="bg-white dark:bg-gray-800 w-64 h-full p-4">
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
        <!-- <Link href="/" class="block px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">Home</Link> -->
        
      </div>
    </div>
  </div>
</template>
