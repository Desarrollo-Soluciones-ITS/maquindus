# Propuesta: Módulo de Gestión y Procesamiento de Órdenes de Cliente

> Documento de viabilidad, alcance y estimación (H/H).
> Proyecto: **Maquindus** — Laravel 12 + Filament 4 + MariaDB.
> Estado: **PROPUESTA — PENDIENTE DE APROBACIÓN**. No iniciar desarrollo sin "OK".

---

## 1. Resumen Ejecutivo

- **Petición:** nuevo módulo para procesar órdenes de los clientes, midiendo su paso por 6 estaciones internas (RFQ, Ingeniería, Compra, Procura, Logística, Cotización).
- **Viabilidad:** **ALTA**. El stack actual lo soporta. Todo es **aditivo** (tablas nuevas + recurso nuevo). No rompe flujos actuales.
- **Núcleo del reto:** no es CRUD. Es un **motor de flujo (workflow) con control de tiempos (SLA)**. Requiere máquina de estados + cálculo de desviaciones + rutas de tiempos configurables.
- **Punto crítico bloqueante:** el cliente **aún no definió** si reutiliza Documentos/Archivos existentes o si el módulo será aislado. Afecta el modelo de datos. **Decisión obligatoria antes de Fase 1.**
- **Estimación:** ~**398 H/H** (332 h + 20% contingencia).
- **Recursos:** 1 dev Senior Laravel/Filament (full-time) + QA parcial + responsable funcional del cliente para talleres.

---

## 2. Requerimiento Entendido

Flujo lineal de una orden de cliente por 6 estaciones:

| # | Estación | Propósito (según cliente) |
|---|---|---|
| 1 | **RFQ** | Recepción de la solicitud del cliente |
| 2 | **Ingeniería** | Análisis + acotaciones y recomendaciones |
| 3 | **Compra** | Contacto proveedores → Vendor List con rangos de precio y opciones |
| 4 | **Procura** | Evaluación de costos de cada proveedor para adquisición |
| 5 | **Logística** | Fechas, materiales, localidades, tiempos de entrega |
| 6 | **Cotización** | Emisión de propuesta formal al cliente |

**Requisitos transversales:**

1. Medir el tiempo real que dura cada orden en cada estación.
2. Crear/gestionar/editar **rutas de tiempos** (duración esperada por estación).
3. Al ingresar una orden, elegir una ruta → obtener duración esperada por estación.
4. Estado por tiempo de cada estación: **Correcto / Adelantado / Atrasado / Cancelado**.
5. Definir **qué acción se ejecuta en cada estación** (quién hace qué).
6. **Métricas y parámetros** por estación para agilizar el proceso.

---

## 3. Análisis de Viabilidad y Escalabilidad

**Viabilidad: ALTA.** El proyecto ya tiene los cimientos:

- **Autenticación y permisos** propios (`Role`, `Permission`, `hasPermission`) → se reutilizan.
- **Auditoría** (`spatie/laravel-activitylog` + trait `HasActivityLog`) → historial de cambios de orden listo.
- **Búsqueda global** (`Searchable` + tabla `search_index`) → la orden será buscable.
- **Bloqueo de registros** (trait `Lockable`) → evita edición concurrente de una orden.
- **Exportación** (`maatwebsite/excel`) → reportes de métricas.
- **Colas** (`queue:listen`, tabla `jobs`) → recálculo de SLA en segundo plano.
- **Catálogo existente:** `Supplier` (proveedores para Vendor List), `SupplierPurchaseOrder` (órdenes de compra → encaja en Procura), `Person` (contactos), `Document`/`File` (documentos).

**Escalabilidad:**

- El volumen esperado (órdenes industriales) es bajo/medio → MariaDB sobra.
- Riesgo de rendimiento NO está en el volumen sino en el **cálculo de tiempos**: se resuelve pre-calculando duraciones por transición (timestamps) y dejando un comando programado que marca atrasos. Sin consultas pesadas en pantalla.
- Multi-estación y concurrencia: cada orden se bloquea con `Lockable` mientras un usuario la edita.
- Crecimiento futuro (más estaciones, sub-flujos por tipo de orden): el diseño de **ruta de tiempos parametrizable** lo permite sin tocar código.

