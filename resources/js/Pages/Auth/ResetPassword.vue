<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
        Reset your password
      </h2>
      
      <div class="mt-8">
        <ResetPasswordForm :token="token" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import ResetPasswordForm from '@/Components/Auth/ResetPasswordForm.vue'

defineProps({
  token: {
    type: String,
    required: true,
  },
})

defineOptions({
  layout: null,
})
</script>
