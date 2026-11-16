<script setup>
import { ref, computed, watch } from 'vue';
import { X } from '@lucide/vue';

const props = defineProps({
    monitor: Object,
    logs: Array,
    loading: Boolean,
});

const emit = defineEmits(['close']);

const filterMode = ref('all');

watch(() => props.monitor, () => {
    filterMode.value = 'all';
});

const errorCount = computed(() =>
    (props.logs || []).filter((l) => l.status === 'down' || l.status === 'slow').length
);

const filteredLogs = computed(() => {
    if (!props.logs) return [];
    if (filterMode.value === 'errors') {
        return props.logs.filter((l) => l.status === 'down' || l.status === 'slow');
    }
    return props.logs;
});
</script>

<template>
    <div v-if="monitor" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] flex flex-col border border-slate-200">
            <!-- Header Modal -->
            <div class="p-5 border-b border-slate-200 flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900">Riwayat Ping: {{ monitor.name }}</h3>
                    <p class="text-xs text-slate-500 break-all">{{ monitor.url }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Menampilkan maksimal 100 riwayat terbaru. Log lebih dari 14 hari dihapus otomatis oleh sistem.
                    </p>
                </div>
                <button @click="emit('close')" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer shrink-0">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Bar Filter Log -->
            <div v-if="!loading && logs.length > 0" class="px-5 pt-3 flex items-center gap-2">
                <button
                    @click="filterMode = 'all'"
                    :class="filterMode === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1 rounded-lg text-xs font-semibold transition cursor-pointer"
                >
                    Semua ({{ logs.length }})
                </button>
                <button
                    @click="filterMode = 'errors'"
                    :class="filterMode === 'errors' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1 rounded-lg text-xs font-semibold transition cursor-pointer"
                >
                    Bermasalah / Slow ({{ errorCount }})
                </button>
            </div>

            <!-- Daftar Log -->
            <div class="p-5 overflow-y-auto space-y-2.5">
                <div v-if="loading" class="text-center py-8 text-sm text-slate-500">Memuat riwayat log...</div>
                <div v-else-if="filteredLogs.length === 0" class="text-center py-8 text-sm text-slate-500">
                    {{ filterMode === 'errors' ? 'Tidak ada catatan error atau kelambatan.' : 'Belum ada riwayat pengecekan.' }}
                </div>

                <div
                    v-else
                    v-for="log in filteredLogs"
                    :key="log.id"
                    class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-1.5"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                :class="{
                                    'bg-emerald-100 text-emerald-700': log.status === 'up',
                                    'bg-amber-100 text-amber-700': log.status === 'slow',
                                    'bg-rose-100 text-rose-700': log.status === 'down',
                                }"
                                class="px-2 py-0.5 font-bold rounded uppercase"
                            >
                                {{ log.status }} {{ log.status_code ? `(${log.status_code})` : '' }}
                            </span>
                            <span class="font-semibold text-slate-700">{{ log.response_time_ms }} ms</span>
                        </div>
                        <span class="text-slate-400">{{ log.checked_at }}</span>
                    </div>

                    <p v-if="log.status === 'slow'" class="p-2 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg">
                        Respons server melambat: waktu tunggu melebihi ambang batas normal (2.000 ms).
                    </p>

                    <p v-if="log.error_message" class="p-2 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg font-mono break-all">
                        {{ log.error_message }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>