import { createRouter, createWebHistory } from 'vue-router';
import CalculatorView from '../views/CalculatorView.vue';
import HistoryView from '../views/HistoryView.vue';

const routes = [
    {
        path: '/',
        name: 'calculator',
        component: CalculatorView,
    },
    {
        path: '/history',
        name: 'history',
        component: HistoryView,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
