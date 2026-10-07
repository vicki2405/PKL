<script setup>
import { computed, ref, watch, nextTick } from 'vue'
import { onClickOutside } from '@vueuse/core'
import FormControlIcon from '@admin/components/FormControlIcon.vue'
import { mdiChevronDown, mdiClose } from '@mdi/js'
import BaseIcon from '@admin/components/BaseIcon.vue'

const props = defineProps({
  options: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: [Object, String, Number],
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Search...',
  },
  icon: {
    type: String,
    default: null,
  },
  borderless: Boolean,
  transparent: Boolean,
  disabled: Boolean,
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const search = ref('')
const inputRef = ref(null)
const listRef = ref(null)
const rootRef = ref(null)

// Handle click outside to close dropdown
onClickOutside(rootRef, () => {
  isOpen.value = false
})

// Filter options based on search input
const filteredOptions = computed(() => {
  if (!search.value) {
    return props.options
  }
  const searchLower = search.value.toLowerCase()
  return props.options.filter((option) => {
    const label = option.label || option.nama || option.nama_bengkel || ''
    return String(label).toLowerCase().includes(searchLower)
  })
})

// Display label for the selected value
const selectedLabel = computed(() => {
  if (!props.modelValue) return ''
  return props.modelValue.label || props.modelValue.nama || props.modelValue.nama_bengkel || ''
})

// Sync search with selected value when closed
watch(isOpen, (val) => {
  if (val) {
    search.value = ''
    nextTick(() => {
      inputRef.value?.focus()
    })
  }
})

const selectOption = (option) => {
  emit('update:modelValue', option)
  isOpen.value = false
  search.value = ''
}

const clearSelection = () => {
  emit('update:modelValue', null)
  search.value = ''
}

const inputClass = computed(() => {
  const base = [
    'px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full',
    'dark:placeholder-gray-400',
    'h-12',
    props.borderless ? 'border-0' : 'border',
    props.transparent ? 'bg-transparent' : 'bg-white dark:bg-slate-800',
    'text-black dark:text-white placeholder-gray-500 dark:placeholder-gray-400',
    props.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
    'flex items-center',
    props.modelValue ? 'pr-16' : 'pr-10',
  ]

  if (props.icon) {
    base.push('pl-10')
  }

  return base
})

const controlIconH = 'h-12'
</script>

<template>
  <div ref="rootRef" class="relative">
    <!-- Selected Value Display (Simulates Input) -->
    <div
      :class="['relative', inputClass]"
      role="button"
      :aria-disabled="disabled ? 'true' : 'false'"
      @click="disabled ? null : (isOpen = !isOpen)"
    >
      <div class="min-w-0 flex-1">
        <span v-if="modelValue" class="block truncate">{{ selectedLabel }}</span>
        <span v-else class="block text-gray-500 dark:text-gray-400 truncate">{{ placeholder }}</span>
      </div>

      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center">
        <button
            v-if="modelValue"
            @click.stop="clearSelection"
            class="mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            :disabled="disabled"
        >
            <BaseIcon :path="mdiClose" size="18" />
        </button>
        <BaseIcon :path="mdiChevronDown" size="20" class="text-gray-500" />
      </div>
    </div>

    <FormControlIcon v-if="icon" :icon="icon" :h="controlIconH" />

    <!-- Dropdown -->
    <div
      v-if="isOpen && !disabled"
      class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-md shadow-lg max-h-60 overflow-hidden flex flex-col"
    >
      <!-- Search Input -->
      <div class="p-2 border-b border-gray-200 dark:border-slate-700">
        <input
          ref="inputRef"
          v-model="search"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-slate-900 dark:text-white"
          placeholder="Cari..."
          @keydown.esc="isOpen = false"
        />
      </div>

      <!-- Options List -->
      <ul ref="listRef" class="overflow-y-auto flex-1">
        <li
          v-for="option in filteredOptions"
          :key="option.id"
          class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200"
          :class="{ 'bg-blue-50 dark:bg-slate-700 font-semibold': modelValue && modelValue.id === option.id }"
          @click="selectOption(option)"
        >
          {{ option.label || option.nama || option.nama_bengkel }}
        </li>
        <li
          v-if="filteredOptions.length === 0"
          class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 italic"
        >
          Tidak ada data ditemukan
        </li>
      </ul>
    </div>
  </div>
</template>
