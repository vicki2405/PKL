<script setup>
defineProps({
  noPadding: Boolean,
})
</script>

<template>
  <div class="flex-1 overflow-y-auto min-h-0" :class="{ 'p-3 sm:p-4 md:p-6': !noPadding }">
    <slot />
  </div>
</template>
