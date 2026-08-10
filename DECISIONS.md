# DECISIONS.md

## ¿Cómo organizaste la arquitectura y por qué?

Separé el proyecto en tres capas, siguiendo la instrucción explícita de la prueba de no concentrar lógica en Resources/Pages/Livewire:

- **`app/Models`** — solo relaciones, casts y los cálculos que son propiedad intrínseca del modelo (`Commitment::isOverdue`, los scopes `overdue()`/`dueSoon()`). Son consultas/derivaciones, no reglas de negocio con efectos secundarios.
- **`app/Services`** — lógica de dominio sin estado, resuelta desde el contenedor: `RiskLevelResolver` (matriz probabilidad × impacto), `DashboardMetricsService` (todas las métricas del dashboard en un único lugar reutilizable) y `ExecutiveSummary\*` (el contrato de IA y su implementación local).
- **`app/Actions`** — operaciones con efectos secundarios y una sola responsabilidad (`GenerateCommitmentCodeAction`, `GenerateRiskCodeAction`, `RecordCommitmentStatusChangeAction`, `CreateCommitmentAction`). Son invocables (`__invoke`) para poder inyectarse y testearse en aislamiento.
- **`app/Observers`** — el pegamento entre modelos y Actions/Services para las reglas que la prueba marca como *obligatorias* y que no deben depender de que un desarrollador se acuerde de llamarlas: `CommitmentObserver` (código automático + auditoría) y `RiskObserver` (código automático + nivel automático). Puse estas reglas en Observers, no en Actions invocadas manualmente desde Filament, a propósito: así quedan garantizadas sin importar si el cambio viene del formulario, de una acción rápida de tabla, de un seeder o de `tinker`. De hecho, este diseño me hizo encontrar un bug real durante el desarrollo (ver más abajo).
- **`app/Filament`** — Resources con Schemas/Tables separados (convención de Filament 5), sin lógica de negocio: los formularios llaman Actions (`CreateCommitment` → `CreateCommitmentAction`) o dejan que los Observers hagan su trabajo.

Los Enums (`app/Enums`) implementan `HasLabel`/`HasColor`/`HasIcon` de Filament para que los badges/selects se vean bien sin lógica repetida en cada Resource.

## ¿Qué mejorarías si tuvieras dos días adicionales?

1. **Policies con roles reales.** Hoy `canAccessPanel()` devuelve `true` para cualquier usuario autenticado y las Policies son permisivas por diseño (documentado como simplificación deliberada para el alcance de la prueba). Con más tiempo agregaría roles (ej. `admin`, `responsable`) y ajustaría las Policies para que un responsable solo pueda cambiar el estado de sus propios compromisos.
2. **RelationManager de Compromisos en Reuniones.** Ya existe la relación `Meeting::commitments()`; falta exponerla como RelationManager en `MeetingResource` para ver/crear compromisos directamente desde la reunión.
3. **Notificaciones.** Avisar (notificación en el panel o email) al responsable cuando un compromiso pasa a vencido o un riesgo sube a crítico, en vez de que el usuario tenga que entrar a mirar el dashboard.
4. **Exportación del dashboard** (PDF/Excel) para compartir el resumen ejecutivo fuera del panel.
5. **Más pruebas de UI/regresión** sobre las tablas de Filament (filtros combinados, orden por prioridad ponderada — dejé el helper `CommitmentPriority::weight()` listo para eso pero sin usarlo como `defaultSort`, ver pregunta de revisión final del punto 13 de la prueba).

## ¿Cómo conectarías OpenAI sin acoplarlo al sistema?

El "Desafío de arquitectura para IA" ya está resuelto con esto en mente: `ExecutiveSummaryServiceInterface` es el único contrato que el resto de la app conoce (`App\Filament\Pages\Dashboard` solo llama `app(ExecutiveSummaryServiceInterface::class)->generate()`). Para conectar OpenAI:

