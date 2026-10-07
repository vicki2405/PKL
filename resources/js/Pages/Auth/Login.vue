<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { mdiSchool, mdiLaptop, mdiBookOpenVariant, mdiCodeBraces, mdiAccountGroup, mdiShapeOutline, mdiStarFourPoints, mdiCircleOutline } from '@mdi/js';
import BottomNav from '@/Components/LandingPage/BottomNav.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const icons = [
    { path: mdiSchool, size: 64, top: '15%', left: '10%', duration: '8s', delay: '0s' },
    { path: mdiLaptop, size: 48, top: '75%', left: '85%', duration: '10s', delay: '2s' },
    { path: mdiBookOpenVariant, size: 56, top: '25%', left: '80%', duration: '12s', delay: '1s' },
    { path: mdiCodeBraces, size: 40, top: '80%', left: '15%', duration: '9s', delay: '3s' },
    { path: mdiAccountGroup, size: 52, top: '10%', left: '50%', duration: '11s', delay: '0.5s' },
    { path: mdiShapeOutline, size: 80, top: '60%', left: '5%', duration: '15s', delay: '0s' },
    { path: mdiStarFourPoints, size: 32, top: '40%', left: '90%', duration: '7s', delay: '4s' },
    { path: mdiCircleOutline, size: 100, top: '90%', left: '40%', duration: '20s', delay: '2s' },
];
</script>

<template>
    <Head title="Log in" />

    <div class="login-page-body">
        <!-- Animated Background Elements -->
        <div class="background-elements">
            <div class="gradient-blob blob-1"></div>
            <div class="gradient-blob blob-2"></div>
            <div class="gradient-blob blob-3"></div>

            <!-- Floating Icons -->
            <div
                v-for="(icon, index) in icons"
                :key="index"
                class="floating-icon"
                :style="{
                    top: icon.top,
                    left: icon.left,
                    animationDuration: icon.duration,
                    animationDelay: icon.delay,
                    width: icon.size + 'px',
                    height: icon.size + 'px'
                }"
            >
                <svg viewBox="0 0 24 24" :width="icon.size" :height="icon.size" fill="rgba(255, 255, 255, 0.1)">
                    <path :d="icon.path" />
                </svg>
            </div>
        </div>

        <div class="login-main-layout">
            <div class="illustration-section">
                <img src="/assets/Illustration1.png" alt="Education Illustration" class="floating-illustration">
                <div class="illustration-text">
                    <h1>Sistem Informasi<br>PKL & Magang</h1>
                    <p> HUMAS SMKS MUHAMMADIYAH 2 GENTENG ( STEMDA )</p>
                </div>
            </div>

            <div class="login-container">
                <div class="login-card">
                <div class="login-header">
                    <h2>HUMAS STEMDA</h2>
                    <p>login sesuai Username Dan Password</p>
                    <p> Jika Belum memiliki Akun <br>Segera Mengghubungi Admin Humas</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="login-form" novalidate>
                    <div class="form-group" :class="{ 'error': form.errors.login }">
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="login"
                                v-model="form.login"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Email / NIS / Username"
                            >
                            <label for="login">Email / NIS / Username</label>
                            <span class="focus-border"></span>
                        </div>
                        <span class="error-message show" v-if="form.errors.login">{{ form.errors.login }}</span>
                    </div>

                    <div class="form-group" :class="{ 'error': form.errors.password }">
                        <div class="input-wrapper password-wrapper">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="Password"
                            >
                            <label for="password">Password</label>
                            <button
                                type="button"
                                class="password-toggle"
                                @click="togglePassword"
                                aria-label="Toggle password visibility"
                            >
                                <span class="eye-icon" :class="{ 'show-password': showPassword }"></span>
                            </button>
                            <span class="focus-border"></span>
                        </div>
                        <span class="error-message show" v-if="form.errors.password">{{ form.errors.password }}</span>
                    </div>

                    <div class="form-options">
                        <label class="remember-wrapper">
                            <input type="checkbox" id="remember" v-model="form.remember">
                            <span class="checkbox-label">
                                <span class="checkmark"></span>
                                Remember me
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="login-btn btn" :class="{ 'loading': form.processing }" :disabled="form.processing">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loader"></span>
                    </button>

                    <!-- Enhanced Back to Home Button -->
                    <div class="mt-4 text-center">
                        <Link href="/" class="inline-flex items-center justify-center gap-2 text-sm text-gray-400 hover:text-orange-500 transition-colors duration-300 group py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Beranda</span>
                        </Link>
                    </div>
    </form>
            </div>
        </div>
    </div>
