<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import PublicStatusView from './views/PublicStatusView.vue';
import AdminDashboard from './views/AdminDashboard.vue';

const currentPath = ref(window.location.pathname);

const navigate = (path) => {
    window.history.pushState({}, '', path);
    currentPath.value = path;
};

const handlePopState = () => {
    currentPath.value = window.location.pathname;
};

onMounted(() => window.addEventListener('popstate', handlePopState));
onUnmounted(() => window.removeEventListener('popstate', handlePopState));
</script>

<template>
    <AdminDashboard v-if="currentPath.startsWith('/admin')" @navigate="navigate" />
    <PublicStatusView v-else @navigate="navigate" />
</template>