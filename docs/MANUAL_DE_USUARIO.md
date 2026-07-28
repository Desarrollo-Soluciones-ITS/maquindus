# Manual de Usuario - Sistema de Gestión Documental Maquindus

## Guía completa para usuarios finales

---

## Índice

1. [Introducción](#1-introducción)
2. [Acceso al Sistema](#2-acceso-al-sistema)
3. [Panel Principal (Dashboard)](#3-panel-principal-dashboard)
4. [Módulo de Equipos](#4-módulo-de-equipos)
5. [Módulo de Repuestos (Partes)](#5-módulo-de-repuestos-partes)
6. [Módulo de Contactos](#6-módulo-de-contactos)
7. [Módulo de Proveedores](#7-módulo-de-proveedores)
8. [Módulo de Órdenes de Compra](#8-módulo-de-órdenes-de-compra)
9. [Módulo de Documentos](#9-módulo-de-documentos)
10. [Módulo de Usuarios y Roles](#10-módulo-de-usuarios-y-roles)
11. [Funcionalidad "Ver en Carpeta"](#11-funcionalidad-ver-en-carpeta)
12. [Búsqueda Global](#12-búsqueda-global)
13. [Exportación de Datos](#13-exportación-de-datos)
14. [Solución de Problemas Comunes](#14-solución-de-problemas-comunes)

---

## 1. Introducción

**Maquindus** es un sistema de gestión documental diseñado para administrar documentos técnicos de equipos industriales. Permite:

- Gestionar equipos, repuestos, contactos y proveedores.
- Almacenar y versionar documentos técnicos (planos, catálogos, manuales, etc.).
- Previsualizar, descargar y localizar archivos en el servidor o en la red local.
- Controlar accesos mediante permisos y roles de usuario.

### 1.1. Requisitos Técnicos

- Navegador web: Chrome, Edge o Firefox (actualizado).
- Resolución de pantalla: Mínimo 1280x720 (recomendado 1920x1080).
- Acceso a Internet o red local para conectarse al servidor.

---

## 2. Acceso al Sistema

### 2.1. Inicio de Sesión

1. Abre tu navegador web.
2. Ingresa la dirección: **`https://192.168.0.4:81`**
3. Aparecerá la pantalla de inicio de sesión:

   ```
   ┌─────────────────────────────────┐
   │   MAQUINDUS                     │
   │                                 │
   │   Correo electrónico            │
   │   [____________________________]│
   │                                 │
   │   Contraseña                    │
   │   [____________________________]│
   │                                 │
   │   [🔓 Iniciar sesión]           │
   │                                 │
   │   ¿Olvidaste tu contraseña?     │
   └─────────────────────────────────┘
   ```

4. Ingresa tu **correo electrónico** y **contraseña** proporcionados por el administrador.
5. Haz clic en **"Iniciar sesión"**.

### 2.2. Cierre de Sesión

1. Haz clic en tu nombre de usuario (esquina superior derecha).
2. Selecciona **"Cerrar sesión"**.

### 2.3. Recuperación de Contraseña

Si olvidaste tu contraseña, contacta al administrador del sistema para que te asigne una nueva.

---

## 3. Panel Principal (Dashboard)

Al iniciar sesión, verás el panel principal con un resumen del sistema:

```
┌─────────────────────────────────────────────────────────────┐
│ [🏠 Dashboard] [⚙️ Equipos] [🔧 Repuestos] ... [👤 Usuario] │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐          │
│  │Equipos  │ │Document.│ │Repuestos│ │Contactos│          │
│  │   142   │ │   856   │ │   89    │ │   34    │          │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘          │
│                                                             │
│  ┌────────────────────┐  ┌────────────────────┐            │
│  │  Últimos Equipos   │  │  Últimos Documentos│            │
│  │ ┌────────────────┐ │  │ ┌────────────────┐ │            │
│  │ │Compresor Atlas │ │  │ │Manual Op.      │ │            │
│  │ │Bomba Centrífuga│ │  │ │Plano Despiece  │ │            │
│  │ │...             │ │  │ │...             │ │            │
│  │ └────────────────┘ │  │ └────────────────┘ │            │
│  └────────────────────┘  └────────────────────┘            │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  Actividad Reciente                                │    │
│  │  • Juan creó el equipo "Compresor Atlas"           │    │
│  │  • María actualizó el documento "Manual Op."      │    │
│  │  • Pedro archivó el repuesto "Rodamiento SKF"     │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

### 3.1. Barra de Navegación Superior

- **Menú principal:** Acceso a todos los módulos del sistema.
- **Búsqueda global:** 🔍 Campo para buscar en todo el sistema.
- **Notificaciones:** 🔔 Alertas y notificaciones.
- **Perfil de usuario:** 👤 Tu nombre, configuración y cierre de sesión.

### 3.2. Widgets del Dashboard

| Widget | Descripción |
|---|---|
| **Equipos** | Número total de equipos registrados |
| **Documentos** | Total de documentos almacenados |
| **Repuestos** | Total de repuestos registrados |
| **Contactos** | Total de contactos registrados |
| **Últimos Equipos** | Lista de los equipos más recientes |
| **Últimos Documentos** | Documentos añadidos recientemente |
| **Actividad Reciente** | Registro de las últimas acciones de usuarios |

---

## 4. Módulo de Equipos

### 4.1. Listado de Equipos

Navegación: **Equipos → "Equipos"** en el menú lateral.

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Equipos               [+ Crear equipo] [⬇ Exportar]   │
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar equipo...                                       │
├──────┬──────────┬──────────┬──────────────────┬────────────┤
│Nombre│ Modelo   │ Serial   │ Descripción      │ Fecha      │
├──────┼──────────┼──────────┼──────────────────┼────────────┤
│Comp. │MODEL123  │SER001    │Compresor centríf.│15/07/2026  │
│Bomba │BOM-X100  │SER002    │Bomba centrífuga  │14/07/2026  │
│...   │          │          │                  │            │
├──────┴──────────┴──────────┴──────────────────┴────────────┤
│ [📄 Ver] [✏️ Editar] [🗑️ Archivar] [📂 Ver en carpeta]  │
└─────────────────────────────────────────────────────────────┘
```

**Acciones disponibles en la tabla:**

| Acción | Icono | Descripción |
|---|---|---|
| **Ver en carpeta** | 📂 | Abre el Explorador en la ubicación del archivo |
| **Ver** | 📄 | Muestra los detalles completos del equipo |
| **Editar** | ✏️ | Modifica los datos del equipo |
| **Archivar** | 🗑️ | Archiva (oculta) el equipo |
| **Exportar** | ⬇ | Descarga la lista en Excel |

### 4.2. Crear un Nuevo Equipo

1. Haz clic en **"+ Crear equipo"**.
2. Completa el formulario:

   ```
   ┌──────────────────────────────────────────────────────────┐
   │  Crear Equipo                                            │
   ├──────────────────────────────────────────────────────────┤
   │                                                          │
   │  Equipo *          │  Modelo *          │ Serial *      │
   │  [Compresor Atlas ]│  [MODELOX123     ]│ [SERIAL0001  ]│
   │                                                          │
   │  Tipo *            │  Fecha fabricación*│ Descripción  │
   │  [Bomba centrífuga]│  [15/07/2026     ]│ [Compresor   ]│
   │                                                          │
   │  Proveedores vinculados                                  │
   │  [Proveedor A ✕] [Proveedor B ✕]                        │
   │                                                          │
   │  ┌────────────────────────────────────────────────────┐  │
   │  │ Órdenes de compra proveedor                        │  │
   │  │ [+ Nueva orden]                                    │  │
   │  │ Órdenes registradas: [OC-001 ✕] [OC-002 ✕]        │  │
   │  └────────────────────────────────────────────────────┘  │
   │                                                          │
   │              [💾 Guardar]     [❌ Cancelar]               │
   └──────────────────────────────────────────────────────────┘
   ```

**Campos del formulario:**

| Campo | Obligatorio | Descripción |
|---|---|---|
| **Equipo** | Sí | Nombre del equipo (máx. 80 caracteres) |
| **Modelo** | Sí | Modelo del equipo (solo letras y números) |
| **Serial** | Sí | Número de serie (solo letras y números) |
| **Tipo** | Sí | Tipo de equipo (ej: Bomba centrífuga) |
| **Fecha de fabricación** | Sí | Fecha de fabricación |
| **Descripción** | Sí | Breve descripción del equipo |
| **Proveedores vinculados** | No | Seleccionar proveedores asociados |
| **Órdenes de compra** | No | Ordenes de compra asociadas |

3. Haz clic en **"Guardar"**.

### 4.3. Ver Detalles de un Equipo

Haz clic en el botón **"Ver"** (📄) junto al equipo deseado.

#### 4.3.1. Vista General

La pantalla de detalles del equipo se organiza en múltiples pestañas (subtablas) que agrupan la información relacionada:

```
┌──────────────────────────────────────────────────────────────────┐
│  ← Equipos / Compresor Atlas                                     │
│                                                                  │
│  Compresor Atlas                              [✏️ Editar equipo] │
│  Modelo: MODELOX123  Serial: SERIAL0001                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  [📄 Documentos] [📋 Especificación Técnica ▾] [📦 Órdenes de C.]│
│  [🔧 Repuestos] [📝 Consultas de Campo] [📊 Reportes]           │
│  [📋 Actividad]                                                  │
├──────────────────────────────────────────────────────────────────┤
│  (Contenido de la pestaña seleccionada)                          │
└──────────────────────────────────────────────────────────────────┘
```

**Nota:** La pestaña **"Especificación Técnica"** es un menú desplegable que contiene varias sub-pestañas agrupadas.

#### 4.3.2. Pestaña: Documentos

```
┌──────────────────────────────────────────────────────────────────┐
│  Documentos                              [+ Nuevo documento]     │
├──────────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar documento...                                    ⬇️ ] │
├─────────┬──────────┬──────────┬──────────┬──────────────────────┤
│ Nombre  │ Tipo     │Categoría │  Fecha   │ Acciones             │
├─────────┼──────────┼──────────┼──────────┼──────────────────────┤
│Manual   │ PDF      │ Manual   │15/07/2026│ [📂⬇👁️]             │
│Catálogo │ PDF      │Catálogo  │14/07/2026│ [📂⬇👁️]             │
│...      │          │          │          │                      │
└─────────┴──────────┴──────────┴──────────┴──────────────────────┘
```

**Acciones disponibles en cada documento:**

| Acción | Icono | Descripción |
|---|---|---|
| **Ver en carpeta** | 📂 | Abre el Explorador en la ubicación del archivo |
| **Descargar** | ⬇️ | Descarga el archivo original |
| **Previsualizar** | 👁️ | Abre el archivo en el navegador |

Al hacer clic en el **nombre** de un documento, se abre la vista detallada del mismo con sus versiones.

#### 4.3.3. Pestaña: Especificación Técnica (Sub-pestañas)

Esta pestaña es un menú desplegable que contiene las siguientes sub-pestañas:

##### Hoja de Datos

Registros de hojas de datos técnicas del equipo. Cada registro puede incluir un documento anexo (PDF, hoja de cálculo, etc.).

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Hojas de datos   [+ Crear hoja datos] │
├──────────────┬─────────┬──────────┬───────┬─────────────────────┤
│ Nº hoja datos│ Rev     │ Fecha    │ Anexo │ Acciones            │
├──────────────┼─────────┼──────────┼───────┼─────────────────────┤
│ HD-001       │ A       │15/07/2026│   ✅  │ [📂👁️✏️🗑️]          │
│ HD-002       │ B       │14/07/2026│   ❌  │ [📂👁️✏️🗑️]          │
└──────────────┴─────────┴──────────┴───────┴─────────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nº hoja datos** | Número de identificación de la hoja de datos |
| **Rev** | Revisión del documento |
| **Fecha** | Fecha del documento |
| **Documento anexo** | Archivo adjunto (PDF, imagen, etc.) |

##### Planos

Registros de planos técnicos del equipo.

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Planos          [+ Crear plano]       │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nº de planos │ Nombre   │ Rev     │ Fecha    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ PL-001       │ Plano    │ A       │15/07/2026│ [📂👁️✏️🗑️]       │
│              │ general  │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nº de planos** | Número de identificación del plano |
| **Nombre** | Nombre o descripción del plano |
| **Rev** | Revisión del plano |
| **Fecha** | Fecha del plano |

##### Catálogos

Registros de catálogos comerciales del equipo.

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Catálogos      [+ Crear catálogo]     │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nº catálogo  │ Nombre   │ Rev     │ Fecha    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ CAT-001      │ Catálogo │ A       │15/07/2026│ [📂👁️✏️🗑️]       │
│              │ general  │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nº catálogo** | Número de identificación del catálogo |
| **Nombre** | Nombre del catálogo |
| **Rev** | Revisión |
| **Fecha** | Fecha del catálogo |

##### Manuales

Registros de manuales de operación, mantenimiento, instalación, etc.

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Manuales       [+ Crear manual]       │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nombre       │ Rev      │ Fecha   │ Anexo    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ Manual       │ A        │15/07/26 │   ✅     │ [📂👁️✏️🗑️]       │
│ operación    │          │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nombre** | Nombre del manual |
| **Rev** | Revisión |
| **Fecha** | Fecha del manual |
| **Documento anexo** | Archivo del manual en PDF |

##### Especificación Técnica (Revisiones)

Registros de las revisiones de especificaciones técnicas detalladas.

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Revisiones    [+ Crear revisión]      │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nombre rev.  │ Rev      │ Fecha   │ Anexo    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ Espec.       │ A        │15/07/26 │   ✅     │ [📂👁️✏️🗑️]       │
│ técnica      │          │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nombre rev.** | Nombre de la revisión de especificación técnica |
| **Rev** | Número de revisión |
| **Fecha** | Fecha de la revisión |
| **Documento anexo** | Archivo de la especificación |

##### Normas

Registros de normas y estándares aplicables al equipo.

```
┌──────────────────────────────────────────────────────────────────┐
│  Especificación técnica · Normas         [+ Crear norma]        │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nombre       │ Rev      │ Fecha   │ Anexo    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ Norma ISO    │ A        │15/07/26 │   ✅     │ [📂👁️✏️🗑️]       │
│ 9001         │          │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

**Campos del formulario:**

| Campo | Descripción |
|---|---|
| **Nombre** | Nombre de la norma |
| **Rev** | Revisión de la norma |
| **Fecha** | Fecha de la norma |
| **Documento anexo** | Archivo de la norma |

**Acciones comunes en todas las sub-pestañas de Especificación Técnica:**

| Acción | Icono | Descripción |
|---|---|---|
| **Ver en carpeta** | 📂 | Abre el Explorador en la ubicación del archivo anexo |
| **Abrir anexo** | 👁️ | Abre el documento anexo en el navegador |
| **Ver** | 📄 | Muestra los detalles del registro |
| **Editar** | ✏️ | Modifica los campos del registro |
| **Eliminar** | 🗑️ | Elimina el registro (solo visible si no está archivado) |
| **Restaurar** | ♻️ | Restaura un registro eliminado |

**Nota:** Tanto al **Crear** como al **Editar**, puedes adjuntar un **Documento anexo** (archivo PDF, imagen, etc.). Si editas un registro existente y adjuntas un nuevo archivo, se agrega automáticamente como una **nueva versión** del documento, conservando el historial de versiones anteriores.

#### 4.3.4. Pestaña: Órdenes de Compra

Muestra las órdenes de compra asociadas al equipo. Cada orden está vinculada a un proveedor.

```
┌──────────────────────────────────────────────────────────────────┐
│  Órdenes de Compra Proveedor                                    │
├──────────────┬──────────────┬────────────────┬──────────────────┤
│ Nº Orden     │ Proveedor    │ Descripción    │ Fecha            │
├──────────────┼──────────────┼────────────────┼──────────────────┤
│ OC-001       │ Proveedor A  │ Compra         │ 15/07/2026       │
│              │              │ compresor      │                  │
└──────────────┴──────────────┴────────────────┴──────────────────┘
```

Puedes crear nuevas órdenes de compra directamente desde aquí o vincular órdenes existentes.

#### 4.3.5. Pestaña: Repuestos

Muestra los repuestos (partes) vinculados a este equipo. Los repuestos se gestionan desde el módulo de **Repuestos** y se asocian a equipos desde allí.

#### 4.3.6. Pestaña: Consultas de Campo

Registros de consultas técnicas realizadas en campo sobre el equipo.

```
┌──────────────────────────────────────────────────────────────────┐
│  Consultas de Campo                     [+ Crear consulta]      │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nombre doc.  │ Rev      │ Fecha   │ Anexo    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ Consulta     │ A        │15/07/26 │   ✅     │ [📂👁️✏️🗑️]       │
│ campo #1     │          │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

#### 4.3.7. Pestaña: Reportes

Reportes asociados al equipo.

```
┌──────────────────────────────────────────────────────────────────┐
│  Reportes                              [+ Crear reporte]        │
├──────────────┬──────────┬─────────┬──────────┬──────────────────┤
│ Nombre doc.  │ Rev      │ Fecha   │ Anexo    │ Acciones         │
├──────────────┼──────────┼─────────┼──────────┼──────────────────┤
│ Reporte      │ A        │15/07/26 │   ✅     │ [📂👁️✏️🗑️]       │
│ técnico #1   │          │         │          │                  │
└──────────────┴──────────┴─────────┴──────────┴──────────────────┘
```

#### 4.3.8. Pestaña: Actividad

Muestra un registro cronológico de todas las acciones realizadas sobre el equipo:

- Creación del equipo.
- Modificaciones de campos (nombre, modelo, serial, etc.).
- Adición de documentos, planos, catálogos, etc.
- Archivado y restauración del equipo.

Cada entrada muestra: **quién** hizo **qué** y **cuándo**.

#### 4.3.9. Navegación entre Sub-pestañas

Al hacer clic en cualquier registro dentro de una sub-pestaña (ej: un plano, una hoja de datos), se abren tres acciones principales en la parte superior:

1. **Ver (👁️):** Muestra los detalles del registro en un panel lateral.
2. **Editar (✏️):** Abre un formulario para modificar los campos del registro.
3. **Eliminar (🗑️):** Archivado suave del registro (se puede restaurar luego).

Para los registros que tienen **documento anexo**, también están disponibles:
4. **Ver en carpeta (📂):** Abre el Explorador en la ubicación del archivo.
5. **Abrir anexo (👁️):** Previsualiza el archivo en el navegador.

### 4.4. Editar un Equipo

1. Haz clic en **"Editar"** (✏️) junto al equipo.
2. Modifica los campos necesarios.
3. Haz clic en **"Guardar"**.

### 4.5. Archivar un Equipo

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Equipos               [+ Crear equipo] [⬇ Exportar]   │
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar equipo...   Archivados: [Ocultar archivados ▼]]│
├──────┬──────────┬──────────┬──────────────────┬────────────┤
│Nombre│ Modelo   │ Serial   │ Descripción      │ Fecha      │
├──────┼──────────┼──────────┼──────────────────┼────────────┤
│Comp. │MODEL123  │SER001    │Compresor centríf.│15/07/2026  │
│...   │          │          │                  │            │
├──────┴──────────┴──────────┴──────────────────┴────────────┤
│ [📄 Ver] [✏️ Editar] [🗑️ Archivar] [📂 Ver en carpeta]  │
└─────────────────────────────────────────────────────────────┘
```

1. Localiza el equipo que deseas archivar en la tabla.
2. Haz clic en **"Archivar"** (🗑️) junto al equipo.
3. Aparecerá un diálogo de confirmación:

   ```
   ┌──────────────────────────────────────────────────┐
   │  🗃️ Archivar Equipo                              │
   │                                                  │
   │  ¿Estás seguro de que deseas archivar este       │
   │  equipo? Esta acción lo moverá a la carpeta      │
   │  'Superado' y lo ocultará en la interfaz.        │
   │                                                  │
   │        [🚫 Cancelar]    [🗃️ Archivar]            │
   └──────────────────────────────────────────────────┘
   ```

4. Haz clic en **"Archivar"** para confirmar.
5. El equipo y **todos sus documentos asociados** se mueven a la carpeta `Superado/` en el almacenamiento.
6. El equipo **desaparece** de la lista principal (ya no es visible).

**¿Qué pasa cuando archivo un equipo?**
- El equipo se marca como "archivado" (soft delete).
- **Todos sus documentos y archivos** se mueven físicamente a la carpeta `Superado/`.
- El equipo deja de aparecer en la lista principal.
- Los datos **no se pierden**, solo se ocultan.

### 4.6. Restaurar un Equipo Archivado

**IMPORTANTE:** Para ver y restaurar equipos archivados, **primero debes cambiar el filtro de archivados** en la tabla. Por defecto, los equipos archivados están ocultos.

**Paso 1: Mostrar los equipos archivados**

En la tabla de equipos, busca el selector de filtro **"Archivados"** en la parte superior (junto al campo de búsqueda). Tiene 3 opciones:

| Opción del filtro | ¿Qué muestra? |
|---|---|
| **"Ocultar archivados"** (valor por defecto) | Solo equipos activos (no archivados) |
| **"Incluir archivados"** | Todos: activos + archivados |
| **"Solo archivados"** | Únicamente los equipos archivados |

Para restaurar, selecciona **"Incluir archivados"** o **"Solo archivados"**:

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Equipos               [+ Crear equipo] [⬇ Exportar]   │
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar equipo...   Archivados: [Incluir archivados ▼]]│
├──────┬──────────┬──────────┬──────────────────┬────────────┤
│Nombre│ Modelo   │ Serial   │ Descripción      │ Fecha      │
├──────┼──────────┼──────────┼──────────────────┼────────────┤
│Comp. │MODEL123  │SER001    │Compresor centríf.│15/07/2026  │ ← Activo
│Bomba │BOM-X100  │SER002    │Bomba centrífuga  │14/07/2026  │ ← Archivado
│...   │          │          │                  │            │
├──────┴──────────┴──────────┴──────────────────┴────────────┤
│ [📄 Ver] [✏️ Editar] [🗑️ Archivar] [♻️ Restaurar]     │
└─────────────────────────────────────────────────────────────┘
```

**Paso 2: Restaurar el equipo**

1. Una vez que el filtro está en **"Incluir archivados"** o **"Solo archivados"**, los equipos archivados aparecerán en la tabla.
2. Localiza el equipo archivado que deseas recuperar.
3. Haz clic en **"Restaurar"** (♻️) — el icono de flecha curva verde.
4. El sistema moverá los archivos de vuelta desde `Superado/` a su ubicación original.
5. El equipo vuelve a aparecer en la lista principal cuando cambies el filtro a **"Ocultar archivados"**.

**Nota importante:** Si en la carpeta original ya existe un archivo con el mismo nombre, la restauración **fallará** y recibirás una notificación de error. En ese caso, debe resolverse manualmente el conflicto de nombres.

---

## 5. Módulo de Repuestos (Partes)

### 5.1. Listado de Repuestos

Navegación: **"Repuestos"** en el menú lateral.

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Repuestos              [+ Crear repuesto] [⬇ Exportar]│
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar repuesto...                                     │
├──────┬──────────┬──────────┬──────────┬────────────────────┤
│ Nombre│Nº Pieza  │ Marca    │Categoría │ Equipos Vinculados│
├──────┼──────────┼──────────┼──────────┼────────────────────┤
│R odam.│RF-100    │SKF       │Rodamiento│ Compresor, Bomba  │
│...    │          │          │          │                    │
└──────┴──────────┴──────────┴──────────┴────────────────────┘
```

### 5.2. Crear un Repuesto

1. Haz clic en **"+ Crear repuesto"**.
2. Completa el formulario:

| Campo | Obligatorio | Descripción |
|---|---|---|
| **Nombre** | Sí | Nombre del repuesto |
| **Nº de pieza** | Sí | Número de pieza o referencia |
| **Marca** | Sí | Marca del repuesto |
| **Categoría** | Sí | Categoría (Rodamiento, Sello, etc.) |
| **Equipos vinculados** | No | Equipos donde se utiliza este repuesto |

3. Haz clic en **"Guardar"**.

### 5.3. Acciones con Repuestos

- **Ver**: Muestra los detalles del repuesto (incluye documentos asociados como catálogos, planos).
- **Editar**: Modifica los datos del repuesto.
- **Archivar**: Oculta el repuesto.

---

## 6. Módulo de Contactos

### 6.1. Listado de Contactos

Navegación: **"Contactos"** en el menú lateral.

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Contactos             [+ Crear contacto] [⬇ Exportar] │
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar contacto...                                     │
├──────────┬────────────┬──────────────┬─────────────────────┤
│ Nombre   │ Correo     │ Teléfono     │ Compañía            │
├──────────┼────────────┼──────────────┼─────────────────────┤
│ Juan Péz │juan@ej.com│+58 412-123...│ Empresa ABC         │
│...       │            │              │                     │
└──────────┴────────────┴──────────────┴─────────────────────┘
```

### 6.2. Crear un Contacto

1. Haz clic en **"+ Crear contacto"**.
2. Completa los campos: Nombre, Correo, Teléfono, Compañía, etc.
3. Haz clic en **"Guardar"**.

---

## 7. Módulo de Proveedores

### 7.1. Listado de Proveedores

Navegación: **"Proveedores"** en el menú lateral.

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Proveedores           [+ Crear proveedor] [⬇ Exportar]│
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar proveedor...                                    │
├──────────┬────────────┬──────────────┬─────────────────────┤
│ Nombre   │ RIF        │ Contacto     │ Equipos Vinculados  │
├──────────┼────────────┼──────────────┼─────────────────────┤
│ProveedorA│J-12345678-9│Juan Pérez    │ Compresor, Bomba    │
│...       │            │              │                     │
└──────────┴────────────┴──────────────┴─────────────────────┘
```

### 7.2. Crear un Proveedor

1. Haz clic en **"+ Crear proveedor"**.
2. Completa los campos: Nombre, RIF, Contacto, Dirección, etc.
3. Haz clic en **"Guardar"**.

---

## 8. Módulo de Órdenes de Compra

### 8.1. Listado de Órdenes

Navegación: **"Órdenes de Compra"** en el menú lateral.

### 8.2. Crear una Orden

1. Haz clic en **"+ Crear orden"**.
2. Completa:

| Campo | Descripción |
|---|---|
| **Nº de orden** | Número único de orden de compra |
| **Proveedor** | Seleccionar proveedor de la lista |
| **Descripción** | Detalle de la orden |

3. Haz clic en **"Guardar"**.

---

## 9. Módulo de Documentos

### 9.1. Visión General

Los documentos se gestionan **desde la vista de cada entidad** (Equipo, Repuesto, Contacto, Proveedor). No se crean documentos de forma independiente.

### 9.2. Crear un Documento

Desde la vista de un **Equipo** (o Repuesto/Contacto/Proveedor):

1. Ve a la entidad (ej: Equipo → "Ver").
2. En la pestaña **"Documentos"**, haz clic en **"+ Nuevo documento"**.
3. Completa el formulario:

```
┌──────────────────────────────────────────────────────────────┐
│  Nuevo Documento                                             │
│  Equipo: Compresor Atlas                                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Nombre *                                                    │
│  [Manual de operación e instalación                        ] │
│                                                              │
│  Categoría                                                   │
│  [📁 Manual               ▼]                                 │
│                                                              │
│  Archivo *                                                   │
│  [➕ Seleccionar archivo]  manual_operacion.pdf              │
│                                                              │
│  Fecha de revisión *                                         │
│  [📅 15/07/2026            ]                                 │
│                                                              │
│             [💾 Guardar]     [❌ Cancelar]                    │
└──────────────────────────────────────────────────────────────┘
```

**Campos del formulario:**

| Campo | Obligatorio | Descripción |
|---|---|---|
| **Nombre** | Sí | Nombre del documento |
| **Categoría** | No | Tipo de documento (varía según la entidad) |
| **Archivo** | Sí | El archivo a subir (PDF, DWG, imagen, etc.) |
| **Fecha de revisión** | Sí* | Fecha de revisión del documento (solo para Equipos y Repuestos) |

**Categorías disponibles (para Equipos y Repuestos):**

| Categoría | Descripción |
|---|---|
| Plano | Planos técnicos del equipo |
| Catálogo | Catálogos comerciales |
| Hoja de datos | Hojas de datos técnicos |
| Especificación técnica | Especificaciones técnicas detalladas |
| Norma | Normas y estándares aplicables |
| Manual | Manuales de operación, mantenimiento, etc. |
| Reporte | Reportes técnicos |
| Consulta de campo | Consultas realizadas en campo |
| Repuesto | Documentación de repuestos |

### 9.3. Subir una Nueva Versión de un Documento

1. Desde la vista del documento (haz clic en el nombre del documento en la tabla).
2. Ve a la pestaña **"Versiones"**.
3. Haz clic en **"Añadir versión"**.
4. Selecciona el archivo de la nueva versión.
5. El sistema automáticamente:
   - Incrementa el número de versión (V1, V2, V3...).
   - Renombra el archivo con el formato: `NombreDocumento - V2.pdf`.
   - Detecta el tipo MIME del archivo.
6. Haz clic en **"Añadir"**.

### 9.4. Acciones sobre Documentos

Cada documento/fila tiene las siguientes acciones:

| Acción | Icono | Descripción |
|---|---|---|
| **Previsualizar** | 👁️ | Ver el archivo en el navegador (PDF, imágenes, etc.) |
| **Ver en carpeta** | 📂 | Abrir el Explorador en la ubicación del archivo |
| **Descargar** | ⬇ | Descargar el archivo original |
| **Ver versión** | 📄 | Ver detalles de la versión (fecha, tipo MIME) |

### 9.5. Previsualizar un Documento

1. Haz clic en el botón **"Previsualizar"** (👁️).
2. El archivo se abre en una nueva pestaña del navegador.
3. Puedes navegar, hacer zoom e imprimir (para PDFs).

**Formatos soportados:**

| Formato | Previsualización |
|---|---|
| PDF | Vista completa en el navegador |
| Imágenes (JPG, PNG, etc.) | Vista previa en el navegador |
| DWG (AutoCAD) | No previsualizable (se descarga) |
| Office (Word, Excel) | No previsualizable (se descarga) |

### 9.6. Descargar un Documento

1. Haz clic en el botón **"Descargar"** (⬇).
2. El navegador inicia la descarga del archivo original.
3. El archivo conserva su nombre original con el número de versión.

---

## 10. Módulo de Usuarios y Roles

### 10.1. Gestión de Usuarios

Navegación: **"Usuarios"** en el menú lateral (solo accesible para administradores).

```
┌─────────────────────────────────────────────────────────────┐
│ [←] Usuarios               [+ Crear usuario]               │
├─────────────────────────────────────────────────────────────┤
│ 🔍 [Buscar usuario...                                      │
├──────────┬──────────────┬────────────────┬─────────────────┤
│ Nombre   │ Correo       │ Rol            │ Estado          │
├──────────┼──────────────┼────────────────┼─────────────────┤
│ Juan Pérez│juan@ej.com  │ Administrador  │ Activo          │
│ María López│maria@ej.com│ Editor         │ Activo          │
│...        │              │                │                 │
└──────────┴──────────────┴────────────────┴─────────────────┘
```

### 10.2. Crear un Usuario

1. Haz clic en **"+ Crear usuario"**.
2. Completa: Nombre, Correo, Contraseña, Rol.
3. Haz clic en **"Guardar"**.

### 10.3. Roles y Permisos

| Rol | Descripción |
|---|---|
| **Administrador** | Acceso completo a todas las funciones |
| **Editor** | Puede crear, editar y gestionar documentos |
| **Consultor** | Solo puede ver y descargar documentos |
| **Técnico** | Puede ver, crear documentos y subir versiones |

---

## 11. Funcionalidad "Ver en Carpeta"

### 11.1. ¿Qué hace?

El botón **"Ver en carpeta"** (📂) abre el Explorador de Windows en la ubicación exacta donde está almacenado físicamente el archivo, con el archivo seleccionado.

### 11.2. ¿Cómo usarlo?

1. Desde cualquier tabla de documentos, haz clic en el botón **📂 "Ver en carpeta"**.
2. El navegador mostrará un diálogo: **"Abrir gestor://?"**.
3. Haz clic en **"Abrir"** o **"Aceptar"**.
4. Se abrirá el Explorador de Windows en la carpeta del archivo, con el archivo seleccionado.

### 11.3. Solución de Problemas

**El botón no aparece:**
- El documento no tiene archivos cargados.
- No tienes permisos para ver archivos.

**Al hacer clic no pasa nada:**
- Si es la primera vez, el navegador pregunta "¿Abrir gestor://?" — acepta.
- Si el protocolo no está instalado, ejecuta en tu PC como Administrador:
  ```cmd
  \\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
  ```

---

## 12. Búsqueda Global

### 12.1. Buscar en Todo el Sistema

1. En la barra superior, haz clic en el campo de búsqueda 🔍 o presiona `Ctrl + K`.
2. Escribe el término a buscar (nombre de equipo, documento, etc.).
3. Los resultados se muestran agrupados por categoría:

```
┌─────────────────────────────────────────────────────────────┐
│  Buscar: [compresor                            ✕]          │
├─────────────────────────────────────────────────────────────┤
│  Equipos (3)                                                │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Compresor Atlas              → Equipo                │   │
│  │ Compresor reciprocante       → Equipo                │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                             │
│  Documentos (5)                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Manual compresor Atlas        → Documento            │   │
│  │ Catálogo compresor            → Documento            │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

4. Haz clic en cualquier resultado para ir directamente a ese registro.

---

## 13. Exportación de Datos

### 13.1. Exportar a Excel

Desde cualquier listado (Equipos, Documentos, Repuestos, etc.):

1. Haz clic en el botón **"Exportar"** (⬇) en la parte superior de la tabla.
2. El sistema genera un archivo Excel (`.xlsx`) con los datos actuales.
3. El archivo se descarga automáticamente.

**Nota:** La exportación incluye los datos filtrados actualmente en la tabla.

---

## 14. Solución de Problemas Comunes

### 14.1. No puedo iniciar sesión

- Verifica que el correo y la contraseña sean correctos.
- Contacta al administrador si olvidaste tu contraseña.
- Asegúrate de estar usando la dirección correcta: `https://192.168.0.4:81`.

### 14.2. No puedo ver un documento

- Es posible que no tengas permisos suficientes (permiso `documents.show`).
- El documento puede estar archivado.
- El archivo puede haber sido eliminado del servidor.

### 14.3. No puedo descargar un documento

- Necesitas el permiso `documents.download`.
- Si el archivo es muy grande, puede tardar en descargarse.

### 14.4. No aparece el botón "Ver en carpeta"

- El documento no tiene archivos cargados.
- El archivo está en una ubicación no accesible.
- No tienes permisos (`documents.show_file`).

### 14.5. Error 500 (Internal Server Error)

- Refresca la página (`F5`).
- Si persiste, contacta al administrador.
- Revisa los logs del servidor.

### 14.6. El sistema se ve mal o desordenado

- Limpia la caché del navegador (`Ctrl + Shift + Supr` → "Caché").
- Asegúrate de usar un navegador actualizado (Chrome, Edge, Firefox).

---

## Apéndice A: Permisos del Sistema

| Permiso | Descripción |
|---|---|
| `equipments.view` | Ver listado de equipos |
| `equipments.create` | Crear nuevos equipos |
| `equipments.edit` | Editar equipos existentes |
| `equipments.show` | Ver detalles de un equipo |
| `equipments.delete` | Archivar equipos |
| `equipments.restore` | Restaurar equipos archivados |
| `documents.view` | Ver listado de documentos |
| `documents.show` | Ver detalles de documentos |
| `documents.show_file` | Ver archivos (previsualizar, ver en carpeta) |
| `documents.download` | Descargar documentos |
| `documents.edit` | Editar documentos |
| `documents.delete` | Archivar documentos |
| `documents.restore` | Restaurar documentos archivados |
| `files.create` | Subir nuevas versiones de archivos |
| `files.download` | Descargar archivos individuales |
| `files.show` | Ver detalles de versiones |
| `purchase_orders.create` | Crear órdenes de compra |
| `parts.view` | Ver listado de repuestos |
| `parts.create` | Crear repuestos |
| `parts.edit` | Editar repuestos |
| `parts.delete` | Archivar repuestos |
| `people.view` | Ver listado de contactos |
| `people.create` | Crear contactos |
| `people.edit` | Editar contactos |
| `people.delete` | Archivar contactos |
| `suppliers.view` | Ver listado de proveedores |
| `suppliers.create` | Crear proveedores |
| `suppliers.edit` | Editar proveedores |
| `suppliers.delete` | Archivar proveedores |
| `roles.view` | Ver roles |
| `roles.create` | Crear roles |
| `roles.edit` | Editar roles |
| `roles.delete` | Eliminar roles |
| `users.*` | Gestión completa de usuarios (solo administradores) |

---

## Apéndice B: Atajos de Teclado

| Atajo | Acción |
|---|---|
| `Ctrl + K` | Abrir búsqueda global |
| `Ctrl + S` | Guardar formulario actual |
| `Escape` | Cerrar modal o cancelar |
| `F5` | Refrescar página actual |

---

## Apéndice C: Glosario

| Término | Definición |
|---|---|
| **Documentable** | Entidad que puede tener documentos asociados (Equipo, Repuesto, Contacto, Proveedor) |
| **Versión** | Cada archivo subido para un documento (V1, V2, V3...) |
| **Archivar** | Ocultar un registro sin eliminarlo definitivamente |
| **Restaurar** | Recuperar un registro archivado |
| **MIME** | Tipo de archivo (PDF, imagen, DWG, etc.) |
| **UNC** | Ruta de red con formato `\\servidor\recurso\carpeta` |
| **Protocolo gestor://** | Protocolo personalizado para abrir carpetas desde el navegador |

---

*Documento generado el 27/07/2026 — Versión 1.0*
*Sistema Maquindus - Gestión Documental*