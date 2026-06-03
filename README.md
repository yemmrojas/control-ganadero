# 📊 Control Ganadero - SMA Crossover Calculator

Aplicación web SPA (Single Page Application) para calcular y visualizar cruces de medias móviles simples (SMA) utilizando datos históricos de criptomonedas desde la API de Binance.

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vue.js)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=flat&logo=tailwind-css)
![Pest PHP](https://img.shields.io/badge/Pest-PHP-8A2BE2?style=flat)

---

## 🎯 Características Principales

### Backend (Laravel 13)
- ✅ **Arquitectura SOLID**: DTOs, Servicios, Actions, Controladores
- ✅ **Cliente Binance**: Paginación automática para más de 1000 velas
- ✅ **Calculadora SMA**: Detección precisa de cruces ascendentes y descendentes
- ✅ **Validaciones Adaptativas**: Límites de rango de fechas según intervalo
- ✅ **API RESTful**: Endpoints para cálculo y consulta de historial
- ✅ **Base de Datos**: SQLite con migraciones y relaciones Eloquent

### Frontend (Vue 3)
- ✅ **SPA Moderna**: Vue 3 con Composition API
- ✅ **Enrutamiento**: Vue Router para navegación fluida
- ✅ **Internacionalización**: Soporte para Español e Inglés
- ✅ **Diseño Premium**: Dark mode con Tailwind CSS v4
- ✅ **Componentes Reactivos**: Formularios, tablas y modales interactivos
- ✅ **Zona Horaria Local**: Fechas formateadas automáticamente

### Testing
- ✅ **18 Tests Automatizados**: Pest PHP
- ✅ **5 Tests Unitarios**: Lógica matemática del SMA
- ✅ **11 Tests de Integración**: API REST con mocks de Binance
- ✅ **100% de Cobertura**: Validaciones, servicios y endpoints

---

## 📋 Requisitos del Sistema

- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **SQLite**: >= 3.x (incluido por defecto)

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd control-ganadero
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Configurar el archivo de entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar la base de datos

El proyecto usa SQLite por defecto. La base de datos ya está creada en `database/database.sqlite`.

```bash
# Ejecutar migraciones
php artisan migrate
```

### 5. Instalar dependencias de Node.js

```bash
npm install
```

### 6. Compilar assets del frontend

```bash
# Para desarrollo
npm run dev

# Para producción
npm run build
```

---

## 🎮 Uso

### Iniciar el servidor de desarrollo

Necesitas dos terminales abiertas:

**Terminal 1: Servidor Laravel**
```bash
php artisan serve
```
El servidor estará disponible en: `http://localhost:8000`

**Terminal 2: Servidor Vite (desarrollo)**
```bash
npm run dev
```
Vite estará disponible en: `http://localhost:5173`

### Acceder a la aplicación

Abre tu navegador y ve a: **http://localhost:8000**

---

## 📖 Guía de Uso

### 1. Calculadora de Cruces SMA

1. **Selecciona un mercado**: BTCUSDT, ETHUSDT o XRPUSDT
2. **Elige un intervalo**: 1m, 3m, 5m, 15m, 30m, 1h, 2h, 4h, 6h, 8h, 12h, 1d, 3d, 1w
3. **Configura las fechas**: Desde y Hasta (respeta los límites adaptativos)
4. **Establece los periodos SMA**: 
   - SMA Corto (ej: 50)
   - SMA Largo (ej: 200)
5. **Haz clic en "Calcular Cruces"**

### 2. Límites de Rango de Fechas (Adaptativos)

Para proteger la API de Binance y garantizar rendimiento:

| Intervalo | Rango Máximo |
|-----------|--------------|
| 1m, 3m    | 7 días       |
| 5m        | 14 días      |
| 15m       | 30 días      |
| 30m       | 60 días      |
| 1h, 2h    | 6 meses      |
| 4h-12h    | 1 año        |
| 1d-1w     | 3 años       |

### 3. Tipos de Cruces

- **Ascendente (Golden Cross)** 🟢: El SMA corto cruza por encima del SMA largo
- **Descendente (Death Cross)** 🔴: El SMA corto cruza por debajo del SMA largo

### 4. Historial de Consultas

- Accede a `/history` para ver todas las consultas previas
- Haz clic en "Ver Detalles" para ver los cruces específicos
- Los datos se almacenan localmente en SQLite

### 5. Cambio de Idioma

- Usa el selector en la esquina superior derecha
- Disponible: **Español (ES)** / **Inglés (EN)**
- La preferencia se guarda en localStorage

---

## 🧪 Testing

### Ejecutar todos los tests

```bash
php artisan test
```

### Ejecutar solo tests unitarios

```bash
php artisan test --testsuite=Unit
```

### Ejecutar solo tests de integración

```bash
php artisan test --testsuite=Feature
```

### Ejecutar tests con cobertura

```bash
php artisan test --coverage
```

### Ejecutar un test específico

```bash
php artisan test tests/Unit/SmaCalculatorTest.php
```

---

## 🏗️ Arquitectura del Proyecto

### Backend (Laravel)

```
app/
├── Actions/                    # Casos de uso (orquestadores)
│   └── CalculateAndStoreCrossoversAction.php
├── DTOs/                       # Data Transfer Objects
│   ├── SmaRequestData.php
│   ├── SmaCalculationResult.php
│   └── CrossoverDetail.php
├── Http/
│   ├── Controllers/            # Controladores REST
│   │   ├── SmaCalculationController.php
│   │   └── SmaHistoryController.php
│   └── Requests/               # Validaciones
│       └── CalculateSmaRequest.php
├── Models/                     # Modelos Eloquent
│   ├── SmaQuery.php
│   └── SmaCrossover.php
└── Services/                   # Servicios de negocio
    ├── Binance/
    │   ├── BinanceClient.php
    │   └── BinanceClientInterface.php
    └── Math/
        ├── SmaCalculator.php
        └── SmaCalculatorInterface.php
```

### Frontend (Vue 3)

```
resources/
├── js/
│   ├── App.vue                 # Componente raíz
│   ├── app.js                  # Entry point
│   ├── router/
│   │   └── index.js            # Configuración de rutas
│   ├── views/
│   │   ├── CalculatorView.vue  # Vista de calculadora
│   │   └── HistoryView.vue     # Vista de historial
│   └── i18n/
│       ├── index.js            # Configuración i18n
│       └── locales/
│           ├── es.json         # Traducciones español
│           └── en.json         # Traducciones inglés
└── views/
    └── app.blade.php           # Template base
```

---

## 🔌 API Endpoints

### POST `/api/sma-crossover`

Calcula los cruces de SMA para los parámetros dados.

**Request Body:**
```json
{
  "market": "BTCUSDT",
  "interval": "30m",
  "start_date": "2024-10-21 00:00:00",
  "end_date": "2024-10-26 23:59:59",
  "short_period": 50,
  "long_period": 200
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "market": "BTCUSDT",
    "interval": "30m",
    "crossovers_count": 2,
    "crossovers": [
      {
        "crossover_time": "2024-10-21T18:00:00Z",
        "direction": "descending",
        "short_sma_value": 62340.50,
        "long_sma_value": 62350.20,
        "price_at_crossover": 62345.00
      }
    ]
  }
}
```

### GET `/api/sma-history`

Obtiene el listado de todas las consultas realizadas.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "market": "BTCUSDT",
      "interval": "30m",
      "start_date": "2024-10-21T00:00:00Z",
      "end_date": "2024-10-26T23:59:59Z",
      "short_period": 50,
      "long_period": 200,
      "crossovers_count": 2,
      "created_at": "2024-10-27T10:30:00Z"
    }
  ]
}
```

### GET `/api/sma-history/{id}`

Obtiene el detalle completo de una consulta específica.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "market": "BTCUSDT",
    "crossovers": [...]
  }
}
```

