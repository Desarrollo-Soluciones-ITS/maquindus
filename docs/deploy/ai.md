# AI Studio

--- User ---
Al momento de desplegar a producción en un servidor dentro de una intranet una aplicación con Laravel 12 y Filament 4.1, cuales son los pasos a seguir?

------------------------------

--- AI Response ---
Desplegar una aplicación moderna con **Laravel 12** y **Filament 4.1** en una intranet (especialmente si el servidor tiene acceso limitado o nulo a internet) requiere un enfoque diferente al despliegue en la nube.

Aquí tienes la guía paso a paso enfocada en un entorno "offline" o restringido, asumiendo que ya estamos a finales de 2025 (tu fecha actual).

### 1. Preparación del Entorno (Servidor Intranet)
Antes de mover el código, asegúrate de que el servidor cumpla con los requisitos estrictos de estas versiones.

*   **PHP:** Versión **8.2** o superior (Idealmente 8.3/8.4 para Laravel 12).
*   **Extensiones PHP:** Asegúrate de tener activadas: `intl`, `mbstring`, `bcmath`, `curl`, `zip`, `xml`, `gd`.
    *   *Nota:* Filament requiere `intl` obligatoriamente para formateo de números y fechas.
*   **Base de Datos:** MySQL 8.0+, MariaDB 10.6+ o PostgreSQL.
*   **Web Server:** Nginx o Apache configurado para apuntar a la carpeta `/public`.

### 2. Preparación de los Artefactos (En tu máquina local)
Dado que es una intranet, es probable que no puedas ejecutar `composer install` o `npm install` en el servidor final. Debes "empaquetar" todo desde tu máquina de desarrollo o un servidor de CI/CD intermedio.

#### A. Dependencias de Backend (Composer)
Ejecuta esto en tu máquina local para descargar todas las librerías necesarias para producción:
```bash
composer install --optimize-autoloader --no-dev
```
*Esto genera la carpeta `vendor` completa lista para producción.*

