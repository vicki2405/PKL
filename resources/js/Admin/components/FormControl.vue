<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { useMainStore } from '@admin/stores/main'
import FormControlIcon from '@admin/components/FormControlIcon.vue'

const props = defineProps({
  name: {
    type: String,
    default: null,
  },
  id: {
    type: String,
    default: null,
  },
  autocomplete: {
    type: String,
    default: null,
  },
  maxlength: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: null,
  },
  inputmode: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  options: {
    type: Array,
    default: null,
  },
  type: {
    type: String,
    default: 'text',
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: '',
  },
  required: Boolean,
  borderless: Boolean,
  transparent: Boolean,
  ctrlKFocus: Boolean,
  disabled: Boolean,
})

const emit = defineEmits(['update:modelValue', 'setRef'])

const computedValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value)
  },
})

const inputElClass = computed(() => {
  const base = [
    'px-3.5 py-2.5 max-w-full focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 border-slate-200/90 dark:border-slate-700 rounded-xl sm:rounded-2xl w-full font-medium transition-all shadow-xs',
    'dark:placeholder-gray-400',
    computedType.value === 'textarea' ? 'h-24 sm:h-28' : 'h-11 sm:h-12',
    props.borderless ? 'border-0' : 'border',
    props.transparent ? 'bg-transparent' : 'bg-white dark:bg-slate-900',
    'text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500',
    'text-sm',
  ]

  if (props.icon) {
    base.push('pl-10')
  }

  // Jika ini elemen <select>, beri ruang lebih di kanan agar teks panjang tidak menimpa ikon panah dropdown
  if (computedType.value === 'select') {
    base.push('pr-8')
  }

  return base
})

const computedType = computed(() => (props.options ? 'select' : props.type))

const controlIconH = computed(() => (props.type === 'textarea' ? 'h-full' : 'h-12'))

const mainStore = useMainStore()

const selectEl = ref(null)

const textareaEl = ref(null)

const inputEl = ref(null)

onMounted(() => {
  if (selectEl.value) {
    emit('setRef', selectEl.value)
  } else if (textareaEl.value) {
    emit('setRef', textareaEl.value)
  } else {
    emit('setRef', inputEl.value)
  }
})

if (props.ctrlKFocus) {
  const fieldFocusHook = (e) => {
    if (e.ctrlKey && e.key === 'k') {
      e.preventDefault()
      inputEl.value.focus()
    } else if (e.key === 'Escape') {
      inputEl.value.blur()
    }
  }

  onMounted(() => {
    if (!mainStore.isFieldFocusRegistered) {
      window.addEventListener('keydown', fieldFocusHook)
      mainStore.isFieldFocusRegistered = true
    } else {
      // console.error('Duplicate field focus event')
    }
  })

  onBeforeUnmount(() => {
    window.removeEventListener('keydown', fieldFocusHook)
    mainStore.isFieldFocusRegistered = false
  })
}
</script>

<template>
  <div class="relative">
    <select
      v-if="computedType === 'select'"
      :id="id"
      v-model="computedValue"
      :name="name"
      :class="inputElClass"
      :disabled="disabled"
      :required="required"
    >
      <option v-if="placeholder" :value="null" disabled>
        {{ placeholder }}
      </option>
      <option
        v-for="option in options"
        :key="option.id ?? option"
        :value="option.value ?? option.id ?? option"
        :disabled="option.disabled ?? false"
      >
        {{ option.label ?? option }}
      </option>
    </select>
    <textarea
      v-else-if="computedType === 'textarea'"
      :id="id"
      v-model="computedValue"
      :class="inputElClass"
      :name="name"
      :maxlength="maxlength"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
    />
    <input
      v-else
      :id="id"
      ref="inputEl"
      v-model="computedValue"
      :name="name"
      :maxlength="maxlength"
      :inputmode="inputmode"
      :autocomplete="autocomplete"
      :required="required"
      :placeholder="placeholder"
      :type="computedType"
      :class="inputElClass"
      :disabled="disabled"
    />
    <FormControlIcon v-if="icon" :icon="icon" :h="controlIconH" />
  </div>
</template>