</div>

    <BottomNav />
</template>

<style scoped>
/* Glassmorphism Login Form - Complete & Self-Contained */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.login-page-body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    padding-bottom: 100px; /* Add padding for bottom nav on mobile */
    line-height: 1.6;
    position: relative;
    overflow: hidden;
}

@media (min-width: 1024px) {
    .login-page-body {
        padding-bottom: 20px; /* Reset padding on desktop */
    }
}

.background-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.gradient-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: floatBlob 20s infinite alternate;
}

.blob-1 {
    width: 400px;
    height: 400px;
    background: #3b82f6;
    top: -100px;
    left: -100px;
    animation-delay: 0s;
}

.blob-2 {
    width: 300px;
    height: 300px;
    background: #06b6d4;
    bottom: -50px;
    right: -50px;
    animation-delay: -5s;
}

.blob-3 {
    width: 200px;
    height: 200px;
    background: #8b5cf6;
    top: 40%;
    left: 60%;
    animation-delay: -10s;
}

.floating-icon {
    position: absolute;
    opacity: 0.15;
    animation: floatIcon 10s ease-in-out infinite;
    z-index: 2;
    pointer-events: none;
}

.floating-icon svg {
    fill: rgba(255, 255, 255, 0.2);
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.1));
}

@keyframes floatBlob {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, 50px) scale(1.1); }
    100% { transform: translate(-20px, -30px) scale(0.9); }
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.login-main-layout {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 40px;
    width: 100%;
    max-width: 1200px;
    z-index: 10;
    position: relative;
}

@media (min-width: 1024px) {
    .login-main-layout {
        flex-direction: row;
        gap: 60px;
        align-items: center;
    }
}

.illustration-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 100%;
    max-width: 500px;
}

.floating-illustration {
    width: 100%;
    max-width: 450px;
    height: auto;
    filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
    animation: floatMainImage 6s ease-in-out infinite;
    margin-bottom: 24px;
}

.illustration-text {
    color: white;
    text-shadow: 0 4px 10px rgba(0,0,0,0.5);
}

.illustration-text h1 {
    font-size: 32px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    -webkit-text-fill-color: transparent;
}

.illustration-text p {
    font-size: 18px;
    color: #e2e8f0;
    font-weight: 500;
}

@keyframes floatMainImage {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.login-container {
    width: 100%;
    max-width: 420px;
    perspective: 1000px;
    z-index: 10;
    position: relative;
}

.login-card {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    position: relative;
}

.login-header {
    text-align: center;
    margin-bottom: 32px;
}

.login-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.login-header p {
    color: #94a3b8;
    font-size: 15px;
}

.input-wrapper input {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px 16px 16px 16px;
    color: white;
    font-size: 15px;
    font-weight: 400;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    outline: none;
}

.input-wrapper input:focus {
    border-color: #3b82f6;
    background: rgba(15, 23, 42, 0.8);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.input-wrapper label {
    display: none; /* Simplify for cleaner look */
}

.login-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 24px;
    position: relative;
    overflow: hidden;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
}

.login-btn:active {
    transform: translateY(0);
}

/* Additional Styles for Form Elements */
.form-group {
    margin-bottom: 24px;
    position: relative;
}

.input-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
}

/* Password Toggle */
.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: rgba(255, 255, 255, 0.5);
    transition: color 0.3s;
}

.password-toggle:hover {
    color: white;
}

.eye-icon {
    display: block;
    width: 20px;
    height: 20px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z' /%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' /%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
}

.eye-icon.show-password {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' /%3E%3C/svg%3E");
}