## 4. Integración con el Estado Actual del Proyecto

Estrategia: **ADITIVA**. No se alteran tablas ni flujos existentes. Cero regresiones.

| Elemento existente | ¿Se reutiliza? | Cómo |
|---|---|---|
| `Supplier` | **Sí** | Vendor List = proveedores candidatos de la orden |
| `SupplierPurchaseOrder` | **Sí** | Procura genera PO al proveedor seleccionado |
| `Person` | **Sí** | Contactos del cliente / responsables |
| `Equipment` / `Part` | **Sí** | Ítems del requerimiento (equipos/repuestos solicitados) |
| `Document` / `File` | **DEPENDE** | Ver punto 5 (decisión abierta) |
| `Permission` / `Role` | **Sí** | Permisos nuevos `client_orders.*`, `order_routes.*` |
| `HasActivityLog` | **Sí** | Bitácora de la orden y sus estaciones |
| `Searchable` | **Sí** | Orden buscable desde el buscador global |
| `Lockable` | **Sí** | Bloqueo de edición concurrente |
| `HasUuids` + `SoftDeletes` | **Sí** | Estándar de PK y archivado |
| `Category` (enum) | **Sí** | Se añade categoría `Cotizacion` si aplica |
| `Status` (enum) | **Extiende** | Nuevo enum de estado de estación (no alterar el actual) |
| Dashboard / Widgets | **Extiende** | Nuevos widgets de métricas de órdenes |

**Puntos de contacto a vigilar (para no regresar):**

- `Permission::$permissions` + seeder `AddNewModulePermissions` (idempotente, `updateOrCreate`). Solo se agregan llaves nuevas.
- Si se reutiliza `Document`: registrar el nuevo modelo en `Searchable::getSearchableFieldsMap()` y en el morph map / helpers (`model_to_spanish`, `documentable_name_column`). Cambios aditivos.
- Menú Filament: se añade `navigationSort` nuevo sin reordenar los existentes.

---

## 5. DECISIÓN ABIERTA (BLOQUEANTE): ¿Integrado o Aislado?

El cliente **aún no define** si el nuevo módulo **usa los Documentos/Archivos actuales** o si es **totalmente aislado**. Esto es determinante.

### Opción A — INTEGRADO (reutiliza `Document`/`File`)

- La orden y sus ítems se registran como `documentable` polimórfico existente.
- **Ventajas:** un solo repositorio de archivos, búsqueda global unificada, versionado y carpetas ya probados, menor H/H.
- **Riesgos:** cualquier cambio en el módulo Documentos impacta órdenes; hay que cuidar el naming de carpetas.
- **Impacto H/H:** dentro de lo estimado.

### Opción B — AISLADO (módulo con archivos propios)

- Tabla/gestión de archivos separada para órdenes.
- **Ventajas:** independencia total, cero acoplamiento.
- **Riesgos:** duplica lógica ya existente (versiones, preview, "ver en carpeta", LAN/`gestor://`), más H/H y más mantenimiento.
- **Impacto H/H:** **+25 a +40 H/H** (duplicar pipeline de archivos).

> **Recomendación técnica:** **Opción A (Integrado)**, por reutilización y consistencia. Requiere confirmación del cliente.

## 6. Arquitectura Propuesta

Se respeta el patrón del proyecto: `Models` + `Enums` + `Services` + `Filament/Resources/{Resource}` con subcarpetas `Schemas`, `Tables`, `Pages`.

### 6.1. Entidades nuevas (tablas aditivas, PK UUID)

| Tabla / Modelo | Rol |
|---|---|
| `client_orders` / `ClientOrder` | La orden del cliente (cabecera) |
| `client_order_items` / `ClientOrderItem` | Ítems solicitados (equipo/repuesto/cantidad) |
| `order_stages` / `OrderStage` | Catálogo fijo de estaciones (RFQ…Cotización) o configurable |
| `order_routes` / `OrderRoute` | Ruta de tiempos (plantilla reutilizable) |
| `order_route_stages` / `OrderRouteStage` | Duración esperada por estación dentro de una ruta |
| `order_stage_progress` / `OrderStageProgress` | Registro del paso real de la orden por cada estación (entrada, salida, duración, estado SLA, responsable) |
| `supplier_quotes` / `SupplierQuote` | Oferta de proveedor (Vendor List): proveedor, opción, rango de precio (min/max), moneda |
| `client_order_quotations` / `ClientOrderQuotation` | Cotización formal al cliente (versiones) |
| `order_stage_actions` / `OrderStageAction` | Registro de la acción ejecutada en la estación (quién, cuándo, observación, adjunto) |