#### B. Dependencias de Frontend (Vite/Filament)
Filament 4 utiliza Vite.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHX9Q-9oqnP9TM2913RQbtDtAe1OlzBvjukRijrC0jyJDy5_phs8JYCSorUZhGDUlroArWtmNNOEU0K1YIJ46sOwdha_oNcHFYDVGTztP3xDZvaWRC9W66iNM7IVW-rR9f76vhFbYNd2C5r6y4FT3YRHCp029CAslg65MxMFOr7PGKwJVau7oU9h5mBNOSnq62vyuNvcF3ZzwE%3D)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQE7otw-B1mYeZ6JYM6tQVE9SBVRyRyiYUlggbajnH7CksO2UydstRMT3M_3TMHOVuRBQPgpNoktpb7f2ZVSDQcMlOSlqqpEyVM8uS-CZ2hM0BRaVGI5fdX3wbLenAcEsusSm9A%3D)][[3](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGNJn9Ls3DXdujHddDR42dTkASWHflev8SBIMynIV90VGQuy2RA1UfyJ-PXrziiRRGJBAFUGtCOBLb_NU5DVs01vw-zCdIQbBoupNBAXxbi5K5hLlx9EtlfVqYOqeAhTMNDhzaEruzhWvTYKw5VbUXiwefz_Q7JCgEvkBKSDxit-l9UGNO4Uyr5SOzEcLHmia4ahpu9YgKylFy2A0RsefHh)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGLJovO9jbWmnfbJJQW1AeGPjThjlZfidEVvS9oCyKU49lI190CZ2CBernfTWSa1sHVL3pmvDS6Ofa-tIlbFmyymsZqggb99-7znI4Xj-pDuegFrBzkYOCUk9-GBvJCNZuy4MpvsN8rR18d742LElhJ6crxKtlum9IZYCE54iFxGirXYa_oG9a5RJwh9aPZYXMVBWMMeOFx9n7VGyh8)][[5](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGru-uXL2GGJllDaCPMYNktAjHSe2wKDqvtkZfWF-SUzTVn19e5LCjcSwWpiCGc3am1hdkGR8-5hCJi26jHLqKQymBx_Vyw7ROvbbWp3IX1ijm7HIt2Q74YdXO03sUYJbSOhPYvPjo-jrSwpCZjy9CSYEF4vK6aXFawREdkc5XCjfBW32GcRUV3e5B01nG9qVt-0XUGly9OycROEg-Iqg%3D%3D)][[6](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHxTYpi8SRH8gfNVYrqCUzSVrfEE0ZFpSgCQEXP5bQjMKVDWkNxWBrTJCIQ9_oIp2OsH3gBsgV7yCbimnsOSTMO7ACllthrVZtCAXNOnasSqOgih_4h7lIgPvHyd3-JAZdegg6dhZ_JitYHH2uUdJVAPY30Qw%3D%3D)][[7](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQECPIbs1ZT6IydvUrgUFf4YYrnxEdu2uKYUFXYpP4Trzf4HM01WE9jy9I69jHqlrco-K5x7jYdwPmhXd8phUSfZA0tOTcq_tgR51jQCrrh_g_KKsirk2MlzZb6coPTz9l48HLFMktxTJ2HFnRvqx43VRvg0m5hubYEJDFCQc7yXACUevnQUUJq5-95BOmFerx1TCthRwBbf1pUwGkC9XTxk5cr924A%3D)][[8](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHQMtp0g_JNPJPtgUvBgv-W7TnRGUxrybDLlKWGWHR7CmXRQoAMKRtJkotHJPJfOnPxEMitilKvLtcF6vRKoovlc0NjgpP8WHLwoxpG89rLBLdy8Euej5KJYBC0Bv0yYKE_eS_sp_02JYGeUepx)][[9](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQFSdo5xqbOIxIJ1-21inR67lCs0frhmdcKBQZ25enwPauG9KCgSPFdxesMlzkTMBx3WxcFjPHF-Vf_6HjG8HMj_KDuHLgBRj5HTVbf2xVCKFtenDKFdHTAnPQuPudm77lGTOAwcwQZGBRuwtO6O2Q%3D%3D)][[10](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGqpeSL-TR-j1URzk7Sz_tld9_btOn1bAhTysxLsKziyqPOxWs_kmZw2N9jUWAQIr7GLN0reQKJ5c8YaU8_wvBSDjWKr8xStlp3yZy49n8ZuPtr4iK3VC3IXeIeDHO89dKbhe8jJMoe7LjlX5uhEJeCJ4WUBf4cI-egTdyG-bYVGAK3lb3xIOaGcBMUMNPfyr_JVcCJ9DruuxEkyMOcEKjJqIVHHp5MZFTqWBiNXEZ7zz_ZEbaqirBn)][[11](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHJ0AoAiURMvC0o-Svr9BkFmOaCtNkjYqqrWToh-cmFXcQ5W1Cq4Q7nLDAeTMw98cVb_vYerqw07WOXvXPsEK67CMcDKfI03ezX5OqXS5qzWKC9xC3v6CsDWXV_tu9wnVPOsQ%3D%3D)][[12](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHcxtanx7yKyqukoLK_4SdfYHozeIZ34ZALw0ohTOBLbW2WrOV3HVCZSRSbKI4YPOV0Yh4pWlihN0ki4IrIZKkSoH7WTzdqB6rU4ppVms9TVpuXO7YMoPYNckqhMV1pWj79of-PwYGFcBHcWRoOyoyv62r361VILsClq1gcYg%3D%3D)] Debes compilar los assets (CSS/JS) localmente para no necesitar Node.js en el servidor de producción.
```bash
npm install
npm run build
```
*Esto genera los archivos compilados en la carpeta `public/build`.*

#### C. Limpieza y Empaquetado
Antes de comprimir, elimina archivos innecesarios para aligerar el peso:
*   Borra `node_modules` (ya no se necesita si ejecutaste `npm run build`).
*   Borra carpetas de caché locales como `storage/framework/cache/*` (pero deja la estructura de carpetas).

