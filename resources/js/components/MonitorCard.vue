<script setup>
defineProps({
    monitor: { type: Object, required: true },
    readonly: { type: Boolean, default: false },
    isPinging: { type: Boolean, default: false },
});

defineEmits(['ping', 'viewLogs', 'delete']);
</script>

<template>
    <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between gap-4">
        <!-- Header Kartu -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span
                        v-if="!readonly && monitor.method"
                        class="px-2 py-0.5 text-[11px] font-bold rounded bg-slate-100 text-slate-700 shrink-0"
                    >
                        {{ monitor.method }}
                    </span>
                    <h3 class="font-semibold text-slate-900 text-base truncate">{{ monitor.name }}</h3>
                </div>
                <a
                    :href="monitor.url"
                    target="_blank"
                    rel="noopener"
                    class="text-xs text-slate-500 hover:underline break-all line-clamp-1 mt-0.5 block"
                >
                    {{ monitor.url }}
                </a>
            </div>

            <!-- Badge Status -->
            <span
                class="px-2.5 py-1 rounded-full text-xs font-semibold shrink-0"
                :class="{
                    'bg-emerald-50 text-emerald-700 border border-emerald-200': monitor.last_status === 'up',
                    'bg-amber-50 text-amber-700 border border-amber-200': monitor.last_status === 'slow',
                    'bg-rose-50 text-rose-700 border border-rose-200': monitor.last_status === 'down',
                    'bg-slate-100 text-slate-600': !monitor.last_status || monitor.last_status === 'pending',
                }"
            >
                {{
                    monitor.last_status === 'up'
                        ? 'Online'
                        : monitor.last_status === 'slow'
                          ? 'Slow'
                          : monitor.last_status === 'down'
                            ? 'Down'
                            : 'Pending'
                }}
            </span>
        </div>

        <!-- Heartbeat Bar 60 Ping Terakhir (Geser Horizontal & Hover) -->
        <div>
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span>60 Ping Terakhir</span>
                <span class="font-medium text-slate-700">
                    {{ monitor.last_response_ms ?? '-' }} ms ({{ monitor.uptime_percentage ?? 100 }}% Uptime)
                </span>
            </div>

            <div class="flex items-center gap-[3px] overflow-x-auto pb-1">
                <div
                    v-for="ping in monitor.recent_pings || []"
                    :key="ping.id"
                    :title="`${ping.checked_at} | ${ping.status.toUpperCase()} | ${ping.response_time_ms} ms`"
                    @click="!readonly && $emit('viewLogs', monitor)"
                    class="h-6 min-w-[6px] flex-1 rounded-sm transition-transform hover:scale-y-110"
                    :class="[
                        !readonly ? 'cursor-pointer' : '',
                        {
                            'bg-emerald-500': ping.status === 'up',
                            'bg-amber-400': ping.status === 'slow',
                            'bg-rose-500': ping.status === 'down',
                        },
                    ]"
                ></div>
                <div
                    v-if="!monitor.recent_pings || monitor.recent_pings.length === 0"
                    class="h-6 w-full rounded bg-slate-100 flex items-center justify-center text-[11px] text-slate-400"
                >
                    Belum ada riwayat ping
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Admin -->
        <div v-if="!readonly" class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-400">{{ monitor.last_checked_at || monitor.updated_at_human || 'Belum dicek' }}</span>
            <div class="flex items-center gap-1.5">
                <button
                    @click="$emit('ping', monitor)"
                    :disabled="isPinging"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium disabled:opacity-50 cursor-pointer"
                >
                    {{ isPinging ? 'Ping...' : 'Ping Now' }}
                </button>
                <button
                    @click="$emit('viewLogs', monitor)"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium cursor-pointer"
                >
                    Log
                </button>
                <button
                    @click="$emit('delete', monitor.id)"
                    class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium cursor-pointer"
                >
                    Hapus
                </button>
            </div>
        </div>
    </div>
</template>