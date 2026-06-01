# Plan de Implementación: Binance SMA Crossover SPA (Laravel 13 & Vue 3)

Este documento detalla el plan estratégico para el desarrollo ordenado de la aplicación bajo la metodología SDD (Spec-Driven Development).

---

## 1. Fases del Proyecto y Entregables

El desarrollo está organizado en 5 fases secuenciales. Cada fase debe validarse antes de proceder a la siguiente:

### Fase 1: Configuración de la Base de Datos (Fase de Datos)
*   **Tareas**:
    *   Crear migraciones físicas para `sma_queries` (historial de búsquedas) y `sma_crossovers` (registros de cruces individuales).
    *   Crear los modelos Eloquent de Laravel con sus relaciones One-to-Many definidas.
    *   Ejecutar las migraciones locales.

### Fase 2: Desarrollo del Backend y Servicios SOLID
*   **Tareas**:
    *   Crear DTOs de transferencia (`SmaRequestData`, `SmaCalculationResult`).
    *   Implementar `BinanceClient` con paginación automática (para más de 1000 velas) y control de excepciones.
    *   Implementar `SmaCalculator` de lógica pura para calcular los promedios matemáticos y detectar cruces ascendentes/descendentes.
    *   Crear la acción orquestadora `CalculateAndStoreCrossoversAction`.
    *   Crear los controladores `SmaCalculationController` y `SmaHistoryController`.
    *   Añadir rutas REST y Request Validations con límites de fecha adaptativos por intervalo.

### Fase 3: Integración del Frontend Vue 3 SPA
*   **Tareas**:
    *   Instalar dependencias NPM (`vue`, `vue-router`, `axios`, etc.) y configurar Vite.
    *   Implementar el archivo de layout Blade unificado y configurar el fallback de rutas en Laravel web routes.
    *   Configurar el enrutador del cliente Vue Router y los archivos i18n para internacionalización (Español / Inglés).
    *   Diseñar la UI con Tailwind v4: layout con sidebar, la vista del formulario/calculadora reactiva (`CalculatorView.vue`) y el listado del historial (`HistoryView.vue`).

### Fase 4: Pruebas Automatizadas (Pest PHP)
*   **Tareas**:
    *   Escribir pruebas unitarias matemáticas para `SmaCalculator`.
    *   Escribir pruebas de integración de API mockeando Binance (`Http::fake()`).
    *   Programar el test de aceptación que verifique el caso de éxito exacto detallado en el PDF.

### Fase 5: Verificación y Entrega
*   **Tareas**:
    *   Ejecutar suite completa de tests de Laravel (`php artisan test`).
    *   Validación visual, adaptabilidad horaria local y cambio de idioma en el navegador.
    *   Actualizar `README.md` con comandos de instalación e instrucciones para ejecutar.

---

## 2. Plan de Verificación

### Pruebas Automatizadas
*   Comando: `php artisan test`
*   Verificar que el mock de Binance cubra escenarios de error de red (HTTP 4xx/5xx).

### Pruebas Manuales
*   Probar el caso exacto de la prueba técnica:
    *   Mercado: `BTCUSDT`
    *   Fechas: `2024-10-21` a `2024-10-26`
    *   Intervalo: `30m`
    *   SMA: `50` y `200`
    *   Resultado esperado: Exactamente 2 cruces (uno descendente el 21 a las 18:00 y otro ascendente el 24 a las 18:00).
