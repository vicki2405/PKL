<script setup>
import { computed, ref, onMounted, nextTick, watch, onUnmounted } from "vue";
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LTooltip } from "@vue-leaflet/vue-leaflet";
import L from "leaflet";

const props = defineProps({
  locations: {
    type: Array,
    default: () => [],
  },
});

// Lazy Loading State
const isMapVisible = ref(false);
const mapContainerRef = ref(null);
let observer = null;

// Default center (Indonesia) jika data kosong
const center = ref([-2.5489, 118.0149]);
const zoom = ref(5);
const map = ref(null);

// Custom Pulsing Icon
const pulseIconRed = L.divIcon({
  className: 'custom-div-icon',
  html: "<div class='pulse-marker pulse-marker--red'></div>",
  iconSize: [12, 12],
  iconAnchor: [6, 6]
});

const pulseIconGreen = L.divIcon({
  className: 'custom-div-icon',
  html: "<div class='pulse-marker pulse-marker--green'></div>",
  iconSize: [12, 12],
  iconAnchor: [6, 6]
});

const pulseIconBlue = L.divIcon({
  className: 'custom-div-icon',
  html: "<div class='pulse-marker pulse-marker--blue'></div>",
  iconSize: [12, 12],
  iconAnchor: [6, 6]
});

const getIconForStudentsCount = (studentsCount) => {
  const count = Number(studentsCount);
  const normalized = Number.isFinite(count) ? count : 0;

  if (normalized === 0) return pulseIconGreen;
  if (normalized >= 1 && normalized <= 3) return pulseIconBlue;
  return pulseIconRed;
};

// Hitung bounds agar peta otomatis mencakup semua titik
const bounds = computed(() => {
  if (props.locations.length === 0) return null;

  const lats = props.locations.map(l => parseFloat(l.lat)).filter(l => !isNaN(l));
  const lngs = props.locations.map(l => parseFloat(l.lng)).filter(l => !isNaN(l));

  if (lats.length === 0 || lngs.length === 0) return null;

  const minLat = Math.min(...lats);
  const maxLat = Math.max(...lats);
  const minLng = Math.min(...lngs);
  const maxLng = Math.max(...lngs);

  // Tambahkan padding
  const latPad = (maxLat - minLat) * 0.2 || 0.05; // Increased padding
  const lngPad = (maxLng - minLng) * 0.2 || 0.05;

  return [
    [minLat - latPad, minLng - lngPad],
    [maxLat + latPad, maxLng + lngPad]
  ];
});

// Indonesia Bounds
const indonesiaBounds = [
  [-11.3, 94.5], // South West
  [6.9, 141.6], // North East
];

// Opsi Peta (Interaksi diaktifkan kembali sesuai request)
const mapOptions = {
  zoomControl: true,
  dragging: true,
  touchZoom: true,
  doubleClickZoom: true,
  scrollWheelZoom: true,
  boxZoom: true,
  keyboard: true,
  attributionControl: false,
  maxBounds: indonesiaBounds,
  maxBoundsViscosity: 1.0,
  minZoom: 4,
};

// Paksa fitBounds saat mounted atau data berubah
const fitMapBounds = async () => {
  await nextTick();
  if (map.value && map.value.leafletObject) {
    if (bounds.value) {
      // Jika ada data, sesuaikan zoom ke titik-titik tersebut
      map.value.leafletObject.fitBounds(bounds.value, { padding: [50, 50], maxZoom: 12 });
    } else {
      // Jika data kosong, fokus ke seluruh Indonesia
      map.value.leafletObject.fitBounds(indonesiaBounds);
    }
  }
};

onMounted(() => {
  // Intersection Observer untuk Lazy Load
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      isMapVisible.value = true;
      observer.disconnect(); // Stop observing once loaded
      fitMapBounds();
    }
  }, {
    rootMargin: "200px" // Load 200px before visible
  });

  if (mapContainerRef.value) {
    observer.observe(mapContainerRef.value);
  }
});

onUnmounted(() => {
  if (observer) observer.disconnect();
});

watch(() => props.locations, () => {
  if (isMapVisible.value) {
    fitMapBounds();
  }
}, { deep: true });

const openMap = (url) => {
  if (url) window.open(url, '_blank');
};
</script>

