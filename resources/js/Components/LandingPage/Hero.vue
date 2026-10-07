<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import ButtonPrimary from "./ButtonPrimary.vue";
import { Motion } from "@motionone/vue";

const listUser = [
  {
    name: "Siswa Aktif",
    number: "500",
    icon: "/assets/Icon/heroicons_sm-user.svg",
  },
  {
    name: "Mitra Industri",
    number: "50",
    icon: "/assets/Icon/gridicons_location.svg",
  },
  {
    name: "Jurusan",
    number: "5",
    icon: "/assets/Icon/bx_bxs-server.svg",
  },
];

const scrollAnimation = {
  hidden: { opacity: 0, y: 150 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.8 } },
};
</script>

<template>
  <div class="max-w-screen-xl mt-24 px-6 sm:px-8 lg:px-16 mx-auto scroll-mt-24" id="about">
    <div
      class="grid grid-flow-row sm:grid-flow-col grid-rows-2 md:grid-rows-1 sm:grid-cols-2 gap-8 py-6 sm:py-16"
    >
      <div
        class="flex flex-col justify-center items-start row-start-2 sm:row-start-1"
      >
        <h1
          class="text-3xl lg:text-4xl xl:text-5xl font-medium text-black-600 leading-normal"
        >
          Sistem Informasi <strong>Praktik Kerja Lapangan</strong>.
        </h1>
        <p class="text-black-500 mt-4 mb-6 text-justify">
          Setiap langkah kecil yang kamu ambil hari ini adalah investasi besar untuk masa depanmu.
          Jangan takut mencoba, jangan ragu belajar, dan jangan berhenti berusaha.
          Ingat, kerja kerasmu sekarang akan menjadi cerita suksesmu nanti!
        </p>
        <div class="flex gap-4">
             <Link v-if="$page.props.auth?.user" :href="route('dashboard')">
                <ButtonPrimary>Buka Dashboard</ButtonPrimary>
             </Link>
             <Link v-else :href="route('login')">
                <ButtonPrimary>Masuk Sekarang</ButtonPrimary>
             </Link>
        </div>
      </div>
      <div class="flex w-full">
        <div class="h-full w-full">
          <img
            src="/assets/Illustration1.png"
            alt="VPN Illustrasi"
            width="612"
            height="383"
            class="w-full h-auto"
          />
        </div>
      </div>
    </div>
    <div class="relative w-full flex">
      <div
        class="rounded-lg w-full grid grid-flow-row sm:grid-flow-row grid-cols-1 sm:grid-cols-3 py-9 divide-y-2 sm:divide-y-0 sm:divide-x-2 divide-gray-ls-100 bg-white-500 z-10 shadow-lg"
      >
        <div
          v-for="(user, index) in listUser"
          :key="index"
          class="flex items-center justify-start sm:justify-center py-4 sm:py-6 w-full px-8 sm:w-auto mx-auto sm:mx-0"
        >
          <div class="flex mx-auto sm:w-auto items-center">
            <div
              class="flex items-center justify-center bg-orange-100 w-12 h-12 mr-6 rounded-full flex-shrink-0"
            >
              <img :src="user.icon" class="h-6 w-6" />
            </div>
            <div class="flex flex-col">
              <p class="text-xl text-black-600 font-bold">
                {{ user.number }}+
              </p>
              <p class="text-lg text-black-500">{{ user.name }}</p>
            </div>
          </div>
        </div>
      </div>
      <div
        class="absolute bg-black-600 opacity-5 w-11/12 rounded-lg h-64 sm:h-48 top-0 mt-8 mx-auto left-0 right-0"
        style="filter: blur(114px)"
      ></div>
    </div>
  </div>
</template>
