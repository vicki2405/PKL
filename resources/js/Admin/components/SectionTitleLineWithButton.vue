<script setup>
import { useSlots, computed } from 'vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import IconRounded from '@admin/components/IconRounded.vue'

defineProps({
  icon: {
    type: String,
    default: null,
  },
  title: {
    type: String,
    required: true,
  },
  main: Boolean,
})

const hasSlot = computed(() => useSlots().default)
</script>

<template>
  <section :class="{ 'pt-4 sm:pt-6': !main }" class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <div class="flex items-center justify-start w-full sm:w-auto">
      <IconRounded v-if="icon && main" :icon="icon" color="light" class="mr-3 flex-shrink-0" bg />
      <BaseIcon v-else-if="icon" :path="icon" class="mr-2 flex-shrink-0" size="20" />
      <h1 :class="main ? 'text-xl sm:text-2xl md:text-3xl' : 'text-lg sm:text-xl md:text-2xl'" class="leading-tight break-words">
        {{ title }}
      </h1>
    </div>
    <div v-if="hasSlot" class="flex-shrink-0 w-full sm:w-auto">
      <slot />
    </div>
  </section>
</template>
