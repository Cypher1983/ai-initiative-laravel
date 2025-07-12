<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LlmChat from '@/Components/LlmmChat.vue'
</script>

<template>
  <AuthenticatedLayout>
    <div class="h-full p-4">
      <LlmChat />
    </div>
  </AuthenticatedLayout>
</template>