### 6.2. Enums nuevos

- `OrderStation` (RFQ, Ingenieria, Compra, Procura, Logistica, Cotizacion).
- `OrderSlaStatus` (Correcto, Adelantado, Atrasado, Cancelado, EnEspera).
- `ClientOrderStatus` (Borrador, EnProceso, Cotizada, Ganada, Perdida, Cancelada).

### 6.3. Servicios (capa `app/Services`)

- `OrderWorkflowService` — valida y ejecuta transiciones de estación (máquina de estados).
- `OrderTimingService` — calcula duración real vs esperada, desviación y estado SLA.
- `OrderMetricsService` — agrega KPIs por estación/período.
- `Artisan Command` programado (`orders:recalc-sla`) — recorre órdenes abiertas y marca Atrasado. Encolado (queue) para no bloquear.

### 6.4. Máquina de estados (motor de flujo)

- Flujo **lineal hacia adelante**: RFQ → Ingeniería → Compra → Procura → Logística → Cotización.
- Cada transición valida: permiso del rol, criterio de salida (gate), campos obligatorios.
- Al entrar a una estación: se crea registro en `order_stage_progress` con `entered_at`.
- Al salir: se fija `exited_at`, se calcula duración y estado SLA comparando contra `order_route_stages.expected_hours` (o días).
- **Retrocesos** (rechazo/reproceso): soportados solo si el cliente lo confirma (ver preguntas).

## 7. Definición de Acciones por Estación

Define **qué se hace**, **quién**, **qué entra**, **qué sale** y **qué botones ofrece el sistema**. Requerido por el cliente (punto 2). Debe validarse en el taller de descubrimiento.

### 7.1. RFQ — Recepción

- **Responsable:** Ventas / Atención al cliente.
- **Acción:** recibir y registrar la solicitud del cliente.
- **Entrada:** datos del cliente, descripción del requerimiento, prioridad, adjuntos.
- **Salida:** orden creada (nº único), ruta de tiempos asignada, cronómetro iniciado.
- **Botones sistema:** Crear orden · Adjuntar documentos · Asignar ruta · Iniciar.
- **Criterio de salida (gate):** solicitud completa + ruta asignada.

### 7.2. Ingeniería

- **Responsable:** Departamento de Ingeniería.
- **Acción:** analizar la solicitud; emitir acotaciones y recomendaciones técnicas.
- **Entrada:** requerimiento de RFQ.
- **Salida:** memoria técnica, recomendaciones, especificaciones/ajustes.
- **Botones sistema:** Registrar recomendaciones (texto + adjuntos) · Marcar revisado · Solicitar aclaratoria.
- **Criterio de salida:** recomendaciones registradas + visto bueno de Ing.

### 7.3. Compra

- **Responsable:** Compras.
- **Acción:** contactar proveedores; generar Vendor List según requisitos del cliente.
- **Entrada:** especificaciones de Ingeniería.
- **Salida:** Vendor List con proveedores, opciones ofrecidas y rango de precios.
- **Botones sistema:** Añadir proveedor candidato (relación `Supplier`) · Cargar oferta/opción con precio mín-máx · Marcar opción.
- **Criterio de salida:** N proveedores mínimos con oferta cargada (N a definir).

### 7.4. Procura

- **Responsable:** Procura / Compras senior.
- **Acción:** evaluar costos de cada proveedor para la adquisición.
- **Entrada:** Vendor List + ofertas.
- **Salida:** cuadro comparativo, proveedor(es) seleccionado(s), costo consolidado.
- **Botones sistema:** Comparativa lado a lado · Scoring/puntaje · Seleccionar proveedor · Generar PO (`SupplierPurchaseOrder`).
- **Criterio de salida:** proveedor seleccionado + costo final.

### 7.5. Logística

