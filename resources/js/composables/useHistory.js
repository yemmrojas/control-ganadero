import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useHistory() {
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

  const formatDateRange = (startDate, endDate) => {
    if (!startDate || !endDate) return '';
    const start = new Date(startDate);
    const end = new Date(endDate);
    return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
  };

  const formatNumber = (value) => {
    if (value === null || value === undefined) return '';
    return new Intl.NumberFormat(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 8,
    }).format(value);
  };

  onMounted(() => {
    fetchHistory();
  });

  return {
    queries,
    loading,
    showModal,
    selectedQuery,
    fetchHistory,
    viewDetails,
    closeModal,
    formatDate,
    formatDateRange,
    formatNumber,
  };
}
