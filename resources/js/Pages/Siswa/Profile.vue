<script setup>
import { ref, computed } from 'vue';
import { usePage, Head, useForm, Link } from '@inertiajs/vue3';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import {
    mdiAccount,
    mdiAccountSchool,
    mdiCardAccountDetails,
    mdiMapMarker,
    mdiPhone,
    mdiEmail,
    mdiCamera,
    mdiContentSave,
    mdiArrowLeft,
    mdiSchool,
    mdiIdentifier,
    mdiHomeMapMarker,
    mdiCityVariant,
    mdiMapLegend
} from '@mdi/js';
import FormField from '@admin/components/FormField.vue';
import FormControl from '@admin/components/FormControl.vue';
import BaseButton from '@admin/components/BaseButton.vue';
import BaseIcon from '@admin/components/BaseIcon.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    siswa: Object,
});

const user = computed(() => usePage().props.auth.user || {});
const siswa = computed(() => props.siswa);
const photoInput = ref(null);
const photoPreview = ref(null);
const imgLoadError = ref(false);

const form = useForm({
    _method: 'POST',
    email: usePage().props.auth.user?.email || '',
    no_hp: props.siswa?.no_hp || '',
    alamat: props.siswa?.alamat || '',
    rt: props.siswa?.rt || '',
    rw: props.siswa?.rw || '',
    dusun: props.siswa?.dusun || '',
    kelurahan: props.siswa?.kelurahan || '',
    kecamatan: props.siswa?.kecamatan || '',
    photo: null,
});

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    imgLoadError.value = false;
    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
    form.photo = photo;
};