- **Responsable:** Logística.
- **Acción:** evaluar fechas, materiales, localidades y tiempos de entrega.
- **Entrada:** proveedor seleccionado.
- **Salida:** plan logístico: lead times, puntos de entrega, disponibilidad de materiales, fecha compromiso.
- **Botones sistema:** Registrar fechas · Localidades · Disponibilidad de material · Calcular tiempo total de entrega.
- **Criterio de salida:** plan logístico completo con fecha compromiso.

### 7.6. Cotización

- **Responsable:** Ventas.
- **Acción:** emitir la propuesta formal de cara al cliente.
- **Entrada:** todo lo acumulado (Ingeniería + Procura + Logística).
- **Salida:** cotización formal (versión + PDF), enviada; respuesta del cliente.
- **Botones sistema:** Generar cotización (ítems/montos/condiciones) · Exportar PDF · Marcar enviada · Registrar respuesta (Aceptada/Rechazada).
- **Criterio de salida:** cotización enviada → orden pasa a Ganada/Perdida.

> **Nota:** esta tabla es un **borrador basado en el requerimiento**. Las acciones exactas deben cerrarse con el cliente (Fase 0). Es el insumo del punto 2 solicitado.

## 8. Métricas y Parámetros por Estación

Insumo del punto 3. Cada métrica deriva de datos ya capturados por la máquina de estados (sin trabajo manual extra).

### 8.1. Métricas transversales (globales)

| Métrica | Definición |
|---|---|
| **Lead time total** | Días desde RFQ hasta Cotización |
| **WIP por estación** | Órdenes actualmente en cada estación (cola/backlog) |
| **Throughput** | Órdenes completadas por período |
| **Cumplimiento SLA** | % de órdenes/estaciones dentro del tiempo esperado |
| **Tasa de cancelación** | Canceladas / total |
| **Cuello de botella** | Estación con mayor tiempo promedio o mayor WIP |
| **Desviación media** | Promedio (tiempo real − tiempo esperado) por estación |
| **Win rate** | Cotizaciones aceptadas / emitidas |

### 8.2. Parámetros por estación

| Estación | Parámetros / KPIs específicos |
|---|---|
| **RFQ** | Tiempo de registro · % solicitudes incompletas · tiempo hasta ruta asignada |
| **Ingeniería** | Tiempo de análisis · nº de recomendaciones · tasa de solicitud de aclaratoria · tiempo de espera de información |
| **Compra** | nº proveedores contactados · nº ofertas recibidas · tiempo de respuesta por proveedor · amplitud del rango de precios (máx−mín) |
| **Procura** | nº proveedores evaluados · ahorro vs presupuesto estimado · tiempo de evaluación · proveedor seleccionado vs mejor precio |
| **Logística** | lead time estimado vs real · cumplimiento de fecha de entrega · desviación de entrega · nº de localidades |
| **Cotización** | tiempo de emisión · monto promedio · nº revisiones/versiones · tasa de aceptación |

### 8.3. Regla de estado por tiempo (SLA)

Comparación: `duración_real` vs `duración_esperada` (de la ruta elegida).

- `duración_real <= esperada` → **Correcto**.
- `duración_real < esperada − tolerancia` → **Adelantado**.
- `duración_real > esperada + tolerancia` → **Atrasado**.
- Orden cancelada → **Cancelado** en la estación activa.

> **Tolerancia (parámetro configurable):** por defecto se propone ±10%, a confirmar por el cliente.

### 8.4. Visualización

- **Tablero por estación** (tipo kanban): órdenes agrupadas por estación, color por estado SLA.
- **Widgets de dashboard:** WIP, cumplimiento SLA, cuello de botella, lead time promedio.
- **Exportación Excel** (maatwebsite) de métricas por rango de fechas.
- **Filtros** reutilizando `TextFilter` / `DateFilter` existentes.

## 9. Requisitos Previos del Cliente (Obligatorios)

Nada de esto se inicia sin estos insumos. Bloquean el cronograma.

