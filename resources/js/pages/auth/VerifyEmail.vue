<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { logout } from '@/api/auth';

const processing = ref(false);
const status = ref('');

const resendVerification = async () => {
    processing.value = true;
    try {
        await fetch('/email/verification-notification', { method: 'POST', credentials: 'include' });
        status.value = 'Link verifikasi sudah dikirim ke email kamu.';
    } catch (e) {
        status.value = 'Gagal mengirim ulang verifikasi.';
    } finally {
        processing.value = false;
    }
};

const handleLogout = async () => {
    processing.value = true;
    try {
        await logout();
        window.location.href = '/login';
    } catch (e) {
        status.value = 'Logout gagal.';
    } finally {
        processing.value = false;
    }
};
<template>
    <Head title="Verifikasi Email" />

    <AppLayout>
        <div class="p-6">
            <h1 class="text-xl font-bold mb-4">Verifikasi Email</h1>

            <p class="mb-4">
                Sebelum melanjutkan, silakan cek email kamu untuk link verifikasi.
                Jika tidak menerima email, kamu bisa kirim ulang.
            </p>

            <div class="flex items-center gap-4">
                <button
                    @click="resendVerification"
                    :disabled="processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
                >
                    Kirim Ulang Email Verifikasi
                </button>
                <button
                    @click="handleLogout"
                    :disabled="processing"
                    class="px-4 py-2 bg-gray-600 text-white rounded"
                >
                    Logout
                </button>
<script setup lang="ts">
// ...existing imports...
import { logout } from '@/api/auth';

const handleLogout = async () => {
    processing.value = true;
    try {
        await logout();
        window.location.href = '/login';
    } catch (e) {
        status.value = 'Logout gagal.';
    } finally {
        processing.value = false;
    }
};
</script>
            </div>

            <p v-if="status" class="mt-4 text-green-600">
                {{ status }}
            </p>
        </div>
    </AppLayout>
</template>