/* Form Options (Remember Me & Forgot Password) */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    font-size: 14px;
}

.remember-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #94a3b8;
}

.remember-wrapper input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    margin-right: 8px;
    position: relative;
    transition: all 0.2s;
}

.remember-wrapper input:checked + .checkbox-label .checkmark {
    background: #3b82f6;
    border-color: #3b82f6;
}

.remember-wrapper input:checked + .checkbox-label .checkmark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 1px;
    width: 4px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.forgot-password {
    color: #3b82f6;
    text-decoration: none;
    transition: color 0.2s;
}

.forgot-password:hover {
    color: #60a5fa;
    text-decoration: underline;
}

/* Error Message */
.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
    display: block;
}

/* Loading State */
.btn.loading {
    pointer-events: none;
}

.btn-text {
    transition: opacity 0.3s ease;
}

.btn-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    opacity: 0;
    animation: spin 1s linear infinite;
    transition: opacity 0.3s ease;
}

.btn.loading .btn-text {
    opacity: 0;
}

.btn.loading .btn-loader {
    opacity: 1;
}

/* Checkbox Styles */
.remember-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.remember-wrapper input[type="checkbox"] {
    display: none;
}

.checkbox-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.remember-wrapper input[type="checkbox"]:checked ~ .checkbox-label .checkmark {
    background: #06b6d4;
    border-color: #06b6d4;
    transform: scale(1.1);
}

.remember-wrapper input[type="checkbox"]:checked ~ .checkbox-label .checkmark::after {
    content: '✓';
    color: white;
    font-size: 11px;
    font-weight: bold;
    animation: checkmarkPop 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Animations */
@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

@keyframes checkmarkPop {
    0% { transform: scale(0); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.login-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transform: translateY(0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #06b6d4, transparent);
    transition: left 0.5s ease;
}

.login-card:hover::before {
    left: 100%;
}

.login-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.login-header {
    text-align: center;
    margin-bottom: 32px;
}

.login-header h2 {
    color: white;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.login-header p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    font-weight: 400;
}

/* Password Toggle Specific Styles */
.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 48px;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: rgba(255, 255, 255, 0.6);
    transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 2;
}

.password-toggle:hover {
    color: rgba(255, 255, 255, 0.9);
}

.eye-icon {
    display: block;
    width: 20px;
    height: 20px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='rgba(255,255,255,0.6)' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/%3e%3c/svg%3e");
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    transition: background-image 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.eye-icon.show-password {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='rgba(255,255,255,0.6)' stroke-width='1.5'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88'/%3e%3c/svg%3e");
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 12px;
}

.forgot-password {
    color: #06b6d4;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.forgot-password::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #06b6d4;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.forgot-password:hover::after {
    width: 100%;
}

.forgot-password:hover {
    color: white;
}

/* Login Button */
.login-btn {
    width: 100%;
    margin-bottom: 24px;
}

/* Divider */
.divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
}

.divider::before {
    margin-right: 16px;
}

.divider::after {
    margin-left: 16px;
}

.divider span {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    white-space: nowrap;
}

/* Social Login */
.social-login {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.social-btn {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 12px 16px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.social-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.social-icon {
    width: 20px;
    height: 20px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}

.google-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3e%3cpath fill='%23ea4335' d='M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z'/%3e%3cpath fill='%2334a853' d='M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z'/%3e%3cpath fill='%23fbbc05' d='M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z'/%3e%3cpath fill='%23ea4335' d='M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z'/%3e%3c/svg%3e");
}

.github-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z'/%3e%3c/svg%3e");
}

/* Signup Link */
.signup-link {
    text-align: center;
}

.signup-link p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.signup-link a {
    color: #06b6d4;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signup-link a:hover {
    color: white;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .login-card {
        padding: 24px;
        margin: 10px;
        border-radius: 20px;
    }

    .login-header h2 {
        font-size: 1.75rem;
    }

    .password-wrapper input {
        padding-right: 44px;
    }

    .social-login {
        flex-direction: column;
    }

    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
}
</style>