1. Crear `App\Services\ExecutiveSummary\OpenAiExecutiveSummaryService implements ExecutiveSummaryServiceInterface`, que arme un prompt con los mismos datos que hoy usa `DashboardMetricsService` (para no duplicar consultas) y devuelva el mismo DTO `ExecutiveSummaryResult`.
2. Guardar la API key en `.env` / `config/services.php` (nunca en código), y usar el cliente HTTP de Laravel (`Illuminate\Http\Client`) con timeout y manejo de errores explícito.
3. Cambiar **una línea** en `AppServiceProvider::register()`: el `bind(ExecutiveSummaryServiceInterface::class, ...)` pasa de `LocalExecutiveSummaryService` a `OpenAiExecutiveSummaryService`. Ideal hacerlo condicional a una variable de entorno (`config('services.openai.key')` presente) para poder alternar entre ambas sin re-deploy.
4. Si se quiere resiliencia, un `FallbackExecutiveSummaryService implements ExecutiveSummaryServiceInterface` que intente OpenAI y caiga al motor local si la llamada falla — decorador sobre el mismo contrato, cero cambios en Filament.

Nada en `App\Filament\Pages\Dashboard`, en la vista del modal, ni en ningún test que use el contrato necesitaría cambiar.

## ¿Qué harías si este módulo tuviera 100.000 compromisos?

- **Índices ya puestos donde se filtra/ordena más** (`due_date`, `status`, `priority` en `commitments`; ver migración). Revisaría el plan de consultas real con `EXPLAIN` antes de agregar más.
- **Migrar de SQLite a Postgres/MySQL.** SQLite serializa escrituras a nivel de archivo completo; con volumen y escrituras concurrentes (auditoría en cada cambio de estado) un motor con locking a nivel de fila es necesario. `GeneratesSequentialCode` ya usa `lockForUpdate()` pensando en esto — en SQLite es un no-op, en Postgres/MySQL sí bloquea la fila correcta.
- **Código correlativo por tabla de secuencia dedicada** en vez de leer el último código con `ORDER BY id DESC LIMIT 1`. Con 100k+ filas y alta concurrencia, una tabla `sequences (name, next_value)` con `UPDATE ... RETURNING` (Postgres) evita el escaneo del índice y reduce la ventana de bloqueo.
- **`isOverdue` como columna denormalizada + índice**, recalculada por un job programado (ej. cada 15 minutos) en vez de calcularse en cada request, si el filtro "vencidos" se usa en reportes pesados. Hoy es 100% correcto por ser calculado al vuelo; a esa escala el trade-off correcto es cachear el resultado y aceptar unos minutos de desfase.
- **Paginación por cursor** en las tablas de Filament en vez de `LIMIT/OFFSET`, y mover el dashboard a consultas agregadas cacheadas (Redis, TTL corto) en lugar de recalcular `DashboardMetricsService` en cada carga.
- **Colas** para la generación del resumen ejecutivo si se conecta un proveedor de IA externo (latencia de red no debe bloquear el request del usuario).

## ¿Qué aspecto del código consideras que necesita mayor revisión?

La generación de códigos correlativos (`GeneratesSequentialCode`). Durante el desarrollo encontré un bug real: al principio asignaba el código en un hook `afterMaking` de las factories. Con `Modelo::factory()->count(5)->create()`, Laravel arma los 5 modelos en memoria *antes* de persistir ninguno, así que los 5 leían "el último código en BD" como si ninguno existiera todavía y colisionaban (`UNIQUE constraint failed`). Lo resolví moviendo la asignación al evento `creating` del modelo (se dispara uno a la vez, justo antes de cada `INSERT`), y agregué `tests/Unit/SequentialCodeGenerationTest.php` como regresión. Sigue siendo el punto más frágil del sistema: en SQLite `lockForUpdate()` no bloquea de verdad (ver pregunta de 100k), así que bajo alta concurrencia con un motor distinto a SQLite habría que probar el comportamiento real con transacciones simultáneas, no solo con tests secuenciales.
