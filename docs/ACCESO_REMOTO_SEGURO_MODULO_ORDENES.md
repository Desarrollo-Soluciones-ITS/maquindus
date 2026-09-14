# Documento Técnico: Acceso Remoto Seguro al Módulo de Órdenes de Cliente

> Proyecto: **Maquindus** — Laravel 12 + Filament 4 + MariaDB (servidor local Windows/IIS).
> Objetivo: exponer **únicamente** el nuevo módulo de Órdenes de Cliente a Internet,
> **solo para dispositivos autorizados**, desde cualquier ubicación, con el mayor nivel de seguridad.
> Estado: **PROPUESTA TÉCNICA — PENDIENTE DE APROBACIÓN Y DECISIONES**. No iniciar implementación sin "OK".

---

## 1. Objetivo y Alcance

**Objetivo.** Permitir que usuarios con **equipos puntuales** (PCs/laptops/tablets corporativas) accedan al módulo de Órdenes de Cliente **desde fuera de la red local**, de forma segura, sin exponer el sistema completo ni los servicios internos (archivos, base de datos, servidor auxiliar PHP).

**En alcance:**
- Diseño de arquitectura de acceso remoto (red + identidad + dispositivo + aplicación).
- Segmentación del módulo (aislarlo del resto del sistema).
- Endurecimiento (hardening) de Laravel/Filament y del servidor web.
- Controles de identificación de dispositivos, MFA, TLS, monitoreo.
- Estimación de trabajo (H/H) y requisitos.

**Fuera de alcance:**
- Rediseño de los módulos LAN existentes.
- Portal público abierto a clientes finales (ver preguntas).
- App móvil nativa.

---

## 2. Contexto Actual (AS-IS) — Hallazgos

Detectado en el repositorio y documentación del servidor local:

| Elemento | Estado actual | Implicación de seguridad |
|---|---|---|
| Panel Filament | **1 panel** (`dashboard`), `path('')`, login habilitado | Todo el sistema comparte el mismo panel y login |
| `User::canAccessPanel()` | **`return true`** (sin restricción) | Cualquier usuario válido entra al panel completo |
| MFA / 2FA | **No implementado** | Solo contraseña protege el acceso |
| Allowlist de dispositivos | **No existe** | No hay control por equipo autorizado |
| Sesión | `driver=database`, `SESSION_ENCRYPT=false`, `SESSION_DOMAIN=null` | Cookies de sesión no cifradas ni acotadas a dominio |
| Cookies seguras | No configuradas (`secure`, `http_only`, `same_site`) | Riesgo de robo de sesión en tránsito |
| HTTPS | `URL::forceScheme('https')` en producción; IIS en **puerto 81** | Falta cadena TLS/renovación gestionada y HSTS |
| nginx (contenedor Docker) | `listen 80` — **solo HTTP**, sin TLS | Sin cifrado en el proxy de referencia |
| Servicios internos | MySQL `3307`, PHP aux `8970`, SMB `445`, `\\192.168.0.4\private` | **Nunca** deben exponerse a Internet |
| Servidor auxiliar "ver en carpeta" | `SHELL_API_URL=http://localhost:8970` + `gestor://` | Solo tiene sentido en LAN; no debe cruzar el borde |
| Auth | Guard estándar `web`, roles/permisos propios (`Role`,`Permission`) | Base reutilizable para segmentar el módulo |

**Conclusión:** hoy el sistema es **estrictamente LAN**. Exponerlo requiere una capa nueva de acceso remoto + segmentación + hardening. Todo es **aditivo**.

---

## 3. Principios de Seguridad Aplicados

1. **Defensa en profundidad (Defense in Depth):** múltiples capas independientes; fallar una no compromete el todo.
2. **Modelo Zero Trust:** "nunca confiar, siempre verificar". Ninguna red es confiable por defecto; se verifica identidad **y** dispositivo en cada acceso.
3. **Mínima exposición:** **no abrir puertos** a Internet si es evitable (túnel saliente en lugar de NAT entrante).
4. **Mínimo privilegio:** el módulo remoto ve **solo** sus funciones y datos.
5. **Segmentación:** el módulo remoto queda aislado del resto (panel, archivos, BD directa).
6. **Trazabilidad:** todo acceso y cambio queda auditado.
7. **Cero regresión:** nada de lo actual se altera; la exposición es una capa nueva.

