<script setup>
import ButtonPrimary from "./ButtonPrimary.vue";
import { Motion } from "@motionone/vue";
import { Link } from "@inertiajs/vue3";

const scrollAnimation = {
  hidden: { opacity: 0, y: 150 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.8 } },
};
</script>

<template>
  <div class="relative w-full">
    <Motion
      initial="hidden"
      in-view="visible"
      :variants="scrollAnimation"
      viewport-once
    >
      <div
        class="absolute bg-white-500 rounded-xl py-8 sm:py-14 px-6 sm:px-12 lg:px-16 w-full flex flex-col sm:flex-row justify-between items-center z-10 shadow-lg"
      >
        <div
          class="flex flex-col text-left w-10/12 sm:w-7/12 lg:w-5/12 mb-6 sm:mb-0"
        >
          <h5
            class="text-black-600 text-xl sm:text-2xl lg:text-3xl leading-relaxed font-medium"
          >
            Akses Sistem Informasi <br />PKL Sekarang!
          </h5>
          <p>Kelola data PKL Anda dengan mudah, cepat, dan efisien.</p>
        </div>
        <div class="flex gap-4">
             <Link :href="route('login')">
                <ButtonPrimary>Login Sekarang</ButtonPrimary>
             </Link>
        </div>
      </div>
      <div
        class="absolute bg-black-600 opacity-5 w-11/12 rounded-lg h-60 sm:h-56 top-0 mt-8 mx-auto left-0 right-0"
        style="filter: blur(114px)"
      ></div>
    </Motion>
  </div>
</template>
