# Lista de Tareas: Binance SMA Crossover SPA

Este archivo contiene el seguimiento detallado de las tareas en ejecución del proyecto bajo el método SDD.

---

## Estado General
- [x] **SDD Fase 1**: Especificación de Requerimientos y Casos de Uso (`docs/sdd_specification.md`)
- [x] **SDD Fase 2**: Diseño Técnico y Arquitectura de Clases (`docs/design_specification.md`)
- [x] **Fase 1**: Configuración de Base de Datos y Modelos
- [ ] **Fase 2**: Implementación del Backend SOLID (DTOs, Cliente Binance, Calculadora, Acción, Controladores)
- [ ] **Fase 3**: Integración del Frontend Vue 3 SPA (Vite, Router, i18n, Componentes UI)
- [ ] **Fase 4**: Pruebas Automatizadas (Pest PHP)
- [ ] **Fase 5**: Verificación Final y Documentación

---

## Checklist Detallado

### Preparación y Documentación
- [x] Escribir especificación de requerimientos e historias de usuario (`docs/sdd_specification.md`)
- [x] Diseñar arquitectura de clases, base de datos y wireframes (`docs/design_specification.md`)
- [x] Organizar roadmap y lista de tareas en `docs/implementation_plan.md` y `docs/task.md`

### Fase 1: Base de Datos y Modelos
- [x] Crear migración `create_sma_queries_table` para guardar parámetros de búsqueda
- [x] Crear migración `create_sma_crossovers_table` para guardar detalles del cruce
- [x] Crear modelo Eloquent `SmaQuery` con fillable y relación `hasMany(SmaCrossover)`
- [x] Crear modelo Eloquent `SmaCrossover` con fillable y relación `belongsTo(SmaQuery)`
- [x] Ejecutar migraciones en base de datos local

### Fase 2: Backend y Servicios SOLID
- [ ] Crear DTO `SmaRequestData`
- [ ] Crear DTO `SmaCalculationResult`
- [ ] Definir interfaz `BinanceClientInterface`
- [ ] Implementar cliente `BinanceClient` con paginación de velas Klines
- [ ] Definir interfaz `SmaCalculatorInterface`
- [ ] Implementar calculadora de SMA y cruces `SmaCalculator`
- [ ] Implementar acción orquestadora `CalculateAndStoreCrossoversAction`
- [ ] Crear Request de validación `CalculateSmaRequest` con límites de fechas adaptativos
- [ ] Crear `SmaCalculationController` para ejecutar el cálculo
- [ ] Crear `SmaHistoryController` para obtener el historial
- [ ] Configurar endpoints en `routes/api.php` y fallback en `routes/web.php`

### Fase 3: Frontend Vue 3 SPA
- [ ] Instalar dependencias npm (`vue`, `vue-router`, `axios`, `@vitejs/plugin-vue`)
- [ ] Configurar plugin de Vue en `vite.config.js`
- [ ] Configurar layout base `resources/views/app.blade.php` con contenedor `#app` y scripts Vite
- [ ] Inicializar app de Vue en `resources/js/app.js`
- [ ] Configurar enrutador Vue Router en `resources/js/router/index.js`
- [ ] Configurar archivos i18n (`resources/js/i18n/index.js`, `es.json`, `en.json`)
- [ ] Crear contenedor layout general `App.vue` con sidebar y selector de idioma
- [ ] Implementar vista `CalculatorView.vue` (Formulario reactivo, validaciones, tabla de cruces)
- [ ] Implementar vista `HistoryView.vue` (Lista de consultas previas con modal de detalle)

### Fase 4: Pruebas Automatizadas
- [ ] Escribir tests unitarios en `tests/Unit/SmaCalculatorTest.php`
- [ ] Escribir tests de integración de API en `tests/Feature/SmaCalculationApiTest.php` (Mock HTTP Binance)
- [ ] Crear test específico para el caso del PDF (`BTCUSDT`, 30m, Oct 21-26 2024, SMA 50/200)

### Fase 5: Verificación y Cierre
- [ ] Correr tests automatizados (`php artisan test`) y verificar aprobación
- [ ] Probar compatibilidad de zonas horarias locales y cambio de idiomas
- [ ] Actualizar `README.md` del proyecto
