# Base de conocimiento del proyecto

- Estructura de códigos para Equipos, Repuestos y Proyectos
  * EQ-XXX-2025-10-10-001
  * RE-XXX-2025-10-10-001
  * PR-XXX-2025-10-10-001

- El acceso a los documentos debe hacerse de tres formas:
  * Previsualizacion sin descargar el documento
  * Mostrar en carpeta, abriendo el explorador de Windows
  * Descargar una copia del documento

- Se debe tener un historial de versiones de los documentos (planos, manuales, etc).
- Estándar de carpetas y nombres con código para archivos (ej. MAN-Nombre = Manual).
- Categorías de manuales: de operacion, de mantenimiento.
- Categorías de planos: conjunto, detalle, para construir, ya construido.

## Preguntas y respuestas

- Los contactos pueden pertenecer a un cliente, a un proveedor, a un proyecto. ¿Pueden ser también las personas de Maquindus? R: Sí, el personal de Maquindus son contactos
- Historial de procura: ¿que exactamente se debe guardar para formar dicho historial? ¿Seguimiento de pedidos de equipos/repuestos? ¿Orientado a proyectos? ¿O visto desde el punto de vista de equipos/repuestos?

## Ideas

- Part y Equipment son basicamente iguales, la unica diferencia es a nivel conceptual, pero los campos y relaciones son exactamente los mismos
- Para que no se pierda el trabajo hecho hasta ahora en la organización de los documentos, se plantea hacer un script que lea el estado de los directorios y cargue automáticamente estos archivos a la DB, sin embargo esto podría ser complicado, así que queda como una posible idea
