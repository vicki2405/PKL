<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'
import {
  Chart,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import { useDarkModeStore } from '@admin/stores/darkMode.js'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  subtext: {
    type: String,
    default: 'Siswa PKL'
  },
  variant: {
    type: String,
    default: 'placement', // 'placement', 'mutasi', 'default'
  }
})

const root = ref(null)
const darkModeStore = useDarkModeStore()

let chart

Chart.register(DoughnutController, ArcElement, Tooltip, Legend)

const centerTextPlugin = {
  id: 'centerText',
  afterDraw: function(chart) {
    if (chart.config.type !== 'doughnut') return;
    if (!chart.chartArea || !chart.config?.data?.datasets?.[0]?.data?.length) return;

    var width = chart.width,
        height = chart.height,
        ctx = chart.ctx;

    // Get chart area dimensions to find the true center of the donut
    const { top, bottom, left, right, width: areaWidth, height: areaHeight } = chart.chartArea;
    const centerX = (left + right) / 2;
    const centerY = (top + bottom) / 2;

    ctx.restore();

    // Responsive font size based on chart area height (donut size)
    // Smaller font for Mutasi because of thicker segments (smaller hole)
    var fontSize = (areaHeight / 160).toFixed(2);
    if (props.variant === 'mutasi') {
        fontSize = (areaHeight / 220).toFixed(2); // Reduced font size further for Mutasi
    }

    ctx.font = "bold " + fontSize + "em Inter, sans-serif";
    ctx.textBaseline = "middle";

    // Theme-aware color
    const isDark = document.documentElement.classList.contains('dark');
    ctx.fillStyle = isDark ? "#e2e8f0" : "#1e293b";

    // Calculate percentage (only for placement variant or if total > 0)
    let value = chart.config.data.datasets[0].data[0] || 0;
    let total = chart.config.data.datasets[0].data.reduce((a, b) => a + b, 0);
    let percentage = total > 0 ? Math.round((value / total) * 100) + "%" : "0%";

    var text = props.variant === 'placement' ? percentage : total;

    // Measure text width for centering
    var textX = Math.round(centerX - (ctx.measureText(text).width / 2));

    // Calculate vertical positioning to center the group (Text + Subtext)
    // Base separation on chart area height
    var verticalSeparation = areaHeight / 15;

    // Move main text up
    var textY = centerY - (verticalSeparation / 1.5);

    ctx.fillText(text, textX, textY);

    // Subtext
    var subFontSize = (fontSize * 0.45).toFixed(2); // Slightly larger relative to main font
    ctx.font = "500 " + subFontSize + "em Inter, sans-serif";
    ctx.fillStyle = isDark ? "#94a3b8" : "#64748b";
    var subtext = props.subtext;
    var subtextX = Math.round(centerX - (ctx.measureText(subtext).width / 2));

    // Move subtext down
    var subtextY = centerY + (verticalSeparation / 1.2);

    ctx.fillText(subtext, subtextX, subtextY);

    ctx.save();
  }
};

watch(
  () => darkModeStore.isEnabled,
  (isEnabled) => {
    if (chart) {
      const textColor = isEnabled ? '#cbd5e1' : '#475569';

      // Update Legend Color
      if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
        chart.options.plugins.legend.labels.color = textColor;
      }

      chart.update();
    }
  }
)