<template>
  <div class="bg-gray-50 py-20 scroll-mt-24" id="map-section">
    <div class="max-w-screen-xl px-6 sm:px-8 lg:px-16 mx-auto flex flex-col w-full">

      <!-- Header Section -->
      <div class="flex flex-col w-full mb-12 text-center">
        <h3 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-4">
          Peta Sebaran Lokasi PKL
        </h3>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Pantau sebaran lokasi Bengkel dan Dunia Usaha/Industri (DUDI) tempat siswa melaksanakan Praktik Kerja Lapangan.
        </p>
      </div>

      <!-- Modern Map Container -->
      <div class="relative w-full h-[400px] sm:h-[500px] lg:h-[600px] rounded-3xl overflow-hidden shadow-2xl border border-gray-200 bg-white" ref="mapContainerRef">

        <l-map
          v-if="isMapVisible && locations.length > 0"
          ref="map"
          :zoom="zoom"
          :center="center"
          :options="mapOptions"
          :use-global-leaflet="false"
          class="z-0 h-full w-full"
          @ready="fitMapBounds"
        >
          <!-- Tile Layer (OpenStreetMap) -->
          <l-tile-layer
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            layer-type="base"
            name="OpenStreetMap"
            attribution="&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
          ></l-tile-layer>

          <!-- Titik Merah Pulsing -->
          <l-marker
            v-for="loc in locations"
            :key="loc.id"
            :lat-lng="[parseFloat(loc.lat), parseFloat(loc.lng)]"
            :icon="getIconForStudentsCount(loc.students_count)"
            @click="openMap(loc.link || loc.url)"
          >
            <l-tooltip :options="{ direction: 'top', offset: [0, -10] }">
              <div class="px-2 py-1">
                <div class="font-bold text-gray-900 text-sm">{{ loc.name }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ loc.students_count }} Siswa</div>
                <div class="text-xs text-red-500 mt-1 font-medium">Buka Maps &rarr;</div>
              </div>
            </l-tooltip>
          </l-marker>
        </l-map>

        <!-- Loading Placeholder -->
        <div v-else-if="!isMapVisible" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 text-gray-400 z-10 animate-pulse">
           <div class="w-16 h-16 bg-gray-200 rounded-full mb-4"></div>
           <div class="h-4 w-48 bg-gray-200 rounded"></div>
           <p class="mt-4 text-sm font-medium">Memuat Peta...</p>
        </div>

        <!-- Fallback State (No Data) -->
        <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 text-gray-400 z-10">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 01-.447-.894L15 7m0 13V7m0 0L9 4"></path></svg>
          </div>
          <p class="font-medium">Belum ada data lokasi bengkel yang terekam.</p>
        </div>
      </div>

      <!-- Stats Section (Moved Out) -->
      <div class="mt-8 flex justify-center w-full">
        <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex flex-col sm:flex-row items-center gap-6 max-w-2xl w-full animate-fade-in-up">
          <div class="flex items-center gap-4">
             <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center shrink-0">
               <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
             </div>
             <div>
               <p class="text-sm text-gray-500 font-medium uppercase tracking-wider">Total Lokasi Terdaftar</p>
               <h4 class="text-3xl font-bold text-gray-900">{{ locations.length }} <span class="text-lg font-normal text-gray-600">Bengkel / DUDI</span></h4>
             </div>
          </div>
          <div class="hidden sm:block w-px h-12 bg-gray-200"></div>
          <div class="flex-1 text-center sm:text-left">
             <p class="text-sm text-gray-600 leading-relaxed">
               Peta ini menampilkan sebaran lokasi tempat siswa melaksanakan PKL di seluruh Indonesia.
               <span class="text-red-600 font-medium block mt-1">*Klik titik untuk melihat detail di Google Maps</span>
             </p>
             <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-2 text-xs font-medium text-gray-600">
               <div class="flex items-center gap-2">
                 <span class="h-3 w-3 rounded-full bg-green-600"></span>
                 <span>Belum ditempati (0 siswa)</span>
               </div>
               <div class="flex items-center gap-2">
                 <span class="h-3 w-3 rounded-full bg-blue-600"></span>
                 <span>Ditempati 1–3 siswa</span>
               </div>
               <div class="flex items-center gap-2">
                 <span class="h-3 w-3 rounded-full bg-red-600"></span>
                 <span>Ditempati &gt; 3 siswa</span>
               </div>
             </div>


          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style>
.custom-div-icon {
  background: transparent;
  border: none;
}

.pulse-marker {
  background-color: var(--pulse-color);
  width: 12px;
  height: 12px;
  border-radius: 50%;
  position: relative;
  box-shadow: 0 0 0 0 var(--pulse-shadow);
  animation: pulse 2s infinite;
}

.pulse-marker--red {
  --pulse-color: #dc2626;
  --pulse-shadow: rgba(220, 38, 38, 0.7);
  --pulse-shadow-transparent: rgba(220, 38, 38, 0);
}

.pulse-marker--green {
  --pulse-color: #16a34a;
  --pulse-shadow: rgba(22, 163, 74, 0.7);
  --pulse-shadow-transparent: rgba(22, 163, 74, 0);
}

.pulse-marker--blue {
  --pulse-color: #2563eb;
  --pulse-shadow: rgba(37, 99, 235, 0.7);
  --pulse-shadow-transparent: rgba(37, 99, 235, 0);
}

@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 var(--pulse-shadow);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px var(--pulse-shadow-transparent);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 var(--pulse-shadow-transparent);
  }
}

.animate-fade-in-down {
  animation: fadeInDown 0.5s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Override leaflet styles if needed */
.leaflet-container {
    background-color: #f8fafc;
    z-index: 0;
}
</style>
