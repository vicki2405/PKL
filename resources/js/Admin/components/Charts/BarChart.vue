<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend
} from 'chart.js'
import { useDarkModeStore } from '@admin/stores/darkMode.js'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
})

const root = ref(null)
const darkModeStore = useDarkModeStore()

let chart

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

// Palet warna modern untuk tiap jurusan (TKR, TSM, TEI, Animasi, Kuliner, Lainnya)
const barColors = [
  '#10b981', // Emerald - TKR
  '#0ea5e9', // Sky - TSM
  '#6366f1', // Indigo - TEI
  '#8b5cf6', // Violet - Animasi
  '#f97316', // Orange - Kuliner
  '#64748b', // Slate - Lainnya
]

const barHoverColors = [
  '#059669',
  '#0284c7',
  '#4f46e5',
  '#7c3aed',
  '#ea580c',
  '#475569',
]

const getThemeColors = () => {
  const isDark = darkModeStore.isEnabled
  return {
    textColor: isDark ? '#cbd5e1' : '#475569',
    gridColor: isDark ? 'rgba(148, 163, 184, 0.08)' : 'rgba(226, 232, 240, 0.8)',
    borderColor: isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(203, 213, 225, 0.8)',
  }
}

const applyDatasetStyles = (data) => {
  if (data?.datasets && data.datasets.length > 0) {
    const ds = data.datasets[0]
    ds.backgroundColor = barColors.slice(0, data.labels?.length || 6)
    ds.hoverBackgroundColor = barHoverColors.slice(0, data.labels?.length || 6)
    ds.borderRadius = 10
    ds.borderSkipped = false
    ds.maxBarThickness = 48
  }
}

onMounted(() => {
  if (!root.value) return

  applyDatasetStyles(props.data)
  const theme = getThemeColors()

  chart = new Chart(root.value, {
    type: 'bar',
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 15,
          bottom: 5,
          left: 5,
          right: 5,
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: theme.textColor,
            font: {
              family: 'Inter, system-ui, sans-serif',
              size: 11,
              weight: '600',
            },
          },
          border: {
            color: theme.borderColor,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: theme.gridColor,
            drawBorder: false,
          },
          ticks: {
            color: theme.textColor,
            font: {
              family: 'Inter, system-ui, sans-serif',
              size: 11,
            },
            precision: 0,
          },
          border: {
            dash: [4, 4],
            color: theme.borderColor,
          },
        },
      },
      plugins: {
        legend: {
          display: false, // Hidden karena tiap batang sudah jelas label jurusannya di sumbu X
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.92)',
          titleColor: '#ffffff',
          bodyColor: '#e2e8f0',
          padding: 12,
          cornerRadius: 10,
          boxPadding: 4,
          displayColors: true,
          titleFont: {
            family: 'Inter, system-ui, sans-serif',
            weight: 'bold',
            size: 12,
          },
          bodyFont: {
            family: 'Inter, system-ui, sans-serif',
            size: 12,
          },
          callbacks: {
            label: function (context) {
              const val = context.raw || 0
              return ` Bengkel Mitra: ${val} Tempat`
            },
          },
        },
      },
    },
  })
})

// Watch Dark Mode
watch(
  () => darkModeStore.isEnabled,
  () => {
    if (!chart) return
    const theme = getThemeColors()
    if (chart.options.scales?.x?.ticks) chart.options.scales.x.ticks.color = theme.textColor
    if (chart.options.scales?.x?.border) chart.options.scales.x.border.color = theme.borderColor
    if (chart.options.scales?.y?.ticks) chart.options.scales.y.ticks.color = theme.textColor
    if (chart.options.scales?.y?.grid) chart.options.scales.y.grid.color = theme.gridColor
    if (chart.options.scales?.y?.border) chart.options.scales.y.border.color = theme.borderColor
    chart.update()
  }
)

// Watch Data Changes
watch(
  () => props.data,
  (newData) => {
    if (chart && newData) {
      applyDatasetStyles(newData)
      chart.data = newData
      chart.update()
    }
  },
  { deep: true }
)

onBeforeUnmount(() => {
  if (chart) {
    chart.destroy()
    chart = null
  }
})
</script>

<template>
  <div class="relative w-full h-full min-h-[280px]">
    <canvas ref="root" />
  </div>
</template>
