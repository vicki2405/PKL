<script setup>
import { containerMaxW } from '@admin/config.js'
</script>

<template>
  <section class="p-3 sm:p-4 md:p-6" :class="containerMaxW">
    <slot />
  </section>
</template>
