import { ref } from 'vue';
import axios from 'axios';

export function useCalculator() {
  const form = ref({
    market: 'BTCUSDT',
    interval: '30m',
    start_date: '',
    end_date: '',
    short_period: 50,
    long_period: 200,
  });

  const loading = ref(false);
  const errorMessage = ref('');
  const result = ref(null);

  const calculateCrossovers = async () => {
    loading.value = true;
    errorMessage.value = '';
    result.value = null;

    try {
      // Detectar la zona horaria del navegador del usuario
      const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      
      const response = await axios.post('/api/sma-crossover', {
        ...form.value,
        timezone: userTimezone
      });
      result.value = response.data.data;
    } catch (error) {
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        errorMessage.value = Object.values(errors).flat().join('. ');
      } else if (error.response?.data?.message) {
        errorMessage.value = error.response.data.message;
      } else {
        errorMessage.value = 'An error occurred while processing the request';
      }
    } finally {
      loading.value = false;
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString(undefined, {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatNumber = (value) => {
    if (value === null || value === undefined) return '';
    return new Intl.NumberFormat(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 8,
    }).format(value);
  };

  return {
    form,
    loading,
    errorMessage,
    result,
    calculateCrossovers,
    formatDate,
    formatNumber,
  };
}
