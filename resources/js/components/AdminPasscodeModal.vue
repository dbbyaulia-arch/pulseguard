<script setup>
import { ref } from 'vue';
import { Lock } from '@lucide/vue';

defineProps({
    show: Boolean,
    errorMessage: String,
});

const emit = defineEmits(['submit']);
const passcode = ref('');

const handleSubmit = () => {
    if (!passcode.value.trim()) return;
    emit('submit', passcode.value);
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 border border-slate-200">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 bg-indigo-100 text-indigo-600 rounded-xl">
                    <Lock class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Akses Admin PulseGuard</h2>
                    <p class="text-xs text-slate-500">Masukkan ADMIN_PASSCODE dari file .env</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <input
                        v-model="passcode"
                        type="password"
                        placeholder="Masukkan passcode..."
                        autofocus
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="errorMessage" class="mt-1.5 text-xs text-rose-600 font-medium">
                        {{ errorMessage }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition cursor-pointer"
                >
                    Buka Dasbor
                </button>
            </form>
        </div>
    </div>
</template>