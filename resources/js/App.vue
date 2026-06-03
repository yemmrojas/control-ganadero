<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">
    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 shadow-lg">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo and Title -->
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-xl">GC</span>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
              Control Ganadero
            </h1>
          </div>

          <!-- Navigation -->
          <nav class="flex items-center space-x-6">
            <router-link
              to="/"
              class="px-4 py-2 rounded-lg transition-colors duration-200"
              :class="$route.path === '/' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800'"
            >
              {{ $t('menu_calculator') }}
            </router-link>
            <router-link
              to="/history"
              class="px-4 py-2 rounded-lg transition-colors duration-200"
              :class="$route.path === '/history' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800'"
            >
              {{ $t('menu_history') }}
            </router-link>

            <!-- Language Selector -->
            <div class="flex items-center space-x-2 ml-4 border-l border-gray-700 pl-4">
              <button
                @click="changeLocale('es')"
                class="px-3 py-1 rounded transition-colors duration-200"
                :class="$i18n.locale === 'es' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
              >
                ES
              </button>
              <button
                @click="changeLocale('en')"
                class="px-3 py-1 rounded transition-colors duration-200"
                :class="$i18n.locale === 'en' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
              >
                EN
              </button>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const changeLocale = (newLocale) => {
  locale.value = newLocale;
  localStorage.setItem('locale', newLocale);
};
</script>
