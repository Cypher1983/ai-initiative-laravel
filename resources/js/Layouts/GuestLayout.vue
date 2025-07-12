<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isDarkMode = ref(false);

const applyDarkMode = (darkMode) => {
  if (darkMode) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
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
})
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 dark:bg-gray-900 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500 dark:text-gray-400" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white dark:bg-gray-800 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