---

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 13**: Framework PHP moderno
- **Pest PHP**: Framework de testing elegante
- **SQLite**: Base de datos ligera
- **Guzzle HTTP**: Cliente HTTP para Binance API

### Frontend
- **Vue 3**: Framework JavaScript progresivo
- **Vue Router 4**: Enrutamiento SPA
- **Vue I18n 10**: Internacionalización
- **Axios**: Cliente HTTP
- **Tailwind CSS 4**: Framework CSS utility-first
- **Vite 8**: Build tool ultrarrápido

---

## 📚 Documentación Adicional

- [Especificación Técnica (SDD)](docs/sdd_specification.md)
- [Diseño de Arquitectura](docs/design_specification.md)
- [Plan de Implementación](docs/implementation_plan.md)
- [Lista de Tareas](docs/task.md)

---

## 🧑‍💻 Desarrollo

### Estructura de Commits

Este proyecto sigue [Conventional Commits](https://www.conventionalcommits.org/):

```
feat(backend): agregar cliente binance con paginación
fix(frontend): corregir formato de fechas en historial
test(math): agregar tests para SmaCalculator
docs(readme): actualizar instrucciones de instalación
```

### Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recrear base de datos
php artisan migrate:fresh

# Ver rutas disponibles
php artisan route:list

# Verificar estado de migraciones
php artisan migrate:status
```

---

## 🐛 Solución de Problemas

### Error: "Class 'BinanceClient' not found"

```bash
composer dump-autoload
```

### Error: "Vite manifest not found"

```bash
npm run build
```

### Error: "Database locked"

```bash
php artisan migrate:fresh
```

### Los tests fallan con error 405

Verifica que las rutas API estén registradas en `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ← Debe estar presente
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 👥 Autor

Desarrollado como prueba técnica para demostrar habilidades en:
- Arquitectura SOLID
- Testing automatizado
- Desarrollo Full Stack (Laravel + Vue)
- Integración con APIs externas
- Diseño de interfaces modernas

---

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - Framework PHP
- [Vue.js](https://vuejs.org) - Framework JavaScript
- [Binance API](https://binance-docs.github.io/apidocs/spot/en/) - Datos de mercado
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS
- [Pest PHP](https://pestphp.com) - Framework de testing

---

**¿Preguntas o sugerencias?** Abre un issue en el repositorio.