1. **Definición funcional de las 6 estaciones:** acciones exactas, entradas, salidas y responsable/rol de cada una (cerrar borrador del punto 7).
2. **Catálogo de rutas de tiempos real:** tiempos objetivo por estación y por tipo de orden.
3. **Tolerancia SLA:** el % que define "Adelantado/Atrasado" (propuesto ±10%).
4. **Decisión Integrado vs Aislado** (punto 5) sobre Documentos/Archivos.
5. **Ejemplos reales:** 2–3 órdenes completas de la vida real para modelar el flujo.
6. **Plantilla de cotización actual** (formato PDF/Word, corporativo) y su contenido (ítems, montos, condiciones, validez).
7. **Catálogo de proveedores** existente (se cargará/reutilizará).
8. **Roles y usuarios** que participan por estación (para permisos).
9. **Moneda(s) e impuestos** usados en cotizaciones.
10. **Ambiente de pruebas** con Windows/IIS/mariaDB equivalente a producción (por LAN/`gestor://`).
11. **¿Retrocesos de estación?** Confirmar si una orden puede devolverse (rechazo/reproceso).

---

## 10. Preguntas Clarificadoras

### 10.1. Preguntas críticas (máx. 3 — cerrar antes de cotizar)

1. **¿El módulo reutiliza Documentos/Archivos existentes o es totalmente aislado?** (define modelo de datos y ±H/H).
2. **¿El flujo es estrictamente lineal o permite retrocesos** (rechazo de Ingeniería, re-cotización)? ¿Cuáles y con qué reglas?
3. **¿La cotización incluye montos, moneda, impuestos y un PDF con formato corporativo específico?** ¿Se envía por correo desde el sistema?

### 10.2. Preguntas secundarias (aclaraciones de alcance)

4. Las estaciones (RFQ…Cotización) ¿son **fijas** o el cliente podrá **crear nuevas** desde el sistema?
5. Los tiempos de ruta se definen en **horas o días**? ¿Se cuentan solo días/horas laborables?
6. Una orden puede afectar **múltiples clientes** o es 1 cliente por orden?
7. ¿Se requiere **notificación/alerta** (correo/in-app) al atrasarse una estación o al cambiar de estación?
8. El tablero por estación, ¿debe permitir **arrastrar y soltar** (drag & drop) para avanzar etapas?
9. ¿Se necesita manejo de **permisos por estación** (p. ej., solo Ingeniería puede cerrar la estación Ingeniería)?

## 11. Desglose de Horas Hombre (H/H)

Base: **Opción A (Integrado)**. Si el cliente elige Aislado, sumar +25 a +40 H/H (ver punto 5).

| Fase | Descripción | H/H |
|---|---|---|
| **F0 — Descubrimiento y Diseño** | Taller con cliente: cerrar acciones por estación, criterios SLA, modelo de datos, máquina de estados, prototipos de pantalla | 16 |
| **F1 — Núcleo de Datos** | Migraciones + modelos + enums + relaciones + traits (`Searchable`, `HasActivityLog`, `Lockable`) + seeders | 40 |
| **F2 — Motor de Workflow y Tiempos** | Máquina de estados, transiciones por rol, cálculo SLA, comando programado, colas, alertas | 48 |
| **F3 — UI Órdenes + Tablero por Estación** | Resource Filament: form/wizard, tablero kanban, timeline de etapas, acciones por etapa | 56 |
| **F4 — Rutas de Tiempos** | CRUD de rutas + duración por estación + validaciones | 20 |
| **F5 — Compra/Procura** | Vendor List, ofertas de proveedor con rango de precios, comparativa, generación de PO | 36 |
| **F6 — Cotización y Propuesta Formal** | Cotización versionada, ítems/montos, export PDF | 32 |
| **F7 — Métricas, KPIs y Reportes** | Widgets dashboard, KPIs por estación, export Excel | 32 |
| **F8 — Permisos, Integración Documentos, i18n, Despliegue** | Permisos/roles, integración con Documentos/Archivos existentes, traducción es, deploy | 20 |
| **F9 — QA / Pruebas / Documentación** | Pruebas funcionales, estrés, manual, ajustes | 32 |
| **Subtotal** | | **332** |
| **Contingencia (+20%)** | Riesgo técnico (workflow + SLA + cobertura test baja del proyecto) | 66 |
| **TOTAL ESTIMADO** | | **≈ 398 H/H** |

