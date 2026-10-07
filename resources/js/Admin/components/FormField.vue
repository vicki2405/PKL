<script setup>
import { computed, useSlots } from 'vue'

defineProps({
  label: {
    type: String,
    default: null,
  },
  labelFor: {
    type: String,
    default: null,
  },
  help: {
    type: String,
    default: null,
  },
})

const slots = useSlots()

const wrapperClass = computed(() => {
  const base = []
  const slotsLength = slots.default().length

  if (slotsLength > 1) {
    base.push('grid grid-cols-1 gap-3')
  }

  if (slotsLength === 2) {
    base.push('md:grid-cols-2')
  }

  return base
})
</script>

<template>
  <div class="mb-4 sm:mb-6 last:mb-0">
    <label v-if="label" :for="labelFor" class="block font-bold mb-1.5 sm:mb-2 text-sm sm:text-base text-black dark:text-white">{{ label }}</label>
    <div :class="wrapperClass">
      <slot />
    </div>
    <div v-if="help" class="text-xs text-gray-600 dark:text-slate-400 mt-1">
      {{ help }}
    </div>
  </div>
</template>