## 4. Opciones de Arquitectura de Acceso Remoto

Se evaluaron 5 alternativas. La recomendada es una **combinación en capas** (ver §5).

| # | Opción | Cómo funciona | Seguridad | Complejidad | Exponer puertos | Apta para "dispositivos puntuales" |
|---|---|---|---|---|---|---|
| **A** | **VPN sitio-a-cliente** (WireGuard / OpenVPN) | Solo los dispositivos con el perfil VPN entran a la red y ven la app | **Muy alta** | Media-Alta | Sí (1 puerto UDP/TCP) | **Sí** (identidad por par de claves) |
| **B** | **Túnel saliente** (Cloudflare Tunnel / ZTNA) | El servidor abre un túnel **de salida**; el tráfico entra por el proveedor | **Muy alta** | Media | **No** (0 puertos abiertos) | **Sí** (políticas por identidad/dispositivo) |
| **C** | **Malla Zero Trust** (Tailscale / NetBird) | Red overlay cifrada; cada dispositivo es un nodo | **Muy alta** | Baja-Media | **No** | **Sí** (ACL por dispositivo) |
| **D** | **Reverse proxy público + WAF + IP allowlist** | Publica `https://dominio` con WAF y reglas por IP de origen | Media-Alta | Media | Sí (443) | Limitado (IPs dinámicas rompen allowlist) |
| **E** | **mTLS (certificado de cliente)** | Solo dispositivos con certificado válido completan el handshake TLS | **Muy alta** | Alta | Depende | **Sí** (por dispositivo, ideal) |

**Notas clave:**
- **A/B/C no publican puertos**: el servidor local inicia la conexión hacia afuera. Menor superficie de ataque.
- **D** exige IP pública fija y abre 443; IP allowlist falla con IPs móviles/dinámicas (celular, casa, hotel).
- **E (mTLS)** es el mecanismo más fuerte para "solo este dispositivo", y **puede combinarse** con A/B/C.

---

## 5. Arquitectura Recomendada

**Estrategia: capas combinadas.** Recomendación por defecto:

> **Túnel saliente / malla Zero Trust** (Cloudflare Tunnel **o** Tailscale) **+** **segmentación del módulo** en un panel Filament aislado **+** **mTLS o allowlist de dispositivo** **+** **MFA (TOTP)** **+** **hardening Laravel/IIS**. Solo **HTTPS 443** visible.

### 5.1. Diagrama de capas

```
   DISPOSITIVO AUTORIZADO (laptop corporativa)
        │  (1) TLS 1.3 + mTLS (cert de cliente)
        ▼
   ┌─────────────────────────────────────────────┐
   │ CAPA 1 · PERÍMETRO (túnel / VPN / ZTNA)      │  <- NO se abren puertos a Internet
   │   Cloudflare Access / Tailscale / WireGuard  │     o solo 443 si hay reverse proxy
   └─────────────────────────────────────────────┘
        │  (2) Política por identidad + dispositivo
        ▼
   ┌─────────────────────────────────────────────┐
   │ CAPA 2 · WAF / REVERSE PROXY (IIS o nginx)   │
   │   TLS, HSTS, headers, rate-limit, geo/UA     │
   └─────────────────────────────────────────────┘
        │  (3) Host = ordenes.dominio.com  (solo este vhost)
        ▼
   ┌─────────────────────────────────────────────┐
   │ CAPA 3 · APLICACIÓN (Laravel + Filament)     │
   │   Panel aislado /ordenes  · guard propio     │
   │   Middleware: DeviceAllowlist + IpAllowlist  │
   │              + RequireMfa                    │
   │   MFA TOTP · sesión cifrada · CSRF · rate    │
   └─────────────────────────────────────────────┘
        │  (4) Solo datos del módulo de órdenes
        ▼
   ┌─────────────────────────────────────────────┐
   │ CAPA 4 · DATOS (MariaDB) — SIN exposición     │
   │   backup cifrado · logs · activity_log        │
   └─────────────────────────────────────────────┘
```

### 5.2. Flujo de conexión de un dispositivo autorizado

