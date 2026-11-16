<script setup>
import { ref } from 'vue';
import { Plus } from '@lucide/vue';

const props = defineProps({
    onAdd: Function,
});

const name = ref('');
const url = ref('');
const method = ref('GET');
const submitting = ref(false);
const errors = ref({});

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};
    try {
        await props.onAdd({
            name: name.value,
            url: url.value,
            method: method.value,
        });
        name.value = '';
        url.value = '';
        method.value = 'GET';
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
            <div class="md:col-span-3">
                <input
                    v-model="name"
                    type="text"
                    placeholder="Nama Layanan (mis. API Toko)"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <p v-if="errors.name" class="mt-1 text-xs text-rose-600">{{ errors.name[0] }}</p>
            </div>

            <div class="md:col-span-2">
                <select
                    v-model="method"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="GET">GET</option>
                    <option value="HEAD">HEAD</option>
                    <option value="POST">POST</option>
                </select>
            </div>

            <div class="md:col-span-5">
                <input
                    v-model="url"
                    type="text"
                    placeholder="https://contoh-website.com"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <p v-if="errors.url" class="mt-1 text-xs text-rose-600">{{ errors.url[0] }}</p>
            </div>

            <div class="md:col-span-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold text-sm rounded-xl flex items-center justify-center gap-1.5 transition cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>{{ submitting ? 'Mengecek...' : 'Tambah' }}</span>
                </button>
            </div>
        </div>
    </form>
</template>