# 📂 Estructura de `storage/app/private/Equipos`

## 🔷 Nivel 1: Equipos (carpetas raíz)

Cada carpeta en `Equipos/` corresponde a un registro en la tabla `equipment`. El nombre de la carpeta = `name` del equipo.

```
storage/app/private/Equipos/
├── Alimentador AF 1807 72 in/
├── Alimentador AF15085 60 in/
├── Alimentador Boliden AF8-96 in/
├── Compresor Atlas/
├── Generador Perkins/
└── Volteador de Vagones/
```

---

## 🔷 Nivel 2: Categorías de Documentos (dentro de cada equipo)

Cada equipo contiene EXACTAMENTE estas 5 carpetas (creadas por `SyncEquipmentFolder`):

```
Equipos/{NombreEquipo}/
├── Consultas de Campo/          ← EquipmentFieldQuery
├── Especificaciones Tecnicas/   ← Contiene sub-carpetas técnicas
├── General/                     ← Documentos generales (solo algunos equipos)
├── Reportes/                    ← EquipmentReport
└── Repuestos/                   ← EquipmentSparePart
```

**Ejemplo real:**
```
Equipos/Compresor Atlas/
├── Consultas de Campo/
├── Especificaciones Tecnicas/
├── General/
├── Reportes/
└── Repuestos/
```

---

## 🔷 Nivel 3: Especificaciones Técnicas (sub-carpetas fijas)

`Especificaciones Tecnicas/` siempre contiene estas 6 sub-carpetas:

```
Especificaciones Tecnicas/
├── Catálogos/          ← EquipmentCatalog
├── Hoja De Datos/      ← EquipmentDataSheet
├── Manuales/           ← EquipmentManual
├── Normas/             ← EquipmentStandard
├── Planos/             ← EquipmentBlueprint
└── Revisiones/         ← EquipmentTechnicalSpecification
```

**Ejemplo real:**
```
Equipos/Generador Perkins/Especificaciones Tecnicas/
├── Catálogos/
│   └── Catálogo multimedia Generador Perkins/
├── Hoja De Datos/
│   ├── DS-001/
│   └── DS-002/
├── Manuales/
│   └── Manual de operación Generador Perkins/
├── Normas/
│   └── Norma técnica Generador Perkins/
├── Planos/
│   └── Plano general Generador Perkins/
└── Revisiones/
    └── Revisión operativa Generador Perkins/
```

---

## 🔷 Nivel 3: Consultas de Campo

```
Consultas de Campo/
└── Consulta de campo {NombreEquipo}/
    └── {archivos PDF, fotos, etc.}
```

**Ejemplo:**
```
Equipos/Compresor Atlas/Consultas de Campo/
└── Consulta de campo Compresor Atlas/
    └── fotos_campo.jpg, reporte.pdf
```

---

## 🔷 Nivel 3: Reportes

```
Reportes/
└── Reporte de servicio {NombreEquipo}/
    └── reporte.pdf, imagenes.png
```

**Ejemplo:**
```
Equipos/Generador Perkins/Reportes/
└── Reporte de servicio Generador Perkins/
    └── informe_mantenimiento.pdf
```

---

## 🔷 Nivel 3: Repuestos

```
Repuestos/
├── SP-001/    ← Código del repuesto (EquipmentSparePart)
├── SP-002/
└── 02020202/  ← Código numérico también válido
```

**Ejemplo:**
```
Equipos/Compresor Atlas/Repuestos/
└── SP-001/
    └── ficha_tecnica.pdf, foto_repuesto.jpg
```

---

## 🔷 Nivel 3: General (solo algunos equipos)

Carpeta opcional para documentos que no encajan en otras categorías:

```
General/
├── Bomba hidráulica/
├── Filtro principal/
├── Motores Industriales del Centro/
├── PO-01-001/       ← Órdenes de compra (SupplierPurchaseOrder)
├── PO-01-002/
├── PO-02-001/
├── PO-02-002/
├── PO-03-001/
├── PO-03-002/
└── Suministros Técnicos SA/
```

