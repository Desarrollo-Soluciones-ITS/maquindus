# 🖥️ Guía de Gestión UI — Módulo Equipos

## 🔷 Acceso a la UI

```
https://192.168.0.4:81/equipment
```

Esta URL muestra la lista de equipos. Cada fila representa un equipo y muestra su código, nombre, marca, modelo y acciones disponibles.

---

## 🔷 Navegación por Equipo

Al hacer clic en el nombre de un equipo, se abre la **vista detalle** con las siguientes pestañas (RelationManagers):

| # | Pestaña en UI | Carpeta en disco | Modelo |
|---|---|---|---|
| 1 | **Hojas de datos** | `Especificaciones Tecnicas/Hoja De Datos/` | `EquipmentDataSheet` |
| 2 | **Planos** | `Especificaciones Tecnicas/Planos/` | `EquipmentBlueprint` |
| 3 | **Catálogos** | `Especificaciones Tecnicas/Catálogos/` | `EquipmentCatalog` |
| 4 | **Manuales** | `Especificaciones Tecnicas/Manuales/` | `EquipmentManual` |
| 5 | **Revisiones** | `Especificaciones Tecnicas/Revisiones/` | `EquipmentTechnicalSpecification` |
| 6 | **Normas** | `Especificaciones Tecnicas/Normas/` | `EquipmentStandard` |
| 7 | **Documentos** | `General/` | `Document` (polimórfico) |
| 8 | **Órdenes de compra** | `General/PO-*/` | `SupplierPurchaseOrder` |
| 9 | **Repuestos** | `Repuestos/` | `EquipmentSparePart` |
| 10 | **Consultas de campo** | `Consultas de Campo/` | `EquipmentFieldQuery` |
| 11 | **Reportes** | `Reportes/` | `EquipmentReport` |

---

## 🔷 Grupo "Especificación Técnica" (pestañas 1-6)

Agrupadas bajo el encabezado **"Especificación técnica"** en la UI. Cada una tiene:

- **Tabla** con columnas: código, nombre/descripción, fecha, archivos
- **Botón "Crear"** (si el usuario tiene permiso)
- **Acciones por fila**: Ver, Editar, Ver en carpeta, Abrir archivo

**Ejemplo visual — Pestaña "Hojas de datos":**
```
┌──────────────────────────────────────────────────┐
│ Hojas de datos          [+ Crear]  [Exportar]    │
├──────────┬────────────────┬──────────┬───────────┤
│ Código   │ Nombre         │ Fecha    │ Archivos  │
├──────────┼────────────────┼──────────┼───────────┤
│ DS-001   │ Hoja técnica A │ 12/03/26 │ 👁️ 📂    │
│ DS-002   │ Hoja técnica B │ 15/04/26 │ 👁️ 📂    │
└──────────┴────────────────┴──────────┴───────────┘
```

---

## 🔷 Acciones Disponibles por Fila

Cada registro (fila) en cualquier pestaña tiene estas acciones:

| Icono | Acción | Descripción |
|---|---|---|
| 👁️ **Ver** | Abre vista detalle del registro | Muestra metadatos, archivos asociados |
| ✏️ **Editar** | Abre formulario de edición | Modificar nombre, código, notas |
| 📂 **Ver en carpeta** | Abre carpeta en Windows Explorer | Usa `folder.php` vía Shell Server local (puerto 8970) |
| 📄 **Abrir archivo** | Abre el archivo en el equipo del usuario | Usa `preview.php` vía Shell Server local (puerto 8970) |
| 🗑️ **Archivar** | Soft delete | Registro se oculta pero no se borra físicamente |

---

## 🔷 Flujo Completo: Crear Documento Técnico

### Paso 1: Ir a la pestaña correspondiente
Ejemplo: pestaña **"Hojas de datos"** dentro de un equipo.

### Paso 2: Clic en **"+ Crear"**
Se abre un modal/formulario con campos:
- **Número/Código** (DS-001, DS-002... auto-incremental o manual)
- **Nombre** (título descriptivo)
- **Notas** (opcional)

