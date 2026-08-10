# Executive Commitments

Módulo de seguimiento ejecutivo de reuniones, compromisos y riesgos, construido para la prueba técnica `DEV-CH-01` de Infinity Group SAS (iAxel Developer Challenge).

## Stack

- Laravel 13 · PHP 8.5
- Filament 5 (panel de administración) + Livewire
- SQLite
- PHPUnit (suite de tests incluida en el scaffold de Laravel; no se agregó Pest para no sumar una dependencia adicional)

## Requisitos previos

- PHP 8.5 con las extensiones: `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, `fileinfo`, `curl`, `zip`, `gd`, `intl`.
- Composer 2.
- Node.js (opcional — solo se usa para los assets por defecto del scaffold de Laravel; el panel de administración usa los assets ya compilados que publica Filament).

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate

# Base de datos SQLite
touch database/database.sqlite   # en Windows: type nul > database\database.sqlite
php artisan migrate --seed

php artisan serve
```

La aplicación redirige `/` a `/admin` (el panel es el único punto de entrada real de este módulo).

## Credenciales demo

| Email | Password |
|---|---|
| `admin@iaxel.test` | `password` |

El seeder también crea 4 usuarios adicionales (responsables ficticios) con la misma contraseña.

## Estructura del dominio

```
app/
  Enums/            MeetingStatus, CommitmentPriority, CommitmentStatus,
                     RiskProbability, RiskImpact, RiskLevel, RiskStatus
                     (implementan HasLabel/HasColor/HasIcon de Filament)
  Models/            Meeting, Commitment, CommitmentStatusHistory, Risk
  Observers/         CommitmentObserver, RiskObserver
  Actions/           Generacion de codigos, registro de auditoria,
                     creacion de compromisos
  Services/          RiskLevelResolver, DashboardMetricsService,
                     ExecutiveSummaryServiceInterface + Local/Gemini/Fallback
  Policies/          MeetingPolicy, CommitmentPolicy, RiskPolicy
  Filament/          Resources, Widgets y Pages del panel
```

La lógica de negocio vive en `Actions`/`Services`/`Observers`, no en los Resources de Filament (ver `DECISIONS.md`).

## Reglas de negocio implementadas

- **Compromiso vencido**: no es una columna en base de datos. Es un atributo calculado (`Commitment::isOverdue`) y un scope de consulta (`Commitment::overdue()`) que comparan `due_date` contra la fecha actual y el estado. Nunca queda desincronizado.
- **Riesgo crítico**: `RiskLevelResolver` aplica una matriz probabilidad × impacto (probabilidad alta + impacto alto → crítico, regla obligatoria de la prueba). Se recalcula automáticamente en `RiskObserver` cada vez que cambian probabilidad o impacto.
- **Auditoría de compromisos**: `CommitmentObserver` registra en `commitment_status_histories` quién cambió el estado, cuándo, y los valores anterior/nuevo — sin importar si el cambio viene del formulario, una acción rápida de tabla o `tinker`.
- **Códigos automáticos** (`COM-0001`, `RIS-0001`): se asignan en el evento `creating` de cada modelo (no en las factories ni en los formularios), para que cualquier vía de creación —incluyendo creación en lote— obtenga un código correlativo único.

## Resumen ejecutivo (botón "Generar resumen ejecutivo")

El dashboard incluye un botón que abre un modal con un resumen generado a través de `ExecutiveSummaryServiceInterface`. Hay dos implementaciones:

- **`LocalExecutiveSummaryService`** (por defecto): redacta el resumen con reglas sobre los datos reales, sin llamar a ningún proveedor externo. No requiere configuración.
- **`GeminiExecutiveSummaryService`** (opcional): le pide el resumen a la API de Gemini (Google AI Studio) con salida JSON estructurada.

### Activar Gemini

1. Consigue una API key gratuita en **aistudio.google.com/apikey**.
2. Agrega en tu `.env`:
   ```
   GEMINI_API_KEY=tu-api-key
   GEMINI_MODEL=gemini-flash-latest
   ```
3. Listo — no hay que tocar código. `AppServiceProvider` detecta la key y activa Gemini automáticamente, envuelto en `FallbackExecutiveSummaryService`: si la llamada a Gemini falla (sin internet, key inválida, rate limit), cae solo al motor local y el usuario nunca ve un error. Sin la key, el comportamiento es exactamente el mismo de antes (solo motor local).

Ver `DECISIONS.md` para el detalle de cómo está armado el contrato y cómo se conectaría otro proveedor (ej. OpenAI) siguiendo el mismo patrón.

## Tests

```bash
php artisan test
```

42 tests cubren: acceso a cada módulo del panel, flujos completos de creación (compromiso y riesgo) vía Livewire, edición de cada recurso, filtros de tabla, la matriz completa de `RiskLevelResolver`, los casos límite de `isOverdue`, `DashboardMetricsService`, `LocalExecutiveSummaryService`, `GeminiExecutiveSummaryService`, `FallbackExecutiveSummaryService` y el binding condicional de `ExecutiveSummaryServiceInterface` (con `Http::fake()`, sin llamadas reales a red ni dependencia de la `GEMINI_API_KEY` del entorno), y una regresión sobre generación de códigos en creación en lote.

## Nota sobre el entorno de desarrollo local

En la máquina donde se construyó este proyecto, el servidor embebido de PHP (`php artisan serve` / `php -S`) sobre PHP 8.5.9 presenta un bug reproducible al reflexionar atributos de Livewire (falla incluso en la página de login sin modificar, con `Undefined constant Attribute::IS_REPEATABLE`). Se confirmó que **no es un problema de esta aplicación**: la misma request funciona correctamente invocando el HTTP Kernel directamente o a través de la suite de tests (que es como se validó todo el desarrollo). Si `php artisan serve` fallara en tu máquina, usa Laravel Herd, Apache/Nginx apuntando a `public/`, o revisa si tu build de PHP 8.5 tiene el mismo problema.

## DECISIONS.md

Ver [`DECISIONS.md`](DECISIONS.md) para las decisiones de arquitectura y las respuestas a las preguntas de la sección 10 de la prueba.
