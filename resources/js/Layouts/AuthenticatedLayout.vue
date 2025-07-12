<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const mobileSidebarOpen = ref(false)
</script>

<template>
  <div class="flex h-screen bg-gray-100 text-gray-900">
    <!-- Sidebar -->
    <aside class="hidden md:flex w-64 flex-col bg-white border-r">
      <div class="px-6 py-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">🤖 AI Assistant</h1>
      </div>
      <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
        <Link href="/dashboard" class="block px-3 py-2 rounded hover:bg-gray-200">Home</Link>
        <Link href="/chat" class="block px-3 py-2 rounded hover:bg-gray-200">Chat</Link>
        <!-- Add more nav items here -->
      </nav>
      <div class="p-4 border-t">
        <form method="POST" action="/logout">
          <button type="submit" class="w-full text-left text-sm text-red-600 hover:underline">Logout</button>
        </form>
      </div>
    </aside>

    <!-- Main Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top bar -->
      <header class="bg-white shadow px-6 py-4 flex justify-between items-center md:hidden">
        <h1 class="text-lg font-semibold">AI Assistant</h1>
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="text-gray-600">
          ☰
        </button>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-auto">
        <slot />
      </main>

      <!-- Footer -->
      <!-- <footer class="p-4 text-center text-xs text-gray-500 bg-white border-t">
        Built with Laravel + Vue + Tailwind
      </footer> -->
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div v-if="mobileSidebarOpen" class="fixed inset-0 bg-black bg-opacity-40 z-50 md:hidden" @click="mobileSidebarOpen = false">
      <div class="bg-white w-64 h-full p-4">
        <h2 class="text-lg font-semibold mb-4">Menu</h2>
        <Link href="/dashboard" class="block px-3 py-2 rounded hover:bg-gray-200">Home</Link>
        <Link href="/chat" class="block px-3 py-2 rounded hover:bg-gray-200">Chat</Link>
        <form method="POST" action="/logout" class="mt-4">
          <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
        </form>
      </div>
    </div>
  </div>
</template>
