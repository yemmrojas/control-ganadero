<template>
  <div class="space-y-8">
    <!-- History Card -->
    <div class="bg-gray-900 rounded-xl shadow-2xl border border-gray-800 p-8">
      <h2 class="text-3xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
        {{ $t('history_title') }}
      </h2>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        <p class="mt-4 text-gray-400">{{ $t('loading') }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && queries.length === 0" class="text-center py-12">
        <p class="text-gray-400 text-lg">{{ $t('history_empty') }}</p>
      </div>

      <!-- History Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-700">
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                {{ $t('history_market') }}
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                {{ $t('history_interval') }}
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                {{ $t('history_date_range') }}
              </th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">
                {{ $t('history_sma_periods') }}
              </th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">
                {{ $t('history_crossovers') }}
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                {{ $t('history_created') }}
              </th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">
                <!-- Actions -->
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="query in queries"
              :key="query.id"
              class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors cursor-pointer"
              @click="viewDetails(query.id)"
            >
              <td class="px-4 py-3 text-sm text-gray-200 font-medium">
                {{ query.market }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-200">
                {{ query.interval }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-200">
                {{ formatDateRange(query.start_date, query.end_date) }}
              </td>
              <td class="px-4 py-3 text-sm text-center text-gray-200">
                {{ query.short_period }} / {{ query.long_period }}
              </td>
              <td class="px-4 py-3 text-sm text-center">
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-900/50 text-blue-300">
                  {{ query.crossovers_count }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-200">
                {{ formatDate(query.created_at) }}
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  @click.stop="viewDetails(query.id)"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors"
                >
                  {{ $t('history_view_details') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Details Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-gray-900 rounded-xl shadow-2xl border border-gray-800 max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gray-900 border-b border-gray-800 px-8 py-6 flex items-center justify-between">
          <h3 class="text-2xl font-bold text-blue-400">
            {{ $t('results_title') }}
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-white transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="selectedQuery" class="p-8">
          <!-- Query Info -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-800 rounded-lg p-4">
              <p class="text-xs text-gray-400 mb-1">{{ $t('history_market') }}</p>
              <p class="text-lg font-semibold text-white">{{ selectedQuery.market }}</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
              <p class="text-xs text-gray-400 mb-1">{{ $t('history_interval') }}</p>
              <p class="text-lg font-semibold text-white">{{ selectedQuery.interval }}</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
              <p class="text-xs text-gray-400 mb-1">{{ $t('history_sma_periods') }}</p>
              <p class="text-lg font-semibold text-white">{{ selectedQuery.short_period }} / {{ selectedQuery.long_period }}</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
              <p class="text-xs text-gray-400 mb-1">{{ $t('history_crossovers') }}</p>
              <p class="text-lg font-semibold text-white">{{ selectedQuery.crossovers_count }}</p>
            </div>
          </div>

          <!-- Crossovers Table -->
          <div v-if="selectedQuery.crossovers_count > 0" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-700">
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                    {{ $t('table_date') }}
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">
                    {{ $t('table_type') }}
                  </th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">
                    {{ $t('table_short_sma') }}
                  </th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">
                    {{ $t('table_long_sma') }}
                  </th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">
                    {{ $t('table_price') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(crossover, index) in selectedQuery.crossovers"
                  :key="index"
                  class="border-b border-gray-800"
                >
                  <td class="px-4 py-3 text-sm text-gray-200">
                    {{ formatDate(crossover.crossover_time) }}
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span
                      :class="crossover.direction === 'ascending' ? 'text-green-400' : 'text-red-400'"
                      class="font-medium"
                    >
                      {{ $t(`crossover_${crossover.direction}`) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-gray-200">
                    {{ formatNumber(crossover.short_sma_value) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-gray-200">
                    {{ formatNumber(crossover.long_sma_value) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-gray-200">
                    {{ formatNumber(crossover.price_at_crossover) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="text-center py-8 text-gray-400">
            {{ $t('no_crossovers') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const queries = ref([]);
const loading = ref(true);
const showModal = ref(false);
const selectedQuery = ref(null);

const fetchHistory = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/sma-history');
    queries.value = response.data.data;
  } catch (error) {
    console.error('Error fetching history:', error);
  } finally {
    loading.value = false;
  }
};

const viewDetails = async (id) => {
  try {
    const response = await axios.get(`/api/sma-history/${id}`);
    selectedQuery.value = response.data.data;
    showModal.value = true;
  } catch (error) {
    console.error('Error fetching query details:', error);
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedQuery.value = null;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString(undefined, {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
};

const formatNumber = (value) => {
  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 8,
  }).format(value);
};

onMounted(() => {
  fetchHistory();
});
</script>
