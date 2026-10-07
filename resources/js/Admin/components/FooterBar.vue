<script setup>
import { containerMaxW } from '@admin/config.js'
import BaseLevel from '@admin/components/BaseLevel.vue'
</script>

<template>
  <footer class="py-2 px-6" :class="containerMaxW">
    <BaseLevel>
      <div class="text-center md:text-left">
        <slot />
      </div>
    </BaseLevel>
  </footer>
</template>
