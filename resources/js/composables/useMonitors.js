import { ref, computed } from 'vue';
import axios from 'axios';

const STORAGE_KEY = 'pulseguard_admin_key';

export function useMonitors() {
    const monitors = ref([]);
    const systemInfo = ref({
        scheduler_active: false,
        last_scheduler_run: 'Belum aktif',
        retention_days: 14,
        db_size_mb: 0,
        total_logs: 0,
    });
    const loading = ref(false);
    const pingingIds = ref(new Set());
    const isPingingAll = ref(false);
    const isUnauthorized = ref(!sessionStorage.getItem(STORAGE_KEY));
    const authError = ref('');
    const searchQuery = ref('');
    const statusFilter = ref('all');

    const getHeaders = () => ({
        'X-Admin-Key': sessionStorage.getItem(STORAGE_KEY) || '',
        Accept: 'application/json',
    });

    const handleAuthError = (err) => {
        if (err.response?.status === 401) {
            sessionStorage.removeItem(STORAGE_KEY);
            isUnauthorized.value = true;
            authError.value = err.response?.data?.message || 'Passcode admin tidak valid.';
            return true;
        }
        return false;
    };

    const fetchMonitors = async () => {
        const key = sessionStorage.getItem(STORAGE_KEY);
        if (!key) {
            isUnauthorized.value = true;
            return;
        }

        if (monitors.value.length === 0) {
            loading.value = true;
        }

        try {
            const res = await axios.get('/api/admin/monitors', { headers: getHeaders() });
            monitors.value = res.data.data || [];
            if (res.data.system) {
                systemInfo.value = res.data.system;
            }
            isUnauthorized.value = false;
            authError.value = '';
        } catch (err) {
            if (!handleAuthError(err)) {
                console.error('Gagal memuat data monitor:', err.response?.data || err.message);
            }
        } finally {
            loading.value = false;
        }
    };

    const verifyPasscode = async (passcode) => {
        authError.value = '';
        sessionStorage.setItem(STORAGE_KEY, passcode);
        await fetchMonitors();
    };

    const lockDashboard = () => {
        sessionStorage.removeItem(STORAGE_KEY);
        monitors.value = [];
        systemInfo.value = {
            scheduler_active: false,
            last_scheduler_run: 'Belum aktif',
            retention_days: 14,
            db_size_mb: 0,
            total_logs: 0,
        };
        isUnauthorized.value = true;
    };

    const addMonitor = async (payload) => {
        const res = await axios.post('/api/admin/monitors', payload, { headers: getHeaders() });
        monitors.value.unshift(res.data.data);
        await fetchMonitors();
    };

    const pingMonitor = async (monitor) => {
        pingingIds.value.add(monitor.id);
        try {
            const res = await axios.post(`/api/admin/monitors/${monitor.id}/ping`, {}, { headers: getHeaders() });
            const idx = monitors.value.findIndex((m) => m.id === monitor.id);
            if (idx !== -1) {
                monitors.value[idx] = res.data.data;
            }
            await fetchMonitors();
        } catch (err) {
            handleAuthError(err);
        } finally {
            pingingIds.value.delete(monitor.id);
        }
    };

    const pingAllMonitors = async () => {
        isPingingAll.value = true;
        try {
            const res = await axios.post('/api/admin/monitors/ping-all', {}, { headers: getHeaders() });
            monitors.value = res.data.data || [];
            if (res.data.system) {
                systemInfo.value = res.data.system;
            }
        } catch (err) {
            handleAuthError(err);
        } finally {
            isPingingAll.value = false;
        }
    };

    const deleteMonitor = async (id) => {
        try {
            await axios.delete(`/api/admin/monitors/${id}`, { headers: getHeaders() });
            monitors.value = monitors.value.filter((m) => m.id !== id);
            await fetchMonitors();
        } catch (err) {
            handleAuthError(err);
        }
    };

    const fetchLogs = async (monitorId) => {
        try {
            const res = await axios.get(`/api/admin/monitors/${monitorId}/logs`, { headers: getHeaders() });
            return res.data.data || [];
        } catch (err) {
            handleAuthError(err);
            return [];
        }
    };

    const stats = computed(() => {
        const list = monitors.value;
        return {
            total: list.length,
            up: list.filter((m) => m.last_status === 'up').length,
            slow: list.filter((m) => m.last_status === 'slow').length,
            down: list.filter((m) => m.last_status === 'down').length,
        };
    });

    const filteredMonitors = computed(() => {
        return monitors.value.filter((m) => {
            const matchesStatus = statusFilter.value === 'all' || m.last_status === statusFilter.value;
            const q = searchQuery.value.toLowerCase();
            const matchesQuery = !q || m.name.toLowerCase().includes(q) || m.url.toLowerCase().includes(q);
            return matchesStatus && matchesQuery;
        });
    });

    return {
        monitors,
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
    };
}