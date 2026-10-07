<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const features = [
  "Pendaftaran & Penempatan Online",
  "Jurnal Kegiatan Harian Digital",
  "Monitoring Absensi Real-time",
  "Rekapitulasi Nilai Otomatis",
];
</script>

<template>
  <div
    class="max-w-screen-xl mt-8 mb-6 sm:mt-14 sm:mb-14 px-6 sm:px-8 lg:px-16 mx-auto"
    id="feature"
  >
    <div
      class="grid grid-flow-row sm:grid-flow-col grid-cols-1 sm:grid-cols-2 gap-8 py-8 my-12"
    >
      <div class="flex w-full justify-end">
        <div class="h-full w-full p-4">
          <img
            src="/assets/Illustration2.png"
            alt="VPN Illustrasi"
            class="w-full h-auto"
            width="508"
            height="414"
          />
        </div>
      </div>
      <div
        class="flex flex-col items-end justify-center ml-auto w-full lg:w-9/12"
      >
        <h3
          class="text-3xl lg:text-4xl font-medium leading-relaxed text-black-600"
        >
          Fitur Unggulan SIPKL
        </h3>
        <p class="my-2 text-black-500">
          Platform ini dirancang untuk memudahkan seluruh proses administrasi dan monitoring kegiatan Praktik Kerja Lapangan.
        </p>
        <ul class="text-black-500 self-start list-inside ml-4 sm:ml-8">
          <li
            v-for="feature in features"
            :key="feature"
            class="relative circle-check custom-list"
          >
            {{ feature }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
