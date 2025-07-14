<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LlmChat from '@/Components/LlmChat.vue'

const llmChatRef = ref(null)

// Method to reset the chat
const resetChat = () => {
  if (llmChatRef.value) {
    llmChatRef.value.resetChat()
  }
}

// Listen for the reset-chat event from the layout
const handleResetChat = () => {
  resetChat()
}

// Listen for the load-chat-session event from the layout
const handleLoadChatSession = (event) => {
  if (llmChatRef.value) {
    llmChatRef.value.loadChatSession(event.detail.sessionId)
  }
}

onMounted(() => {
  // Add event listener for reset-chat event
  window.addEventListener('reset-chat', handleResetChat)
  // Add event listener for load-chat-session event
  window.addEventListener('load-chat-session', handleLoadChatSession)
})

onUnmounted(() => {
  // Clean up event listeners
  window.removeEventListener('reset-chat', handleResetChat)
  window.removeEventListener('load-chat-session', handleLoadChatSession)
})

// Expose the reset method to parent components
defineExpose({
  resetChat
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="h-full p-4">
      <LlmChat ref="llmChatRef" />
    </div>
  </AuthenticatedLayout>
</template>