1. El dispositivo abre `https://ordenes.maquindus.com` (DNS del proveedor de túnel o dominio propio).
2. **Capa 1** valida política (identidad federada / clave de dispositivo) y encamina el túnel. Sin túnel/válido → bloqueo.
3. **Capa 2** termina TLS, aplica HSTS/headers, rate-limit y reglas WAF.
4. **Capa 3** (Laravel) exige: certificado de dispositivo (mTLS) o fingerprint en allowlist, `MFA` ya verificado, y **rol con permiso** del módulo.
5. La sesión queda **cifrada**, con cookie `Secure; HttpOnly; SameSite`, ligada al dispositivo.
6. Todo el acceso queda en `activity_log` + logs del proxy.

### 5.3. Segmentación: el módulo NO es "el sistema"

Regla de oro: **el acceso remoto solo alcanza el módulo de Órdenes**, nunca los módulos de documentos/archivos/administración ni el panel LAN.

- Se crea un **segundo panel Filament** (p. ej. `id('ordenes')`, path `/ordenes`, o subdominio dedicado) con **su propio guard y middleware**.
- El panel LAN actual (`dashboard`) **permanece** tal cual, sin exposición.
- Ver implementación en §7.

## 6. Controles de Seguridad por Capa

### 6.1. Capa de Red (Perímetro)

- **Preferido:** túnel **saliente** (Cloudflare Tunnel) o **malla Zero Trust** (Tailscale). El servidor **no publica puertos**.
- **Alternativa:** VPN WireGuard con **1 puerto** UDP 51820, o reverse proxy con **solo 443/TCP**.
- **Firewall (Windows Defender Firewall / router):**
  - Reglas **deny by default** entrante.
  - Permitir **solo** los puertos estrictamente necesarios.
  - **Prohibido exponer:** `445` (SMB), `3307`/`3306` (MySQL), `8970` (PHP aux), `3389` (RDP sin VPN).
- **Aislamiento:** el módulo remoto no debe poder resolver/routear hacia la subred de archivos/SMB.

### 6.2. Capa de Transporte (TLS)