Comprime todo el proyecto (incluyendo `vendor` y `public/build`) en un archivo `.zip` o `.tar.gz`.

### 3. Despliegue en el Servidor
Transfiere el archivo comprimido al servidor intranet (vía SCP, SFTP, USB, etc.) y descomprímelo en la ruta final (ej. `/var/www/mi-app`).

#### A. Permisos
Es crítico dar permisos de escritura al servidor web (usuario `www-data` o `apache`) sobre estas carpetas:
```bash
chown -R www-data:www-data /var/www/mi-app
chmod -R 775 storage bootstrap/cache
```

#### B. Configuración del Entorno (.env)[[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGLJovO9jbWmnfbJJQW1AeGPjThjlZfidEVvS9oCyKU49lI190CZ2CBernfTWSa1sHVL3pmvDS6Ofa-tIlbFmyymsZqggb99-7znI4Xj-pDuegFrBzkYOCUk9-GBvJCNZuy4MpvsN8rR18d742LElhJ6crxKtlum9IZYCE54iFxGirXYa_oG9a5RJwh9aPZYXMVBWMMeOFx9n7VGyh8)][[10](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQGqpeSL-TR-j1URzk7Sz_tld9_btOn1bAhTysxLsKziyqPOxWs_kmZw2N9jUWAQIr7GLN0reQKJ5c8YaU8_wvBSDjWKr8xStlp3yZy49n8ZuPtr4iK3VC3IXeIeDHO89dKbhe8jJMoe7LjlX5uhEJeCJ4WUBf4cI-egTdyG-bYVGAK3lb3xIOaGcBMUMNPfyr_JVcCJ9DruuxEkyMOcEKjJqIVHHp5MZFTqWBiNXEZ7zz_ZEbaqirBn)]
1.  Copia el archivo de ejemplo: `cp .env.example .env`
2.  Edita el `.env`:
    *   `APP_ENV=production`
    *   `APP_DEBUG=false`
    *   `APP_URL=http://tu-servidor-intranet.local` (Crucial para que Filament cargue los assets correctamente).
    *   Configura la conexión a la base de datos (`DB_HOST`, `DB_PASSWORD`, etc.).

### 4. Pasos Finales (Comandos Artisan)
Aunque no tengas internet, necesitas ejecutar ciertos comandos de Laravel.

1.  **Generar clave de aplicación:**
    ```bash
    php artisan key:generate
    ```
2.  **Caché de configuración (Vital para rendimiento en Intranet):**
    Laravel 12 es muy rápido, pero necesita esto en producción.
    ```bash
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
    ```
3.  **Migraciones de Base de Datos:**
    Si el servidor tiene conexión a la BD:
    ```bash
    php artisan migrate --force
    ```
    *Si por políticas de seguridad no puedes ejecutar migraciones desde el código, deberás generar el SQL en tu local (`php artisan migrate --pretend`) y dárselo al DBA.*

4.  **Optimización de Filament:**
    Filament 4 incluye comandos para cachear sus propios componentes e iconos.
    ```bash
    php artisan filament:optimize
    ```
    *(Si este comando da error por falta de conexión para descargar iconos, omítelo; Filament funcionará igual pero un poco más lento en la primera carga).*

5.  **Crear Usuario Administrador:**
    Como es la primera vez, necesitarás entrar al panel:
    ```bash
    php artisan make:filament-user
    ```

### Resumen de Problemas Comunes en Intranet
*   **Assets no cargan (Pantalla blanca o sin estilos):** Revisa que el `APP_URL` en el `.env` coincida exactamente con lo que escribes en el navegador. Si usas IP, pon la IP.
*   **Iconos faltantes:** Filament a veces intenta descargar iconos de Blade UI Kit. Asegúrate de haber ejecutado `npm run build` correctamente en local, ya que eso empaqueta la mayoría de los recursos necesarios.
*   **Livewire falla:** Filament 4 usa Livewire 3/4. Si hay proxies corporativos que bloquean WebSockets o ciertas cabeceras, podrías necesitar configurar `LIVEWIRE_ASSET_URL` o ajustar los headers de tu Nginx.

