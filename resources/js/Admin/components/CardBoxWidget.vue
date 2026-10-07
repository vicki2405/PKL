<script setup>
import { mdiCog } from '@mdi/js'
import CardBox from '@admin/components/CardBox.vue'
import NumberDynamic from '@admin/components/NumberDynamic.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import PillTagTrend from '@admin/components/PillTagTrend.vue'
import BaseButton from '@admin/components/BaseButton.vue'

defineProps({
  number: {
    type: Number,
    default: 0,
  },
  icon: {
    type: String,
    default: null,
  },
  prefix: {
    type: String,
    default: null,
  },
  suffix: {
    type: String,
    default: null,
  },
  label: {
    type: String,
    default: null,
  },
  color: {
    type: String,
    default: null,
  },
  trend: {
    type: String,
    default: null,
  },
  trendType: {
    type: String,
    default: null,
  },
})
</script>

<template>
  <CardBox class="p-1">
    <BaseLevel v-if="trend" class="mb-3" mobile>
      <PillTagTrend :trend="trend" :trend-type="trendType" small />
      <BaseButton :icon="mdiCog" icon-w="w-4" icon-h="h-4" color="lightDark" small />
    </BaseLevel>
    <BaseLevel mobile>
      <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ label }}
        </h3>
        <h1 class="text-3xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">
          <NumberDynamic :value="number" :prefix="prefix" :suffix="suffix" />
        </h1>
      </div>
      <div v-if="icon" class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700/50 shadow-xs flex items-center justify-center">
        <BaseIcon :path="icon" size="32" :class="color" />
      </div>
    </BaseLevel>
  </CardBox>
</template>