### Paso 3: Guardar
Al guardar, el sistema:
1. Crea el registro en la tabla MySQL (`equipment_data_sheets`)
2. Crea la carpeta física: `Equipos/{NombreEquipo}/Especificaciones Tecnicas/Hoja De Datos/DS-001/`
3. Si se suben archivos, se almacenan dentro de esa carpeta

---

## 🔷 Flujo: Subir Archivos a un Documento

1. **Crear documento** (o abrir uno existente)
2. En la vista detalle, aparece la tabla **"Archivos"** (modelo `File`)
3. Clic en **"+ Subir archivo"**
4. Seleccionar archivo(s) desde el PC
5. El sistema:
   - Guarda el archivo físicamente en la carpeta del documento
   - Crea registro `File` en BD con: `path`, `mime_type`, `size`, `name`

**Vista de archivos dentro de un documento:**
```
┌──────────────────────────────────────────────────┐
│ Archivos                 [+ Subir archivo]       │
├──────────────────┬──────────┬──────────┬─────────┤
│ Nombre           │ Tipo     │ Tamaño   │ Acciones│
├──────────────────┼──────────┼──────────┼─────────┤
│ ds001_revA.pdf   │ PDF      │ 2.4 MB   │ 👁️ 📂  │
│ foto_equipo.jpg  │ Imagen   │ 1.1 MB   │ 👁️ 📂  │
└──────────────────┴──────────┴──────────┴─────────┘
```

---

## 🔷 Sincronización Carpeta ↔ BD

El comando `php artisan equipment:sync-folder` asegura que:

1. Cada equipo en BD tenga su carpeta base en disco
2. Las 5 carpetas principales existan (`Consultas de Campo`, `Especificaciones Tecnicas`, `General`, `Reportes`, `Repuestos`)
3. Las 6 sub-carpetas de `Especificaciones Tecnicas` existan
4. Las carpetas de documentos existentes en BD tengan su contraparte física

**El comando NO borra nada.** Solo crea carpetas faltantes.

---

## 🔷 Permisos y Roles

Cada pestaña/RelationManager verifica permisos antes de mostrar acciones:

| Permiso | Permite |
|---|---|
| `documents.create` | Botón "+ Crear" en pestaña Documentos |
| `documents.show_file` | Vista previa de archivos |
| `files.show_file` | Vista previa de archivos (alternativo) |
| `equipment.view` | Ver la vista detalle del equipo |

---

## 🔷 Acceso Rápido: Ver en Carpeta y Abrir Archivo

Las acciones 📂 **"Ver en carpeta"** y 📄 **"Abrir archivo"** ejecutan este flujo:

```
UI (Filament) → exec_url() → http://127.0.0.1:8970/folder.php?path=...
  → folder.php → explorer /select,... → Windows Explorer

UI (Filament) → exec_url() → http://127.0.0.1:8970/preview.php?path=...
  → preview.php → start "" "..." → El archivo se abre en el equipo local
```

Ambas acciones se ejecutan en el **PC local** del usuario (no en el servidor). Para funcionar en LAN, los scripts deben usar la ruta UNC `\\192.168.0.4\gestor-archivos\storage\app\private` y el cliente debe tener acceso SMB.

---

## 🔷 Resumen Visual de Pestañas

```
Vista Equipo: "Compresor Atlas"
├── 📋 Detalles (infolist)
├── 📑 Especificación técnica
│   ├── Hojas de datos     (DS-001, DS-002...)
│   ├── Planos             (Plano general...)
│   ├── Catálogos          (Catálogo multimedia...)
│   ├── Manuales           (Manual de operación...)
│   ├── Revisiones         (Revisión operativa...)
│   └── Normas             (Norma técnica...)
├── 📄 Documentos          (General/)
├── 🛒 Órdenes de compra   (PO-01-001...)
├── 🔧 Repuestos           (SP-001...)
├── 🔍 Consultas de campo  (Consulta de campo...)
└── 📊 Reportes            (Reporte de servicio...)
```

Cada pestaña es gestionada por su `RelationManager` correspondiente en `app/Filament/RelationManagers/`.