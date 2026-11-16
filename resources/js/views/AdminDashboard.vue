<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Activity, Lock, Search, Globe, RefreshCw } from '@lucide/vue';
import { useMonitors } from '../composables/useMonitors';
import AdminPasscodeModal from '../components/AdminPasscodeModal.vue';
import MonitorForm from '../components/MonitorForm.vue';
import MonitorCard from '../components/MonitorCard.vue';
import LogModal from '../components/LogModal.vue';

const emit = defineEmits(['navigate']);

const {
    filteredMonitors,
    stats,
    systemInfo,
    loading,
    pingingIds,
    isPingingAll,
    isUnauthorized,
    authError,
    searchQuery,
    statusFilter,
    fetchMonitors,
    verifyPasscode,
    lockDashboard,
    addMonitor,
    pingMonitor,
    pingAllMonitors,
    deleteMonitor,
    fetchLogs,
} = useMonitors();

const selectedMonitor = ref(null);
const modalLogs = ref([]);
const loadingLogs = ref(false);
let pollingTimer = null;

const openLogs = async (monitor) => {
    selectedMonitor.value = monitor;
    loadingLogs.value = true;
    modalLogs.value = [];
    try {
        modalLogs.value = await fetchLogs(monitor.id);
    } finally {
        loadingLogs.value = false;
    }
};

const confirmDelete = (id) => {
    if (confirm('Hapus monitor ini beserta seluruh riwayat log-nya?')) {
        deleteMonitor(id);
    }
};

onMounted(() => {
    fetchMonitors();
    pollingTimer = setInterval(() => {
        if (!isUnauthorized.value) {
            fetchMonitors();
        }
    }, 30000);
});

onUnmounted(() => {
    if (pollingTimer) clearInterval(pollingTimer);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Navbar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-600 text-white rounded-xl">
                        <Activity class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-lg tracking-tight">PulseGuard</span>
                        <span class="ml-2 text-xs font-medium px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">Admin</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="emit('navigate', '/')"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <Globe class="w-3.5 h-3.5" />
                        <span>Halaman Publik</span>
                    </button>

                    <button
                        @click="lockDashboard"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <Lock class="w-3.5 h-3.5" />
                        <span>Kunci Sesi</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Konten Utama -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
            <!-- Panel Status Otomatisasi, Retensi & Telemetry SQLite -->
            <div class="mb-6 p-3.5 rounded-2xl border border-slate-200 bg-white flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
                <div class="flex items-center gap-2.5">
                    <span
                        class="inline-block w-2.5 h-2.5 rounded-full shrink-0"
                        :class="systemInfo?.scheduler_active ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400'"
                    ></span>
                    <span class="font-semibold text-slate-800">
                        {{ systemInfo?.scheduler_active ? 'Auto-Monitor Aktif (Cek Tiap 1 Menit)' : 'Auto-Monitor Belum Aktif (Mode Manual)' }}
                    </span>
                    <span class="text-slate-300 hidden sm:inline">•</span>
                    <span class="text-slate-500">Sync: {{ systemInfo?.last_scheduler_run || '-' }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span>Retensi: <strong class="text-slate-800">{{ systemInfo?.retention_days ?? 14 }} Hari</strong> (Auto-hapus)</span>
                    <span class="text-slate-300">•</span>
                    <span>SQLite: <strong class="text-slate-800">{{ systemInfo?.db_size_mb ?? 0 }} MB</strong> ({{ systemInfo?.total_logs ?? 0 }} log)</span>
                    <button
                        @click="pingAllMonitors"
                        :disabled="isPingingAll"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 disabled:opacity-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isPingingAll }" />
                        <span>{{ isPingingAll ? 'Mengecek...' : 'Ping Semua' }}</span>
                    </button>
                </div>
            </div>

            <!-- Ringkasan Status -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                    <p class="text-xs text-slate-500">Total Layanan</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ stats.total }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                    <p class="text-xs text-emerald-600 font-medium">Online</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.up }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                    <p class="text-xs text-amber-600 font-medium">Slow (>2s)</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ stats.slow }}</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                    <p class="text-xs text-rose-600 font-medium">Down / Error</p>
                    <p class="text-2xl font-bold text-rose-600 mt-1">{{ stats.down }}</p>
                </div>
            </div>

            <!-- Form Tambah URL -->
            <MonitorForm :onAdd="addMonitor" />

            <!-- Pencarian & Filter Status -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-1.5">
                    <button
                        v-for="tab in [
                            { key: 'all', label: 'Semua' },
                            { key: 'up', label: 'Online' },
                            { key: 'slow', label: 'Slow' },
                            { key: 'down', label: 'Down' },
                        ]"
                        :key="tab.key"
                        @click="statusFilter = tab.key"
                        :class="statusFilter === tab.key ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 transition cursor-pointer"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="relative w-full sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama atau URL..."
                        class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <!-- Grid Kartu Monitor -->
            <div v-if="loading" class="text-center py-12 text-sm text-slate-500">Memuat daftar monitor...</div>
            <div v-else-if="filteredMonitors.length === 0" class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">
                <p class="text-sm font-medium text-slate-600">Belum ada layanan yang dipantau.</p>
                <p class="text-xs text-slate-400 mt-1">Tambahkan URL website atau API pada form di atas untuk memulai.</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <MonitorCard
                    v-for="item in filteredMonitors"
                    :key="item.id"
                    :monitor="item"
                    :isPinging="pingingIds.has(item.id)"
                    @ping="pingMonitor"
                    @viewLogs="openLogs"
                    @delete="confirmDelete"
                />
            </div>
        </main>

        <!-- Modal Log & Modal Passcode -->
        <LogModal :monitor="selectedMonitor" :logs="modalLogs" :loading="loadingLogs" @close="selectedMonitor = null" />
        <AdminPasscodeModal :show="isUnauthorized" :errorMessage="authError" @submit="verifyPasscode" />
    </div>
</template>