- **TLS 1.2 mínimo, TLS 1.3 preferido**; deshabilitar SSLv3/TLS1.0/TLS1.1 y cifrados débiles.
- Certificado válido emitido por CA pública (**Let's Encrypt** con renovación automática: `win-acme` para IIS, o `certbot` para nginx). **Sin certificados autofirmados** en el borde.
- **HSTS** (`Strict-Transport-Security: max-age=31536000; includeSubDomains`).
- Redirección forzada **HTTP → HTTPS**.
- **mTLS (opcional pero recomendado):** exigir **certificado de cliente** para las URLs del módulo; CA interna propia que emite un cert por dispositivo. Es el control **más fuerte de "solo este dispositivo"**.

### 6.3. Capa de Identidad (Usuarios)

- **MFA obligatorio (TOTP)** para todo usuario del módulo remoto. Filament 4 trae **autenticación multifactor integrada** (`->multiFactorAuthentication()`), activable sin librerías externas.
- Política de contraseñas ya existente (mín. 8, mixta, símbolos) — **mantener y reforzar** (mín. 12 para usuarios remotos).
- **Bloqueo por intentos fallidos** + throttling de login (rate-limit).
- Cuentas remotas **separadas** de las cuentas LAN (o roles remotos explícitos).
- Revocación rápida (desactivar usuario/rol → pierde acceso).

### 6.4. Capa de Dispositivo (el control clave)

Formas de identificar "**dispositivos puntuales**" (elegir 1-2 combinadas):

| Método | Fuerza | Notas |
|---|---|---|
| **Certificado de cliente (mTLS)** | ★★★★★ | Un cert por equipo; revocable; no depende de IP |
| **Allowlist de fingerprint** (UA+HU/CS) | ★★ | Débil; solo como señal secundaria |
| **Device binding de sesión** (cookie firmada a dispositivo) | ★★★ | Fuerza re-login si cambia de equipo |
| **Allowlist de IP** | ★★ | **Rompe con IP dinámica** (móvil/casa); solo si son IPs fijas |
| **ACL del proveedor ZTNA** (Tailscale/CF Access) | ★★★★★ | Política por dispositivo/identidad en el perímetro |
| **Registro de endpoints MDM** | ★★★★★ | Si el cliente ya usa Intune/SCCM/JAMF |

> Recomendación: **mTLS** o **ACL del túnel ZTNA** como control primario; **device binding** como refuerzo en la app.

### 6.5. Capa de Aplicación (Laravel)

Ver detalle de implementación en §7. Resumen:
- Sesiones **cifradas**, cookies `Secure/HttpOnly/SameSite`.
- **CSRF** activo (ya está vía `VerifyCsrfToken`).
- **Rate limiting** en login y acciones sensibles.
- **Trusted proxies** configurados (IP del túnel) para logs/HTTPS correctos.
- **Headers de seguridad** (HSTS, X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy).
- **APP_DEBUG=false** en producción; **errores genéricos** (sin stack trace al público).
- Restricción `canAccessPanel()` **por rol del módulo** (hoy retorna `true` → **corregir**).

### 6.6. Capa de Datos

- MariaDB **solo escucha en localhost**; jamás expuesta.
- **Backups cifrados** (`spatie/laravel-backup` + cifrado) y almacenados fuera del servidor.
- Archivos sensibles en `storage/app/private` (fuera del webroot).
- `APP_KEY` robusta y **no** versionada; rotación controlada.

### 6.7. Capa de Monitoreo y Auditoría

- `activity_log` del módulo (quién entró, qué cambió).
- Logs del proxy/túnel + IIS/nginx (con **IP real** vía trusted proxies).
- **Alertas** ante: intentos fallidos repetidos, accesos desde ubicación inesperada, picos de tráfico.
- **Fail2ban**/bloqueo automático (en Linux) o reglas de bloqueo equivalentes en el borde.

## 7. Implementación Técnica en Laravel / Filament

Todo **aditivo**. Referencias reales del proyecto: `bootstrap/app.php`, `bootstrap/providers.php`, `app/Providers/Filament/DashboardPanelProvider.php`, `config/session.php`.

### 7.1. Segundo Panel Filament aislado (`ordenes`)

Nuevo `app/Providers/Filament/OrdenesPanelProvider.php` (se registra en `bootstrap/providers.php`):

```php
class OrdenesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ordenes')
            ->path('ordenes')                 // o ->domain('ordenes.maquindus.com')
            ->login()
            ->multiFactorAuthentication([     // MFA nativo de Filament 4
                AppAuthentication::make()->recoverable(),
            ])
            ->authGuard('web')
            ->middleware([
                // ... base de Filament ...
                \App\Http\Middleware\EnsureIpIsAllowed::class,
                \App\Http\Middleware\EnsureDeviceIsAuthorized::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\RequireMfa::class,
            ])
            ->discoverResources(in: app_path('Filament/Ordenes/Resources'), for: 'App\\Filament\\Ordenes\\Resources')
            ->pages([...])->widgets([...]);
    }
}
```

> El panel LAN `dashboard` **no se toca**. El módulo remoto solo monta sus propios Resources.

### 7.2. Middleware de dispositivo y red

- `EnsureIpIsAllowed` — si se usa allowlist de IP/CIDR del túnel; lee allowed de config/env. Compatible con `TrustProxies` (IP real).
- `EnsureDeviceIsAuthorized` — valida **mTLS** (certificado de cliente vía cabecera del proxy, p.ej. `SSL_CLIENT_VERIFY`/`Cf-Client-Cert`) **o** fingerprint en tabla `authorized_devices`.
- `RequireMfa` — exige que el usuario tenga MFA confirmado antes de servir el panel.
- Todos siguen el patrón del middleware existente `app/Http/Middleware/EnsureUserHasAnyPermission.php`.

### 7.3. Tabla de dispositivos autorizados (nueva, aditiva)

```
authorized_devices
- id (uuid, PK)
- user_id (uuid, FK users, nullable)      -- o general del tenant
- label (string)                           -- "Laptop Ventas 01"
- fingerprint_hash (string)                -- hash de señal de dispositivo
- client_cert_subject (string, nullable)   -- CN del certificado mTLS
- allowed_ip (string, nullable)            -- opcional, solo si IP fija
- last_seen_at (timestamp)
- revoked_at (timestamp, nullable)
- timestamps
```

### 7.4. Sesión y cookies seguras (`config/session.php` / `.env`)

```ini
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=60
SESSION_DOMAIN=ordenes.maquindus.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### 7.5. Trusted proxies (`bootstrap/app.php`)

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: ['<IP-del-tunel-o-proxy>']);
    $middleware->alias([
        'permission.any'          => EnsureUserHasAnyPermission::class,
        'device.authorized'       => \App\Http\Middleware\EnsureDeviceIsAuthorized::class,
        'ip.allowed'              => \App\Http\Middleware\EnsureIpIsAllowed::class,
        'mfa.required'            => \App\Http\Middleware\RequireMfa::class,
    ]);
})
```

### 7.6. Rate limiting y headers

- `RateLimiter::for('login', ...)` y límites por usuario/IP en botones sensibles.
- Middleware de headers (o reglas del proxy): `Strict-Transport-Security`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Content-Security-Policy` (mínimo viable; Filament usa Livewire → CSP debe permitir sus inline/websockets).
- `APP_DEBUG=false`, `LOG_LEVEL=warning` en el entorno remoto.

### 7.7. `canAccessPanel` por rol del módulo

Hoy `User::canAccessPanel()` retorna `true`. **Cambio aditivo:** restringir a usuarios con permiso del módulo (rol remoto), sin afectar a los usuarios LAN:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $panel->getId() === 'ordenes'
        ? $this->hasPermission('client_orders.view')
        : true; // panel LAN: comportamiento actual preservado
}
```

### 7.8. Auditoría

El módulo ya usa `HasActivityLog`/`LogsActivity`. Se añaden eventos de **login remoto**, **dispositivo rechazado** y **dispositivo revocado** al `activity_log` + alertas.

## 8. Requisitos de Infraestructura y del Cliente

### 8.1. Infraestructura (a proveer / habilitar)

| Requisito | Detalle | ¿Quién? |
|---|---|---|
| **Dominio** | Subdominio dedicado, p.ej. `ordenes.maquindus.com` | Cliente |
| **DNS** | Registro CNAME/A al túnel o IP pública | Cliente / proveedor |
| **Salida a Internet** | El servidor debe poder **salir** (443) para túnel/malla | Cliente |
| **IP pública** | Fija, **solo** si NO se usa túnel/malla | Cliente/ISP |
| **Certificado TLS** | Let's Encrypt automático (win-acme/certbot) | Nosotros |
| **CA interna (mTLS)** | Si se activa mTLS, para emitir certs por dispositivo | Nosotros |
| **Cuenta proveedor** | Cloudflare Zero Trust o Tailscale (según opción) | Cliente |
| **Acceso a router/firewall** | Para reglas (o confirmar que no se requiere NAT entrante) | Cliente |
| **Windows Server / IIS** | Confirmar versión y acceso admin para vhost/bindings | Cliente |
| **MDM (opcional)** | Si ya existe (Intune/SCCM), para identificar endpoints | Cliente |

### 8.2. Del Cliente (organizacional)

- **Lista de dispositivos** autorizados (nombre, usuario, tipo).
- **Lista de usuarios remotos** y rol dentro del módulo.
- Aprobación del **modelo de identificación de dispositivo** (§6.4).
- Ventana de mantenimiento para la puesta en marcha.

---

## 9. Plan de Implementación por Fases y Estimación H/H

Base: arquitectura recomendada **túnel/ZTNA + segmentación + MFA + mTLS/allowlist + hardening**.

| Fase | Actividades | H/H |
|---|---|---|
| **S1 — Relevamiento y diseño** | Inventario de red/firewall, decisión de opción, diseño de segmentación, plan de certs | 8 |
| **S2 — Perímetro (túnel/ZTNA/VPN)** | Alta de túnel o VPN, DNS, TLS automático, reglas firewall, pruebas de conectividad | 16 |
| **S3 — Segmentación del módulo** | Panel Filament `ordenes`, guard, rutas, `canAccessPanel` por rol, aislar Resources | 24 |
| **S4 — Identidad (MFA + política)** | MFA TOTP, enrolamiento, reforzar contraseñas, throttling, bloqueo | 16 |
| **S5 — Dispositivo (mTLS / allowlist)** | CA interna + emisión de certs **o** tabla `authorized_devices` + middleware + enrolamiento | 16 |
| **S6 — Hardening (Laravel + servidor)** | Sesión cifrada, cookies, headers, trusted proxies, proxy/TLS, ocultar servicios internos | 16 |
| **S7 — Monitoreo y auditoría** | Logs, alertas, registro de eventos, bloqueo automático, dashboard de accesos | 12 |
| **S8 — Documentación y runbook** | Manual de operación, alta/baja de dispositivos, respuesta a incidentes | 8 |
| **S9 — QA de seguridad** | Pruebas: acceso no autorizado, mTLS sin cert, rate-limit, MFA, regresión LAN | 16 |
| **Subtotal** | | **132** |
| **Contingencia (+20%)** | Dependencias de red/ISP, variantes de entorno | 26 |
| **TOTAL ESTIMADO** | | **≈ 158 H/H** |

**Variaciones según opción elegida:**

| Escenario | Ajuste H/H |
|---|---|
| Solo segmentación + TLS + MFA + hardening (sin mTLS, sin túnel) | −30 |
| Túnel/ZTNA (en vez de VPN con puerto) | ±0 |
| **mTLS por dispositivo** (CA interna + emisión) | +16 a +24 |
| Reverse proxy público + IP allowlist (en vez de túnel) | −8 (pero menor seguridad) |

> Total realista en el rango **130–180 H/H** según decisiones del cliente.

## 10. Requisitos Previos Obligatorios del Cliente

Bloquean el cronograma. Ningún control de seguridad se implementa sin estos.

1. **Dominio / subdominio** propio o autorización para usar uno (p.ej. `ordenes.maquindus.com`).
2. **Decisión de la opción de acceso** (túnel/ZTNA, VPN, o reverse proxy) — §4.
3. **Lista de dispositivos autorizados** (equipo, usuario, ubicación habitual).
4. **Decisión de identificación de dispositivo** (mTLS vs allowlist vs MDM) — §6.4.
5. **Acceso a router/firewall e IIS** o confirmación de que no se abrirán puertos.
6. **ISP:** confirmar IP pública (fija/dinámica) y ausencia de **CGNAT** si se usara reverse proxy público (CGNAT impide publicar servicios → obliga a usar túnel/VPN).
7. **Cuenta** del proveedor elegido (Cloudflare / Tailscale) o visto bueno para crearla.
8. **Aprobación de MFA obligatorio** para usuarios remotos.
9. **Usuarios y roles** del módulo remoto definidos.
10. **Política de respaldo** aceptada (cifrado + ubicación externa).
11. **Ventana de mantenimiento** y **contacto técnico** responsable del lado cliente.

---

## 11. Preguntas Clarificadoras

### 11.1. Preguntas críticas (máx. 3 — cerrar antes de cotizar)

1. **¿Cuántos dispositivos y usuarios** exactamente? ¿Son **IPs fijas** o móviles (celular/casa/hotel)? (define mTLS vs IP allowlist vs ZTNA).
2. **¿El proveedor de Internet del cliente usa CGNAT / IP dinámica?** (define si es viable reverse proxy público o si **obliga** a túnel/VPN).
3. **¿Existe un **MDM/dominio corporativo** (Intune, AD, SCCM)?** (permite identificación fuerte de dispositivos y SSO en vez de certs manuales).

### 11.2. Preguntas secundarias

4. ¿Los dispositivos remotos son **propiedad de la empresa** o personales (BYOD)?
5. ¿Debe el acceso remoto ver **solo el módulo de órdenes** o también adjuntos/documentos de la orden?
6. ¿Se requiere **registro de auditoría** exportable con retención mínima (p.ej. 90 días)?
7. ¿Existe requisito **regulatorio** de seguridad (ISO 27001, PCI, etc.) o solo buenas prácticas?
8. ¿Se necesita **doble aprobación** para revocar/habilitar un dispositivo?
9. ¿Debe funcionar sin conexión (offline) o siempre online?
10. ¿Se espera **SSO** (Azure AD/Google) o login local del sistema?

## 12. Riesgos y Mitigación

| # | Riesgo | Impacto | Mitigación |
|---|---|---|---|
| SR1 | **CGNAT del ISP** impide publicar servicio | Alto | Usar **túnel saliente/VPN**; no depender de NAT entrante |
| SR2 | IP allowlist se rompe con IP dinámica (móvil) | Medio | Preferir **mTLS/ZTNA**; no basar seguridad solo en IP |
| SR3 | Exponer por error servicios internos (SMB/DB/8970) | **Crítico** | Regla deny-by-default; solo 443; auditoría de puertos |
| SR4 | Certificados TLS mal renovados → caída | Medio | Automatizar renovación + alerta de expiración |
| SR5 | Convivir con el panel LAN (regresión) | Alto | Panel `ordenes` aislado; `canAccessPanel` condicional; pruebas LAN |
| SR6 | MFA no adoptado por usuarios | Medio | Enrolamiento asistido + política obligatoria |
| SR7 | Robo de sesión/cookie | Medio | `SESSION_ENCRYPT=true`, cookies Secure/HttpOnly/SameSite, device binding |
| SR8 | Fuga del certificado de dispositivo | Medio | Passphrase/keystore + revocación por cert; MFA como segundo factor |
| SR9 | Superficie por dependencia de terceros (CF/Tailscale) | Bajo-Medio | Cuenta corporativa, MFA en el proveedor, documentar plan de salida |
| SR10 | Sin trazabilidad ante incidente | Medio | `activity_log` + logs de proxy + alertas |

**Cero regresión:** el panel LAN actual no se modifica en su comportamiento (salvo `canAccessPanel`, que preserva el `true` para `dashboard`). Todo lo demás es nuevo.

---

## 13. Checklist de Verificación de Seguridad (Aceptación)

Antes de dar por bueno el despliegue:

- [ ] Solo **HTTPS 443** visible; **0** puertos internos expuestos (445/3307/8970 cerrados).
- [ ] TLS ≥ 1.2 (ideal 1.3) + **HSTS** + redirección HTTP→HTTPS.
- [ ] Certificado TLS válido y **renovación automática** probada.
- [ ] **Túnel/VPN** verificado: sin túnel → sin acceso.
- [ ] **mTLS** o `authorized_devices`: dispositivo no autorizado → **rechazado**.
- [ ] **MFA** obligatorio y probado (recovery codes).
- [ ] Sesión **cifrada**; cookies con `Secure/HttpOnly/SameSite`; tiempo de vida acotado.
- [ ] **Rate-limit** y bloqueo por intentos fallidos probados.
- [ ] `APP_DEBUG=false`; **sin stack traces** al público.
- [ ] `canAccessPanel()` restringe el panel `ordenes` por rol.
- [ ] **Auditoría** de accesos y cambios en `activity_log` + logs de proxy con IP real.
- [ ] **Regresión LAN** verificada: panel `dashboard`, documentos, `gestor://`, "ver en carpeta" siguen funcionando.
- [ ] **Backups** cifrados y restauración probada.
- [ ] **Runbook** y procedimiento de alta/baja de dispositivos entregado.

---

## 14. Anexo — Configuración Propuesta (referencia)

### 14.1. `.env` (entorno remoto)

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ordenes.maquindus.com
LOG_LEVEL=warning

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=60
SESSION_DOMAIN=ordenes.maquindus.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Perímetro / dispositivo (nombres referenciales)
REMOTE_ACCESS_ENABLED=true
REMOTE_ALLOWED_IPS=            # opcional, CIDR del túnel/ZTN
REMOTE_REQUIRE_MTLS=true       # exige certificado de cliente
```

### 14.2. Matriz de decisión rápida

| Situación del cliente | Opción recomendada |
|---|---|
| Pocos dispositivos fijos, sin MDM | **Túnel/ZTNA + mTLS + MFA** |
| Dispositivos móviles / IP dinámica | **Túnel/ZTNA + MFA + device binding** (NO IP allowlist) |
| Ya tiene MDM + AD | **Túnel/ZTNA + SSO + device posture + MFA** |
| Prefiere VPN tradicional | **WireGuard + MFA** (1 puerto UDP) |
| Requisito máximo sin depender de terceros | **WireGuard + mTLS + MFA** (autogestionado) |

---

*Documento técnico de seguridad. Versión 1.0.*
*Estado: PENDIENTE DE APROBACIÓN Y RESPUESTAS DEL CLIENTE.*
*No iniciar implementación sin "OK".*
