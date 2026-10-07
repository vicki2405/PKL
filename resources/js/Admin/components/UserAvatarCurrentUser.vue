<script setup>
import { useMainStore } from '@admin/stores/main'
import UserAvatar from '@admin/components/UserAvatar.vue'

const mainStore = useMainStore()
</script>

<template>
  <UserAvatar :username="mainStore.userName" :avatar="mainStore.userAvatar">
    <slot />
  </UserAvatar>
</template>