onMounted(() => {
  const ctx = root.value.getContext('2d');

  if (props.variant === 'placement') {
              // Create Gradients for Placement
              const gradientGreen = ctx.createLinearGradient(0, 0, 0, 400);
              gradientGreen.addColorStop(0, '#10b981'); // Emerald 500
              gradientGreen.addColorStop(1, '#059669'); // Emerald 600

              const gradientRed = ctx.createLinearGradient(0, 0, 0, 400);
              gradientRed.addColorStop(0, '#f43f5e'); // Rose 500
              gradientRed.addColorStop(1, '#e11d48'); // Rose 600

              if (props.data.datasets && props.data.datasets.length > 0) {
                  props.data.datasets[0].backgroundColor = [gradientGreen, gradientRed];
                  props.data.datasets[0].hoverBackgroundColor = [gradientGreen, gradientRed];
                  props.data.datasets[0].borderRadius = 20;
                  props.data.datasets[0].borderWidth = 0;
                  props.data.datasets[0].cutout = '75%';
                  props.data.datasets[0].spacing = 5;
              }
          } else if (props.variant === 'mutasi') {
              // Solid Colors for Mutasi (Donut Style)
              // Menunggu (Yellow/Orange)
              const colorPending = '#f59e0b'; // Amber 500
              // Diproses (Blue)
              const colorProcessing = '#3b82f6'; // Blue 500
              // Disetujui (Green)
              const colorApproved = '#10b981'; // Emerald 500
              // Ditolak (Red)
              const colorRejected = '#ef4444'; // Red 500

              if (props.data.datasets && props.data.datasets.length > 0) {
                  props.data.datasets[0].backgroundColor = [colorPending, colorProcessing, colorApproved, colorRejected];
                  props.data.datasets[0].hoverBackgroundColor = [colorPending, colorProcessing, colorApproved, colorRejected];
                  props.data.datasets[0].borderRadius = 0; // No rounded corners
                  props.data.datasets[0].borderWidth = 0;
                  props.data.datasets[0].cutout = '50%'; // Thicker segments
                  props.data.datasets[0].spacing = 2; // Slight spacing
              }
          } else if (props.variant === 'bengkel') {
              // Solid Colors for Bengkel per Jurusan
              const colorTKR = '#10b981'; // Emerald 500
              const colorTSM = '#3b82f6'; // Blue 500
              const colorTEI = '#6366f1'; // Indigo 500
              const colorANS = '#a855f7'; // Purple 500
              const colorKLR = '#f97316'; // Orange 500
              const colorLain = '#71717a'; // Zinc 500

              if (props.data.datasets && props.data.datasets.length > 0) {
                  props.data.datasets[0].backgroundColor = [colorTKR, colorTSM, colorTEI, colorANS, colorKLR, colorLain];
                  props.data.datasets[0].hoverBackgroundColor = [colorTKR, colorTSM, colorTEI, colorANS, colorKLR, colorLain];
                  props.data.datasets[0].borderRadius = 0;
                  props.data.datasets[0].borderWidth = 0;
                  props.data.datasets[0].cutout = '50%';
                  props.data.datasets[0].spacing = 2;
              }
          }

          // Common styling fallback if not set by variant specific block
          if (props.data.datasets && props.data.datasets.length > 0 && !props.data.datasets[0].cutout) {
              props.data.datasets[0].borderRadius = 20;
              props.data.datasets[0].borderWidth = 0;
              props.data.datasets[0].cutout = '75%';
              props.data.datasets[0].spacing = 5;
          }

  chart = new Chart(root.value, {
    type: 'doughnut',
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: 20
      },
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 20,
            font: {
            family: 'Inter, sans-serif',
            size: 12
          },
          color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#475569',
          generateLabels: (chart) => {
            const datasets = chart.data.datasets;
            const meta = chart.getDatasetMeta(0);
            const total = meta.total;

            return chart.data.labels.map((label, i) => {
              const style = meta.controller.getStyle(i);
              const value = datasets[0].data[i];
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';

              // Handle gradients or solid colors
              let fillStyle = style.backgroundColor;
              if (Array.isArray(datasets[0].backgroundColor)) {
                fillStyle = datasets[0].backgroundColor[i];
              }

              return {
                text: `${label}: ${value} (${percentage})`,
                fillStyle: fillStyle,
                strokeStyle: style.borderColor,
                lineWidth: style.borderWidth,
                hidden: !chart.getDataVisibility(i),
                index: i
              };
            });
          }
        }
      },
      tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    let value = context.parsed;
                    let total = context.chart._metasets[context.datasetIndex].total;
                    let percentage = (value * 100 / total).toFixed(1) + "%";
                    return label + value + ' (' + percentage + ')';
                }
            }
        }
      },
    },
    plugins: [centerTextPlugin]
  })
})

const chartData = computed(() => props.data)

watch(chartData, (data) => {
  if (chart) {
    chart.data = data
    chart.update()
  }
}, { deep: true })

onBeforeUnmount(() => {
  if (chart) {
    chart.destroy()
    chart = null
  }
})
</script>

<template>
  <div class="relative w-full h-full min-h-[260px] flex items-center justify-center">
    <canvas ref="root" />
  </div>
</template>