------------------------------

--- User ---
The server where I'm going to deploy is Windows Server

------------------------------

--- AI Response ---

En Windows Server, el estándar profesional es usar **IIS (Internet Information Services)**. Es el servidor web nativo de Windows, mucho más rápido, seguro y estable que Apache sobre Windows.

Aquí tienes la guía definitiva para desplegar tu proyecto (Laravel 12 + Filament 4) en Windows Server correctamente:

### 1. Preparar el Servidor (Instalaciones Base)
En lugar de un "paquete todo en uno" como XAMPP, debes instalar los servicios por separado:

1.  **Activar IIS:**
    *   Ve a *Server Manager* > *Add Roles and Features*.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]
    *   Selecciona **Web Server (IIS)**.[[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)][[3](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQEuphfSVA-PoY9m36uDLuv7VdZ7-ewlDdTsYtyba_vKnosOgcCSFEZNJqJ9uw6suTRmN1TNb_kQzhT1ouJB_Mah2_crRZwyE0qpBZ-F9tKjC1GRD8mS8tjq8LwMtkOjBcWyx4ia50KvSxROarLrji0sp8DUAAG5VG6XEGD0kMH8YokGpKNx79Qcm33x73zqpRQxMx_XMaeE1lFTEXZ32sI4lXR76LcEkCE7pfzJshePGqdTnMId5Nk_HeJhJFX39gClpOhJyNvU)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHfRM6yCOrpqk6Dfmb7C5naWP6YdOfhHzOKyMVDelD38RS6H5je3ed1GGoD5RM7cHsA5C-9xBv-0V0i2vJfFdUHEDrSAjFJ46mHe6ki8PEXZusyAhUdmPwmys14763lBAD9aMiJFhg%3D)]
    *   Importante: En "Role Services", asegúrate de marcar **CGI** (necesario para ejecutar PHP) y **ISAPI Extensions**.[[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]

2.  **Instalar PHP (Manualmente):**
    *   Descarga la versión **Non-Thread Safe (NTS)** de PHP 8.2/8.3 para Windows desde `windows.php.net`. (NTS es vital para el rendimiento en IIS).
    *   Descomprímelo en `C:\PHP`.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]
    *   Renombra `php.ini-production` a `php.ini`.
    *   Edita `php.ini` y activa las extensiones requeridas por Filament (`intl`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `zip`).

3.  **Instalar "URL Rewrite Module":**
    *   IIS no entiende el archivo `.htaccess` de Laravel por defecto.
    *   Descarga e instala el **Microsoft URL Rewrite Module 2.0** (oficial de Microsoft). Esto permitirá que IIS lea el archivo `web.config` que Laravel trae incluido.

4.  **Base de Datos:**
    *   Instala MySQL o MariaDB Community Server como un **Servicio de Windows** independiente.

### 2. Configurar el Sitio en IIS[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHfRM6yCOrpqk6Dfmb7C5naWP6YdOfhHzOKyMVDelD38RS6H5je3ed1GGoD5RM7cHsA5C-9xBv-0V0i2vJfFdUHEDrSAjFJ46mHe6ki8PEXZusyAhUdmPwmys14763lBAD9aMiJFhg%3D)][[5](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHyatJTZTfyPiuD-NQDr0GahTSedkpc8KpVTwfxBcPHd3BOZgoErrIoDIyNWireW1apWYUKUfaUDB2li3bde9VNb66-2JIgPSNlg2jf6W5neRjPJfpaN8cvmViHmtDqtUHiuECYiJoXvaPTd4n83OHW3Qae2g90qA%3D%3D)]
1.  Abre el **IIS Manager**.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHfRM6yCOrpqk6Dfmb7C5naWP6YdOfhHzOKyMVDelD38RS6H5je3ed1GGoD5RM7cHsA5C-9xBv-0V0i2vJfFdUHEDrSAjFJ46mHe6ki8PEXZusyAhUdmPwmys14763lBAD9aMiJFhg%3D)]
2.  Haz clic derecho en "Sites" > **Add Website**.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]
3.  **Site name:** TuProyecto.
4.  **Physical path:** Apunta a la carpeta **`public`** de tu proyecto (ej: `C:\inetpub\wwwroot\tu-proyecto\public`).
    *   *Nota: Nunca apuntes a la raíz del proyecto, siempre a `public` por seguridad.*
