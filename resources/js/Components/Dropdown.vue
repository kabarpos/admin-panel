<template>
  <div class="relative">
    <!-- Trigger Button -->
    <div @click="open = ! open">
      <slot name="trigger" />
    </div>

    <!-- Dropdown Content -->
    <div v-show="open"
         @click="open = false"
         class="fixed inset-0 z-40"
    />

    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-show="open"
           class="absolute z-50 mt-2 rounded-md shadow-lg"
           :class="[
             widthClass,
             alignmentClasses
           ]"
           style="display: none;"
           @click="open = false"
      >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
          <slot name="content" />
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  align: {
    type: String,
    default: 'right'
  },
  width: {
    type: String,
    default: '48'
  },
  contentClasses: {
    type: String,
    default: 'py-1 bg-white'
  }
})

const open = ref(false)

const widthClass = computed(() => {
  return {
    '48': 'w-48'
  }[props.width.toString()]
})

const alignmentClasses = computed(() => {
  if (props.align === 'left') {
    return 'origin-top-left left-0'
  } else if (props.align === 'right') {
    return 'origin-top-right right-0'
  } else {
    return 'origin-top'
  }
})

const closeOnEscape = (e) => {
  if (open.value && e.key === 'Escape') {
    open.value = false
  }
}

onMounted(() => document.addEventListener('keydown', closeOnEscape))
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape))
</script> 