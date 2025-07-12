<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LlmChat from '@/Components/LlmmChat.vue'

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

onMounted(() => {
  // Add event listener for reset-chat event
  window.addEventListener('reset-chat', handleResetChat)
})

onUnmounted(() => {
  // Clean up event listener
  window.removeEventListener('reset-chat', handleResetChat)
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