5.  **Configurar PHP en IIS:**
    *   En IIS Manager, selecciona tu servidor (la raíz del árbol a la izquierda).
    *   Doble clic en **Handler Mappings**.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHfRM6yCOrpqk6Dfmb7C5naWP6YdOfhHzOKyMVDelD38RS6H5je3ed1GGoD5RM7cHsA5C-9xBv-0V0i2vJfFdUHEDrSAjFJ46mHe6ki8PEXZusyAhUdmPwmys14763lBAD9aMiJFhg%3D)]
    *   Clic derecho > **Add Module Mapping**.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]
    *   *Request path:* `*.php`
    *   *Module:* `FastCgiModule`
    *   *Executable:* Selecciona tu `C:\PHP\php-cgi.exe`.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)]
    *   *Name:* PHP via FastCGI.[[1](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQG0_Ifn0_1Fm4yzVxiZQJUwjM1FUrIf0pgtdbnZWfyByohF4MmaZx-mDU5-fGWJlBQwk-KHylGjrYu9hr37oj4C0plQAJJ3qmY25uksAy6D4UyxO5JX944_bVi-VDLZEvFGH0FJ0VkZemkTMRgbpLMEbV9R3misZcYGxm2spk-dP7DYFPmKOeMJ)][[2](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQH51vyWTj2AGPln7Y2ZlWVfpBQRReKtJw5PVOPFg7PkNsr4AcfnReMIZgCz2GpTvnVMswgOBMHkE2hGhzcsOOBpOZI5K7RvIgHT5jiE1MkTqboizIQ8L4jIkan7G8jEW5WPL9WoFiexeyz6GpUePN7-gpOinmZTDMJptijF5fPB)][[4](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQHfRM6yCOrpqk6Dfmb7C5naWP6YdOfhHzOKyMVDelD38RS6H5je3ed1GGoD5RM7cHsA5C-9xBv-0V0i2vJfFdUHEDrSAjFJ46mHe6ki8PEXZusyAhUdmPwmys14763lBAD9aMiJFhg%3D)][[6](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQExqxFhtywDF-SQOX_8b2Eb6iiIPTbiZlv1sIh5aWRfw547iVcJ3eTx6lhIBsQ_1E9xJQJFmaBVD-GN951jkLBFTUJv67YnrfxTAXDBBGwiezNTP-3fSjSCb1sCfulDytlxpZY2TLYk1PZJpPh24V6kE75Qs4Pl8qj14zXtYDKRNau2bVDMwFvK3Mam0I2UyJNyFKatSPiQ_FRq37PX)]

### 3. Permisos de Carpetas (El error más común)
En Linux usas `chmod 775`, pero en Windows funcionan diferente.
Laravel necesita escribir en `storage` y `bootstrap/cache`.

1.  Ve a la carpeta de tu proyecto en el Explorador de Archivos.
2.  Click derecho en la carpeta `storage` > Propiedades > Seguridad.
3.  Dale permisos de **Modificar (Modify)** y **Escribir (Write)** a estos dos usuarios:
    *   **`IUSR`** (El usuario anónimo de internet).
    *   **`IIS_IUSRS`** (El grupo de procesos de IIS).
4.  Repite lo mismo para la carpeta `bootstrap/cache`.