**Ejemplo real (Compresor Atlas):**
```
Equipos/Compresor Atlas/General/
├── Bomba hidráulica/
├── Filtro principal/
├── Motores Industriales del Centro/
├── PO-01-001/
├── PO-01-002/
├── PO-02-001/
├── PO-02-002/
├── PO-03-001/
├── PO-03-002/
└── Suministros Técnicos SA/
```

---

## 🔷 Nivel 4+: Documentos y Archivos

Dentro de cada carpeta hoja (Nivel 3 o 4), residen los archivos físicos (PDFs, imágenes, DWGs, etc.) y versiones de documentos.

```
Hoja De Datos/DS-001/
├── ds001_revA.pdf        ← Versión actual
├── ds001_revB.pdf        ← Versión anterior (histórico)
└── ...
```

---

## 🔷 Árbol Completo (Resumen Jerárquico)

```
Equipos/                              ← Tabla: equipment
└── {NombreEquipo}/
    ├── Consultas de Campo/           ← EquipmentFieldQuery
    │   └── {nombre_consulta}/
    │       └── archivos...
    │
    ├── Especificaciones Tecnicas/
    │   ├── Catálogos/                ← EquipmentCatalog
    │   │   └── {nombre_catalogo}/
    │   ├── Hoja De Datos/            ← EquipmentDataSheet
    │   │   └── DS-{numero}/
    │   ├── Manuales/                 ← EquipmentManual
    │   │   └── {nombre_manual}/
    │   ├── Normas/                   ← EquipmentStandard
    │   │   └── {nombre_norma}/
    │   ├── Planos/                   ← EquipmentBlueprint
    │   │   └── {nombre_plano}/
    │   └── Revisiones/               ← EquipmentTechnicalSpecification
    │       └── {nombre_revision}/
    │
    ├── General/                      ← Documentos generales / PurchaseOrders
    │   └── {nombre}/
    │
    ├── Reportes/                     ← EquipmentReport
    │   └── {nombre_reporte}/
    │
    └── Repuestos/                    ← EquipmentSparePart
        └── {codigo_repuesto}/
```

---

## 🔷 Relación con la Base de Datos

| Carpeta | Modelo Eloquent | Tabla MySQL |
|---|---|---|
| `Equipos/` | `Equipment` | `equipment` |
| `Consultas de Campo/` | `EquipmentFieldQuery` | `equipment_field_queries` |
| `Especificaciones Tecnicas/Catálogos/` | `EquipmentCatalog` | `equipment_catalogs` |
| `Especificaciones Tecnicas/Hoja De Datos/` | `EquipmentDataSheet` | `equipment_data_sheets` |
| `Especificaciones Tecnicas/Manuales/` | `EquipmentManual` | `equipment_manuals` |
| `Especificaciones Tecnicas/Normas/` | `EquipmentStandard` | `equipment_standards` |
| `Especificaciones Tecnicas/Planos/` | `EquipmentBlueprint` | `equipment_blueprints` |
| `Especificaciones Tecnicas/Revisiones/` | `EquipmentTechnicalSpecification` | `equipment_technical_specifications` |
| `Reportes/` | `EquipmentReport` | `equipment_reports` |
| `Repuestos/` | `EquipmentSparePart` | `equipment_spare_parts` |
| `General/` | `SupplierPurchaseOrder` (PO-*) y otros | `supplier_purchase_orders` |

---

## 🔷 Comando de Sincronización

```bash
php artisan equipment:sync-folder
```

Este comando (`SyncEquipmentFolder`) crea automáticamente las 5 carpetas base dentro de cada equipo si no existen:
1. `Consultas de Campo`
2. `Especificaciones Tecnicas` (con sus 6 sub-carpetas)
3. `General`
4. `Reportes`
5. `Repuestos`