# Tablas

A continuación se listan cada una de las tablas de la base de datos, además de las columnas listadas por tabla, todas las tablas poseen los siguientes campos:

- **id**: ID único de 36 caracteres aleatorios
- **created_at**: Fecha de creación
- **updated_at**: Fecha de actualización
- **deleted_at**: Fecha de "eliminación" (en realidad no se eliminará, sino que se marcara como "Superado")

## Usuarios (users)

Almacena los usuarios que poseen una cuenta en el sistema, y pueden iniciar sesión (no confundir con Contactos).

- **name**: Nombre de usuario
- **email**: Correo eletrónico
- **password**: Contraseña de la cuenta

## Países (countries)

Almacena los países del mundo (data precargada).

- **name**: Nombre del país

## Estados (states)

Almacena los estados de Venezuela (data precargada).

- **name**: Nombre del estado

## Ciudades (cities)

Almacena las ciudades de Venezuela (data precargada).

- **name**: Nombre de la ciudad
- **state_id**: Estado al que pertenece la ciudad

## Clientes (customers)

Almacena las empresas que son clientes de proyectos de Maquindus

- **rif**: RIF del cliente
- **name**: Nombre de la empresa
- **email**: Correo electrónico
- **phone**: Teléfono de contacto
- **about**: Breve descripción de la empresa
- **address**: Dirección de la empresa
- **country_id**: País donde se encuentra.
- **state_id**: Estado en que se encuentra (vacío si no es Venezuela).
- **city_id**: Ciudad en que se encuentra (vacío si no es Venezuela).

## Proveedores (suppliers)

Almacena las empresas que son proveedores de equipos y repuestos para Maquindus

- **rif**: RIF del proveedor
- **name**: Nombre de la empresa
- **email**: Correo electrónico
- **phone**: Teléfono de contacto
- **about**: Breve descripción de la empresa
- **address**: Dirección de la empresa
- **country_id**: País donde se encuentra.
- **state_id**: Estado en que se encuentra (vacío si no es Venezuela).
- **city_id**: Ciudad en que se encuentra (vacío si no es Venezuela).

## Contactos (people)

Almacena las personas que están relacionadas con los clientes, proveedores o proyectos de Maquindus

- **name**: Nombre de la persona
- **surname**: Apellido de la persona
- **email**: Correo electrónico
- **phone**: Teléfono de contacto
- **position**: Cargo en la empresa (opcional)
- **address**: Dirección de la empresa
- **country_id**: País donde se encuentra.
- **state_id**: Estado en que se encuentra (vacío si no es Venezuela).
- **city_id**: Ciudad en que se encuentra (vacío si no es Venezuela).
- **personable**: Cliente o Proveedor al que pertenece el contacto (opcional)

## Equipos (equipment)

Almacena cada modelo de equipo con el cual trabaja Maquindus. Cabe destacar que almacena un **MODELO** de un equipo, no un **EJEMPLAR** de un equipo

- **name**: Nombre del equipo (único)
- **code**: Código del equipo (único), formato: EQ-ABC-2025-11-11-001
- **about**: Descripción corta del equipo
- **details**: Ficha técnica del equipo (número ilimitado de características con su nombre y valor)

## Repuesto (parts)

Almacena cada modelo de repuesto con el cual trabaja Maquindus. Cabe destacar que almacena un **MODELO** de un repuesto, no un **EJEMPLAR** de un repuesto

- **name**: Nombre del repuesto (único)
- **code**: Código del repuesto (único), formato: RE-ABC-2025-11-11-001
- **about**: Descripción corta del repuesto
- **details**: Ficha técnica del repuesto (número ilimitado de características con su nombre y valor)

## Proyectos (projects)

Almacena los proyectos realizados por Maquindus a clientes

- **name**: Nombre del proyecto
- **code**: Código del proyecto (único), formato:
PR-ABC-2025-11-11-001
- **about**: Descripción del proyecto (opcional)
- **start**: Fecha de inicio
- **end**: Fecha de finalización (opcional)
- **status**: Etapa del proyecto (Planificación, En curso, Finalizado)
- **customer_id**: Empresa cliente relacionada al proyecto

## Actividades (activities)

Almacena un historial de las actividades realizadas dentro de un proyecto

- **title**: Título de la actividad
- **comment**: Comentario acerca de la actividad
- **project_id**: Proyecto al que pertenece

## Documentos (documents)

Almacena los documentos relacionados a Proyectos, Equipos o Repuestos que se subieron al sistema

- **name**: Nombre del documento
- **type**: Tipo de documento (Planos, Manuales, Fichas Técnicas, Reportes). Es utilizado para generar la subcarpeta en que se guarda el documento.
- **documentable**: Equipo, Repuesto o Proyecto al que pertenece el documento

## Versiones (files)

Almacena las diferentes versiones subidas de un documento

- **path**: Enlace al directorio donde se almacenó la versión
- **mime**: Tipo de archivo de la versión (PDF, Word, Excel, PowerPoint, Imagen, SolidWork, AutoCAD)
- **version**: Número de versión (1, 2, 3, etc)
- **document_id**: Documento al que pertenece la versión

# Relaciones multiples

A continuación se muestran las relaciones múltiples planteadas entre las tablas ya descritas anteriormente.

- Una **Actividad** puede involucrar múltiples **Contactos**
- Un **Equipo** puede tener múltiples **Repuestos**
- Un **Equipo** puede ser utilizado en múltiples **Proyectos**
- Un **Equipo** puede provenir de múltiples **Proveedores**
- Un **Repuesto** puede funcionar para múltiples **Equipos**
- Un **Repuesto** puede ser utilizado en múltiples **Proyectos**
- Un **Repuesto** puede provenir de múltiples **Proveedores**
- Un **Contacto** puede participar en múltiples **Actividades**
- Un **Contacto** puede participar en múltiples **Proyectos**
- Un **Proyecto** puede abarcar múltiples **Equipos**
- Un **Proyecto** puede abarcar múltiples **Repuestos**
- Un **Proyecto** puede requerir múltiples **Contactos**
- Un **Proveedor** puede trabajar con múltiples **Equipos**
- Un **Proveedor** puede trabajar con múltiples **Repuestos**
