<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import MonitorCard from '../components/MonitorCard.vue';

defineEmits(['navigate']);

const monitors = ref([]);
const meta = ref({ last_updated: '-', retention_days: 14 });
const loading = ref(true);
let timer = null;

const fetchPublicStatus = async () => {
    try {
        const res = await axios.get('/api/public/status');
        monitors.value = res.data.data;
        meta.value = res.data.meta;
    } finally {
        loading.value = false;
    }
};

const allOperational = computed(() =>
    monitors.value.length > 0 && monitors.value.every((m) => m.last_status === 'up')
);

onMounted(() => {
    fetchPublicStatus();
    timer = setInterval(fetchPublicStatus, 30000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <div class="max-w-5xl mx-auto px-4 py-10">
      <!-- Header Status Publik -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">PulseGuard Status</h1>
          <p class="text-sm text-slate-500">Pemantauan ketersediaan layanan real-time (Retensi {{ meta.retention_days }} hari)</p>
        </div>
        <button
          @click="$emit('navigate', '/admin')"
          class="text-xs px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100"
        >
          Panel Admin
        </button>
      </div>

      <!-- Banner Kondisi Global -->
      <div
        v-if="!loading"
        class="mb-8 p-4 rounded-2xl border flex items-center justify-between"
        :class="allOperational ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900'"
      >
        <div class="flex items-center gap-3">
          <span class="w-3 h-3 rounded-full" :class="allOperational ? 'bg-emerald-500' : 'bg-amber-500'"></span>
          <span class="font-semibold">
            {{ allOperational ? 'Semua Sistem Berjalan Normal' : 'Sebagian Layanan Mengalami Penurunan / Gangguan' }}
          </span>
        </div>
        <span class="text-xs opacity-75">Update: {{ meta.last_updated }}</span>
      </div>

      <!-- Grid Layanan Publik -->
      <div v-if="loading" class="text-center py-12 text-slate-400 text-sm">Memuat status layanan...</div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <MonitorCard
          v-for="monitor in monitors"
          :key="monitor.id"
          :monitor="monitor"
          :readonly="true"
        />
      </div>
    </div>
  </div>
</template>