const submit = () => {
    if (photoInput.value && photoInput.value.files[0]) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('siswa.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            imgLoadError.value = false;
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
    photoPreview.value = null;
};
</script>

<template>
    <Head title="Profil Saya" />

    <SiswaLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 rounded-3xl p-5 sm:p-8 overflow-hidden shadow-xl text-white border border-slate-800">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-orange-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                            <BaseIcon :path="mdiAccount" size="28" class="text-orange-400" />
                            <span>Profil & Biodata Siswa</span>
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                            Perbarui foto 3x4, nomor WhatsApp, dan alamat tempat tinggal kamu.
                        </p>
                    </div>

                    <Link
                        :href="route('siswa.dashboard')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl backdrop-blur-sm transition-all text-white font-bold text-xs active:scale-95"
                    >
                        <BaseIcon :path="mdiArrowLeft" size="16" />
                        <span>Kembali</span>
                    </Link>
                </div>
            </div>

            <!-- Flash Alert -->
            <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm text-sm font-semibold">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ $page.props.flash.message }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

                <!-- Left Column: 3x4 Photo Card -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-8">
                        <div class="h-28 bg-gradient-to-r from-slate-900 to-orange-950 relative">
                            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2">
                                <div class="relative group">
                                    <div class="w-28 h-36 sm:w-32 sm:h-40 rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-slate-800">
                                        <!-- Photo Preview -->
                                        <img
                                            v-if="photoPreview"
                                            :src="photoPreview"
                                            class="w-full h-full object-cover"
                                        />
                                        <!-- Current Photo -->
                                        <img
                                            v-else-if="!imgLoadError && (siswa?.profile_photo_url || user.profile_photo_url)"
                                            :src="siswa?.profile_photo_url || user.profile_photo_url"
                                            @error="imgLoadError = true"
                                            class="w-full h-full object-cover"
                                        />
                                        <!-- Default Icon -->
                                        <div v-else class="w-full h-full flex items-center justify-center bg-slate-700 text-slate-400 font-bold text-3xl">
                                            {{ user.name?.charAt(0) || 'S' }}
                                        </div>
                                    </div>

                                    <!-- Camera Upload Button -->
                                    <button
                                        type="button"
                                        @click="selectNewPhoto"
                                        class="absolute bottom-2 right-2 p-2.5 bg-orange-500 hover:bg-orange-600 rounded-full shadow-lg text-white transition-transform hover:scale-110 active:scale-95 border-2 border-white"
                                        title="Ubah Foto 3x4"
                                    >
                                        <BaseIcon :path="mdiCamera" size="18" />
                                    </button>
                                    <input ref="photoInput" type="file" class="hidden" accept="image/*" @change="updatePhotoPreview">
                                </div>
                            </div>
                        </div>

                        <div class="pt-24 pb-6 px-5 sm:px-6 text-center">
                            <!-- Photo Error Message -->
                            <div v-if="form.errors.photo" class="mb-3 px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-semibold">
                                {{ form.errors.photo }}
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-slate-800">{{ user.name }}</h3>
                            <p class="text-slate-500 text-xs mt-0.5">{{ user.email }}</p>

                            <div class="mt-4 flex flex-wrap justify-center gap-2">
                                <span class="px-3 py-1 bg-orange-50 text-orange-700 text-[11px] font-bold rounded-full border border-orange-100">
                                    {{ siswa?.kelas?.nama_kelas || 'Tanpa Kelas' }}
                                </span>
                                <span class="px-3 py-1 bg-amber-50 text-amber-700 text-[11px] font-bold rounded-full border border-amber-100">
                                    {{ siswa?.kelas?.jurusan?.nama_jurusan || 'Tanpa Jurusan' }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold flex items-center gap-1.5">
                                    <BaseIcon :path="mdiIdentifier" size="14" />
                                    NISN
                                </span>
                                <span class="font-bold text-slate-800 font-mono">{{ siswa?.nisn || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-semibold flex items-center gap-1.5">
                                    <BaseIcon :path="mdiIdentifier" size="14" />
                                    NIPD
                                </span>
                                <span class="font-bold text-slate-800 font-mono">{{ siswa?.nipd || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Form Inputs -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submit">

                        <!-- Kontak & Akun Card -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
                                <BaseIcon :path="mdiCardAccountDetails" size="20" class="text-orange-500" />
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base">Informasi Kontak & Akun</h3>
                            </div>
                            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                <FormField label="Email" help="Email terdaftar">
                                    <FormControl v-model="form.email" :icon="mdiEmail" type="email" required />
                                    <div v-if="form.errors.email" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.email }}</div>
                                </FormField>

                                <FormField label="No. Handphone / WhatsApp" help="Nomor aktif untuk kontak pembimbing">
                                    <FormControl v-model="form.no_hp" :icon="mdiPhone" type="tel" placeholder="08..." />
                                    <div v-if="form.errors.no_hp" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.no_hp }}</div>
                                </FormField>
                            </div>
                        </div>

                        <!-- Alamat Domisili Card -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
                                <BaseIcon :path="mdiHomeMapMarker" size="20" class="text-orange-500" />
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base">Alamat Domisili Siswa</h3>
                            </div>
                            <div class="p-5 sm:p-6 space-y-5">
                                <FormField label="Alamat Rumah Lengkap" help="Nama jalan, nomor rumah, atau patokan">
                                    <FormControl v-model="form.alamat" type="textarea" placeholder="Jl. Mawar No. 12..." />
                                    <div v-if="form.errors.alamat" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.alamat }}</div>
                                </FormField>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                    <FormField label="RT / RW">
                                        <div class="flex items-center gap-2">
                                            <FormControl v-model="form.rt" placeholder="RT 001" class="w-1/2" />
                                            <span class="text-slate-400 font-bold">/</span>
                                            <FormControl v-model="form.rw" placeholder="RW 002" class="w-1/2" />
                                        </div>
                                    </FormField>

                                    <FormField label="Dusun">
                                        <FormControl v-model="form.dusun" :icon="mdiMapLegend" placeholder="Nama Dusun" />
                                    </FormField>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                    <FormField label="Desa / Kelurahan">
                                        <FormControl v-model="form.kelurahan" :icon="mdiCityVariant" placeholder="Nama Desa" />
                                    </FormField>

                                    <FormField label="Kecamatan">
                                        <FormControl v-model="form.kecamatan" :icon="mdiCityVariant" placeholder="Nama Kecamatan" />
                                    </FormField>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Submit Button -->
                        <div class="bg-slate-50/90 backdrop-blur border-t border-slate-100 p-4 rounded-2xl flex items-center justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm rounded-xl hover:from-orange-600 hover:to-amber-600 transition-all shadow-lg shadow-orange-500/20 active:scale-95"
                                :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                            >
                                <BaseIcon :path="mdiContentSave" size="18" />
                                <span>Simpan Profil</span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </SiswaLayout>
</template>
