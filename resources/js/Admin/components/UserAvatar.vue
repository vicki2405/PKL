<script setup>
import { computed } from 'vue'

const props = defineProps({
  username: {
    type: String,
    required: true,
  },
  avatar: {
    type: String,
    default: null,
  },
})

const initial = computed(() => props.username?.charAt(0)?.toUpperCase() || '?')

const colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500']
const colorClass = computed(() => colors[props.username?.length % colors.length] || 'bg-blue-500')
</script>

<template>
  <div>
    <img
      v-if="avatar"
      :src="avatar"
      :alt="username"
      class="rounded-full block h-auto w-full max-w-full bg-gray-100 dark:bg-slate-800"
    />
    <div
      v-else
      class="rounded-full block h-auto w-full max-w-full flex items-center justify-center text-white font-bold text-lg"
      :class="colorClass"
    >
      {{ initial }}
    </div>
    <slot />
  </div>
</template>
