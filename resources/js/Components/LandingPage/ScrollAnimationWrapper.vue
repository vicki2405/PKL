<script setup>
import { computed } from "vue";

const props = defineProps({
  className: {
    type: String,
    default: "",
  },
});
</script>

<template>
  <div :class="className">
    <slot></slot>
  </div>
</template>
