<template>
  <div class="space-y-8">
    <!-- Form Card -->
    <div class="bg-gray-900 rounded-xl shadow-2xl border border-gray-800 p-8">
      <h2 class="text-3xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
        {{ $t('menu_calculator') }}
      </h2>

      <form @submit.prevent="calculateCrossovers" class="space-y-6">
        <!-- Market and Interval Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Market -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_market') }}
            </label>
            <select
              v-model="form.market"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            >
              <option value="BTCUSDT">BTCUSDT</option>
              <option value="ETHUSDT">ETHUSDT</option>
              <option value="XRPUSDT">XRPUSDT</option>
            </select>
          </div>

          <!-- Interval -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_interval') }}
            </label>
            <select
              v-model="form.interval"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            >
              <option value="1m">1m</option>
              <option value="3m">3m</option>
              <option value="5m">5m</option>
              <option value="15m">15m</option>
              <option value="30m">30m</option>
              <option value="1h">1h</option>
              <option value="2h">2h</option>
              <option value="4h">4h</option>
              <option value="6h">6h</option>
              <option value="8h">8h</option>
              <option value="12h">12h</option>
              <option value="1d">1d</option>
              <option value="3d">3d</option>
              <option value="1w">1w</option>
            </select>
          </div>
        </div>

        <!-- Date Range Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Start Date -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_start_date') }}
            </label>
            <input
              v-model="form.start_date"
              type="datetime-local"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            />
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_end_date') }}
            </label>
            <input
              v-model="form.end_date"
              type="datetime-local"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            />
          </div>
        </div>

        <!-- SMA Periods Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Short Period -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_short_sma') }}
            </label>
            <input
              v-model.number="form.short_period"
              type="number"
              min="1"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            />
          </div>

          <!-- Long Period -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              {{ $t('form_long_sma') }}
            </label>
            <input
              v-model.number="form.long_period"
              type="number"
              min="2"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              required
            />
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-900/50 border border-red-700 rounded-lg p-4">
          <p class="text-red-200">{{ errorMessage }}</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="loading"
            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? $t('btn_calculating') : $t('btn_calculate') }}
          </button>
        </div>
      </form>
    </div>

    <!-- Results Card -->
    <div v-if="result" class="bg-gray-900 rounded-xl shadow-2xl border border-gray-800 p-8">
      <h3 class="text-2xl font-bold mb-6 text-blue-400">
        {{ $t('results_title') }}
      </h3>

      <!-- Summary -->
      <div class="mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
        <p class="text-lg text-gray-200">
          {{ $t('total_crossovers', { count: result.crossovers_count }) }}
        </p>
      </div>

      <!-- Crossovers Table -->
      <div v-if="result.crossovers_count > 0" class="overflow-x-auto">
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
              v-for="(crossover, index) in result.crossovers"
              :key="index"
              class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
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
</template>

<script setup>
import { useCalculator } from '../composables/useCalculator';

const {
  form,
  loading,
  errorMessage,
  result,
  calculateCrossovers,
  formatDate,
  formatNumber,
} = useCalculator();
</script>
