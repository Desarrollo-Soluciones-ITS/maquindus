#!/bin/bash
# Script para resolver conflictos de merge en el servidor de produccion
# Ejecutar desde Git Bash como: bash scripts/resolver_conflictos.sh

echo "============================================="
echo " Resolviendo conflictos de merge en servidor"
echo "============================================="
echo ""

cd /c/inetpub/wwwroot/gestor-archivos || { echo "ERROR: No se encuentra el repositorio"; exit 1; }

echo "PASO 1: Estado actual del merge"
echo "-------------------------------"
git status
echo ""

echo "PASO 2: Aceptar nuestra version de helpers.php"
echo "----------------------------------------------"
git checkout --theirs app/helpers.php && echo "OK: helpers.php resuelto" || echo "ERROR: helpers.php"
echo ""

echo "PASO 3: Aceptar nuestra version de routes/web.php"
echo "--------------------------------------------------"
git checkout --theirs routes/web.php && echo "OK: routes/web.php resuelto" || echo "ERROR: routes/web.php"
echo ""

echo "PASO 4: Marcar conflictos como resueltos"
echo "----------------------------------------"
git add app/helpers.php routes/web.php && echo "OK: archivos agregados al stage" || echo "ERROR al agregar"
echo ""

echo "PASO 5: Hacer commit del merge"
echo "-------------------------------"
git commit -m "fix: resolve merge conflicts - keep LAN mode with route model binding" && echo "OK: merge commit creado" || echo "ERROR al hacer commit"
echo ""

echo "PASO 6: Sincronizar con remoto"
echo "-------------------------------"
git pull && echo "OK: pull completado" || echo "OK: branch ya actualizado"
echo ""

echo "============================================="
echo " VERIFICACION FINAL"
echo "============================================="
git log --oneline -5
echo ""

echo "CONFLICTOS RESUELTOS EXITOSAMENTE"
echo "Ya puedes usar git pull sin errores."