### 4. Colas y Tareas Programadas (Workers)
En Linux usarías Supervisor. En Windows Server, la herramienta profesional equivalente es **NSSM** (Non-Sucking Service Manager).

1.  Descarga NSSM.[[7](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQEzuVdUj8Xujzx5iCHY81zxK6LRaVE1bm1jl09mRXO8TVG77YJieKrQKVGP1QMM8vZrDQo3sja4WLUKx3wkG0vKgAVb_KKIHn26e7jVIg_-R8smGxPNL2V0_Oagu3obpOto7-vV-U737K4mLl16Vw42hqykxiAakWmJ72dOzMS8hI_W5NgCfg%3D%3D)][[8](https://www.google.com/url?sa=E&q=https%3A%2F%2Fvertexaisearch.cloud.google.com%2Fgrounding-api-redirect%2FAUZIYQEJntI2-AXjEQohbRiSxY_a1Gnd70LGZGXi-kIrDJscipMlpwrCM8q3RcYwvfY_ppEQfawPy7xVrGbyjISzGH-wknkSMREZkk7E2EYlBuagrogfPp6z6Iaxc00001G_xa3SWJouhYQKf8VDVwmOJg199gIKShI-QvNkoxzIw546GdGj9Z900-ISyQhUOKZeAPavlQ2GzbJYU7yk70uUPMI%3D)]
2.  Crea un servicio para tus colas (emails, importaciones de Filament):
    ```powershell
    nssm install LaravelQueue "C:\PHP\php.exe" "C:\inetpub\wwwroot\tu-proyecto\artisan queue:work --tries=3"
    ```
3.  Esto mantendrá tu worker corriendo en segundo plano y lo reiniciará si falla.

### Resumen
*   **Desarrollo:** XAMPP o Laragon.
*   **Producción Windows:** IIS + PHP (NTS) + MySQL Service + NSSM.

Si el servidor te permite instalar **Docker Desktop** (o Rancher Desktop), esa sería la única alternativa moderna válida a IIS, pero IIS suele ser más ligero en recursos para Windows Server.


------------------------------

--- User ---

Que configuraciones de php.ini deben hacerse al momento de desplegar esta aplicación de Laravel 12 y Filament 4.1?

------------------------------

--- AI Response ---

Al desplegar **Laravel 12 con Filament 4.1** en Windows Server (IIS), el archivo `php.ini` es crítico para evitar errores de "memoria agotada", fallos en la subida de archivos o problemas de rendimiento.

Debes tomar como base el archivo **`php.ini-production`** (renómbralo a `php.ini`) y modificar las siguientes secciones.

Aquí tienes la "Checklist" definitiva para este stack tecnológico:

### 1. Extensiones Obligatorias (Uncomment)
En Windows, las extensiones están comentadas con un `;` al inicio. Debes quitar ese punto y coma.
Filament y Laravel 12 requieren estrictamente estas:

```ini
; Asegúrate de que el directorio de extensiones apunte a la carpeta correcta
extension_dir = "ext"

; Extensiones Vitales
extension=curl
extension=fileinfo
extension=gd        ; Necesaria para manipular imágenes (Avatares, subidas en Filament)
extension=intl      ; ¡CRÍTICA! Filament se rompe sin esta (formateo de fechas/monedas)
extension=mbstring
extension=exif      ; Recomendada para manejar metadatos de imágenes
extension=openssl
extension=pdo_mysql ; O pdo_pgsql si usas PostgreSQL
extension=zip       ; Necesaria para updates y exportaciones
```

### 2. Límites de Recursos (Tuning para Filament)
Los paneles administrativos como Filament consumen más memoria que una web pública simple, especialmente al renderizar tablas grandes, generar reportes o manipular imágenes.

```ini
; Memoria: 128M (default) es muy poco para Filament. Súbelo a 512M.
memory_limit = 512M

; Tiempo de ejecución: Evita timeouts en importaciones/exportaciones largas.
max_execution_time = 120
max_input_time = 120
```

### 3. Carga de Archivos (Uploads)
Filament facilita la subida de archivos. La configuración por defecto de PHP (2MB) es insuficiente.

```ini
; Define el tamaño máximo de un archivo individual (ej. 64MB)
upload_max_filesize = 64M

; Define el tamaño total del envío (debe ser mayor que upload_max_filesize)
; Esto incluye todos los archivos subidos + los datos del formulario.
post_max_size = 80M

; Cantidad máxima de archivos en una sola petición (útil para subidas múltiples)
max_file_uploads = 20
```

### 4. Rendimiento (OPcache - ¡Obligatorio!)
En producción, **OPcache** hace que PHP no tenga que leer y compilar los scripts en cada petición. Esto multiplica la velocidad de Laravel.

```ini
[opcache]
; Habilita OPcache
opcache.enable=1

; Memoria asignada para el caché de código compilado (128M o 256M está bien)
opcache.memory_consumption=256

; Cantidad de scripts a cachear (Laravel tiene muchos archivos)
opcache.max_accelerated_files=20000

; CRÍTICO PARA PRODUCCIÓN:
; Si está en 0, PHP no revisará si el archivo cambió. Máxima velocidad.
; Desventaja: Debes reiniciar el servicio PHP/IIS cada vez que despliegues cambios.
opcache.validate_timestamps=0

; Opcional: JIT Compiler (Laravel 12 le saca provecho, default en PHP 8.x es 'tracing' o deshabilitado según versión)
opcache.jit_buffer_size=100M
opcache.jit=tracing
```

### 5. Seguridad y Zona Horaria

```ini
; Oculta la versión de PHP en las cabeceras HTTP (Seguridad por oscuridad)
expose_php = Off

; Desactiva mostrar errores en pantalla (los usuarios no deben ver stack traces)
display_errors = Off
display_startup_errors = Off
log_errors = On
; Define dónde se guardan los logs de PHP (útil si IIS falla)
error_log = "C:\Windows\temp\php-errors.log"

; Define tu zona horaria para evitar desfases en los logs de PHP
date.timezone = "America/Caracas" 
```

### 6. Configuración Específica de IIS (FastCGI)
Para que IIS se comunique correctamente con PHP:

```ini
; Mantiene el path info correcto para el ruteo de Laravel
cgi.fix_pathinfo=1

; Desactiva el GC de PHP para dejar que IIS maneje los procesos (mejora estabilidad)
fastcgi.impersonate = 1
```

### Resumen de Pasos post-edición
Una vez guardes el archivo `php.ini`:
1.  **Reinicia IIS:** Abre PowerShell como admin y ejecuta `iisreset`.
2.  **Verifica:** Crea un archivo `info.php` con `<?php phpinfo(); ?>` en tu carpeta pública momentáneamente para verificar que `intl` está activo y los límites de memoria subieron. (Bórralo después).

------------------------------

--- User ---

Alguna configuración a hacer de parte del lado de Laravel?

------------------------------

--- AI Response ---

Sí, hay varias configuraciones críticas **dentro del código y archivos de Laravel** que debes ajustar para que funcione correctamente en un entorno Windows Server (IIS) y, especialmente, para una Intranet (sin conexión a internet).

Aquí tienes la lista de ajustes obligatorios y recomendados:

### 1. El archivo `.env` (Producción)
En tu servidor de producción, edita el archivo `.env`. Estos valores son los que causan el 90% de los errores visuales en Filament si están mal puestos.

```ini
APP_ENV=production
APP_DEBUG=false
# IMPORTANTE: En IIS, esto debe coincidir exactamente con la URL del navegador.
# Si entran por IP, pon la IP. Si hay un dominio interno, pon el dominio.
# Si esto está mal, Filament no cargará los estilos (CSS/JS).
APP_URL=http://tu-servidor-interno.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
...

# Configuración de Sesión (Importante para IIS)
# Si usas 'file', asegúrate que storage/framework/sessions tenga permisos de escritura.
# Recomendado para producción: usar 'database' o 'redis' si es posible.
SESSION_DRIVER=file 
```

### 2. Configuración "Offline" de Filament (Intranet)
Por defecto, Filament intenta buscar avatares en Gravatar (internet) y fuentes en Google Fonts. En una intranet sin internet, esto hará que la carga sea lenta esperando a que fallen las peticiones.

**Desactivar Gravatar (Avatares):**
Ve a tu `app/Providers/Filament/AdminPanelProvider.php` y añade `defaultAvatarProvider`:

```php
use Filament\AvatarProviders\UiAvatarsProvider; // O usa uno local

public function panel(Panel $panel): Panel
{
    return $panel
        // ... otras configs
        ->defaultAvatarProvider(UiAvatarsProvider::class) // Genera avatares con iniciales (no requiere imagen externa)
        // O simplemente desactívalo si prefieres subir fotos manuales
        ->font('Inter') // Asegúrate de que la fuente esté importada localmente en tu CSS, no desde Google Fonts
        ->favicon(asset('images/favicon.png'));
}
```
*Nota:* Si usas una fuente personalizada, asegúrate de haberla incluido en tu `resources/css/app.css` y compilado con Vite, para no depender de CDNs externos.

### 3. El enlace simbólico en Windows (`storage:link`)
Laravel necesita que la carpeta `public/storage` apunte a `storage/app/public`.
En Windows, crear enlaces simbólicos (symlinks) requiere permisos especiales.

1.  Abre **PowerShell** o CMD **como Administrador**.
2.  Ve a la ruta de tu proyecto.
3.  Ejecuta:
    ```bash
    php artisan storage:link
    ```
*Si no eres administrador, el comando fallará.* Sin esto, las imágenes subidas en Filament no se verán.

### 4. Caché de Configuración (Vital para Windows)
Windows maneja las rutas de archivos (`\`) diferente a Linux (`/`). Laravel calcula esto dinámicamente, lo cual es lento. Al cachear la configuración, Laravel convierte todas las rutas a texto estático, acelerando drásticamente la aplicación.

Ejecuta estos comandos en cada despliegue:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Configuración de Livewire (Filament Core)
Filament corre sobre Livewire. En IIS, a veces Livewire no detecta bien dónde están los scripts si la app no está en la raíz del dominio.

Si tu app está en una subcarpeta (ej. `http://servidor/mi-app/`), necesitas ajustar el archivo `config/livewire.php`.
Si no existe, publícalo:
```bash
php artisan vendor:publish --tag=livewire:config
```

Y edita:
```php
'asset_url' => env('APP_URL', 'http://localhost'), // Fuerza a Livewire a saber su URL base
```

### 6. Permisos de Directorios (Checklist)
Asegúrate de que estas carpetas dentro de tu proyecto tengan permisos de **escritura** para el usuario `IUSR`:
*   `storage/` (y todas sus subcarpetas)
*   `bootstrap/cache/`

### 7. Configuración Regional (Intranet)
Generalmente las intranets son para una zona específica. Configura esto en `config/app.php` para que Filament muestre fechas y horas correctamente:

```php
'timezone' => 'America/Caracas', // O tu zona horaria
'locale' => 'es', // Español
'fallback_locale' => 'en',
```
*(Recuerda instalar el paquete de idioma de Laravel si quieres los mensajes de validación en español: `lang:publish` y descargar los archivos json).*

### Resumen del flujo de despliegue en Laravel
Cada vez que actualices el código en el servidor Windows:
1.  Copiar archivos nuevos.
2.  `composer install --no-dev --optimize-autoloader`
3.  `php artisan migrate --force`
4.  **`php artisan optimize`** (Este comando ejecuta todos los `cache` mencionados arriba en Laravel 11/12).
5.  `php artisan filament:optimize` (Cachea iconos y componentes de Filament).
6.  `php artisan config:cache` (Reiniciar caché de config por si acaso).
7.  Reiniciar los workers de colas (si usas NSSM): `nssm restart LaravelQueue`.
