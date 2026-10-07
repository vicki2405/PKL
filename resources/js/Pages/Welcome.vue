<script setup>
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/LandingPage/Header.vue";
import Hero from "@/Components/LandingPage/Hero.vue";
import Feature from "@/Components/LandingPage/Feature.vue";
import FlowSection from "@/Components/LandingPage/FlowSection.vue";
import { defineAsyncComponent } from 'vue';

const MapSection = defineAsyncComponent(() =>
  import("@/Components/LandingPage/MapSection.vue")
);
import BengkelTable from "@/Components/LandingPage/BengkelTable.vue";
import Subscribe from "@/Components/LandingPage/Subscribe.vue";
import Footer from "@/Components/LandingPage/Footer.vue";

defineProps({
  canLogin: {
    type: Boolean,
  },
  laravelVersion: {
    type: String,
    default: null,
  },
  phpVersion: {
    type: String,
    default: null,
  },
  mapData: {
    type: Array,
    default: () => [],
  },
  tableData: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <Head title="Sistem Informasi PKL" />
  <div class="bg-white-300 pb-24 lg:pb-0">
    <Header />
    <Hero />
    <Feature />
    <FlowSection />
    <MapSection :locations="mapData" />
    <BengkelTable :bengkels="tableData" />
    <Footer />
  </div>
</template>