> Contingencia aplicada: **+20%** (complejidad media-alta + cobertura de test actual baja → riesgo de regresión).

---

## 12. Recursos Necesarios y Cronograma

**Equipo:**

| Rol | Dedicación | Responsabilidad |
|---|---|---|
| Dev Senior Laravel/Filament | Full-time | Backend, DB, motor de workflow, UI, métricas |
| QA / Tester | Parcial | Pruebas funcionales, SLA, estrés, regresión |
| Analista funcional / Product Owner (cliente) | Parcial | Talleres, cierre de acciones por estación, validaciones |
| Diseño (opcional) | Mínima | Layout de tablero y plantilla de cotización |

**Cronograma (referencial):**

- 1 dev full-time ≈ 40 h/semana → 398 h ≈ **10 semanas de desarrollo puro**.
- Sumando taller inicial, QA y validaciones del cliente → **11 a 13 semanas calendario**.
- Ruta crítica: **F0 (descubrimiento) → F1 → F2**. F3 en adelante puede paralelizarse parcialmente con QA.

---

## 13. Riesgos y Mitigación (Cero Regresiones)

| # | Riesgo | Impacto | Mitigación |
|---|---|---|---|
| R1 | Cliente no define acciones por estación | Alto (bloquea F1) | Taller F0 obligatorio antes de codificar |
| R2 | Decisión Integrado/Aislado pendiente | Alto (modelo de datos) | Cerrar antes de F1; recomendación = Integrado |
| R3 | Cobertura de test baja (5 archivos) | Alto | No tocar modelos existentes; cambios aditivos; pruebas nuevas del módulo |
| R4 | Cálculo de SLA pesado en pantalla | Medio | Pre-cálculo + comando programado + colas |
| R5 | Deploy híbrido Windows/IIS/LAN | Medio | Probar en ambiente igual a producción; no romper `gestor://` |
| R6 | Ambigüedad de moneda/impuestos/montos | Medio | Cerrar pregunta crítica #3 |
| R7 | Multi-proveedor/multi-ítem complejo | Medio | Diseño N:M desde F1; validar con ejemplos reales |

**Garantía de no regresión:** todo el módulo es **aditivo** (tablas y recursos nuevos). No se alteran tablas, rutas ni flujos actuales. La integración con Documentos (si se elige Opción A) es por registro adicional en el mapa polimórfico, sin modificar lógica existente.

---

## 14. Fuera de Alcance (v1) / Futuro

**Fuera de alcance inicial (salvo acuerdo):**
- Facturación electrónica / cobros.
- Portal externo para clientes (login de cliente).
- Integración con ERP/CRM de terceros.
- Envío automático de correo (si no se confirma en pregunta #3).

**Candidatos a fase futura:**
- Portal de cliente para seguimiento de su orden.
- Notificaciones automáticas multicanal.
- Historial de procura avanzado (mencionado como idea en `docs/info.md`).
- Migración masiva de órdenes históricas desde Excel.

---

## 15. Anexo — Mapeo al Código Actual

| Necesidad | Reutiliza | Ubicación |
|---|---|---|
| Patrón de Resource | Sí | `app/Filament/Resources/{Resource}/` + `Schemas`,`Tables`,`Pages` |
| Permisos dinámicos | Sí | `app/Models/Permission.php` + `database/seeders/AddNewModulePermissions.php` |
| Bitácora | Sí | `app/Traits/HasActivityLog.php` + spatie |
| Búsqueda global | Sí | `app/Traits/Searchable.php` + tabla `search_index` |
| Bloqueo edición | Sí | `app/Traits/Lockable.php` |
| Proveedores | Sí | `app/Models/Supplier.php` |
| Orden de compra | Sí | `app/Models/SupplierPurchaseOrder.php` |
| Documentos/Versiones | Condicional | `app/Models/Document.php`, `File.php` |
| Export Excel | Sí | `maatwebsite/excel` |
| Colas | Sí | `QUEUE_CONNECTION`, `queue:listen` |

---

*Documento generado como propuesta técnica. Versión 1.0.*
*Estado: PENDIENTE DE APROBACIÓN Y RESPUESTAS DEL CLIENTE.*
*No iniciar desarrollo sin "OK".*
