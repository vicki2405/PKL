<script setup>
import SectionFullScreen from '@admin/components/SectionFullScreen.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import LayoutGuest from '@admin/layouts/LayoutGuest.vue'
</script>

<template>
  <LayoutGuest>
    <SectionFullScreen v-slot="{ cardClass }" bg="pinkRed">
      <CardBox :class="cardClass">
        <div class="space-y-3">
          <h1 class="text-2xl">Unhandled exception</h1>

          <p>An Error Occurred</p>
        </div>

        <template #footer>
          <BaseButtons>
            <BaseButton label="Done" to="/dashboard" color="danger" />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionFullScreen>
  </LayoutGuest>
</template>
