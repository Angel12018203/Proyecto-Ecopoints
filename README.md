# 🌿 Ecopoint — Plataforma Web de Reciclaje y Economía Circular

Plataforma web modular desarrollada en **PHP** y **MySQL** que conecta:
**Ciudadanos → Recicladores → Centros de Acopio**.

---

## 🚀 Puesta en Marcha en Entorno Local (XAMPP / WAMP / Laragon)

### 1. Base de Datos (phpMyAdmin)
1. Abre tu panel de **phpMyAdmin** (usualmente en `http://localhost/phpmyadmin`).
2. Haz clic en la pestaña **Importar**.
3. Selecciona el archivo:
   ```
   database/ecopoint.sql
   ```
4. Haz clic en **Importar / Ejecutar**. Se creará la base de datos `ecopoint` con todas sus tablas, relaciones foráneas, índices y datos iniciales de prueba.

### 2. Configuración de Conexión (Opcional)
Si tu MySQL tiene usuario o contraseña diferente a `root` y vacío, ajusta los valores en:
`config/database.php`:
```php
private static string $host = '127.0.0.1';
private static string $dbname = 'ecopoint';
private static string $user = 'root';
private static string $pass = '';
```

### 3. Ejecutar el Proyecto
- Si usas **XAMPP / WAMP / Laragon**, coloca la carpeta en `htdocs` o `www` y abre:
  ```
  http://localhost/Proyecto_Ecopoints/
  ```
- O si ejecutas el servidor embebido de PHP desde la terminal en esta carpeta:
  ```bash
  php -S localhost:8000
  ```
  y abre `http://localhost:8000` en tu navegador.

---

## 👥 Cuentas de Demostración Iniciales

| Rol | Correo Electrónico | Contraseña | Perfil Inicial |
| :--- | :--- | :--- | :--- |
| **♻️ Reciclador** | `reciclador@ecopoint.app` | `Carlos123*` | Carlos Rodríguez • Suba • 2.850 Ecopuntos • Reciclador Destacado |
| **👤 Ciudadano** | `ciudadano@ecopoint.app` | `Maria123*` | María Gómez • Suba Centro • Calle 145 # 92-30 |

---

## 🧪 Demostración del Flujo Maestro de Prueba (Punto 39)

Sigue estos pasos para verificar el ciclo completo de negocio:

1. **Inicio:** Inicia sesión con la cuenta de ciudadana: `ciudadano@ecopoint.app` / `Maria123*`.
2. **Crear Solicitud:** Ve a **Solicitar Recolección**, indica 30 kg de Cartón en Suba, horario 2:00 p.m. - 5:00 p.m. y publica.
3. **Cerrar Sesión:** Haz clic en *Cerrar Sesión* en el menú lateral.
4. **Ingresar como Reciclador:** Inicia sesión con `reciclador@ecopoint.app` / `Carlos123*`.
5. **Ver Solicitudes Cercanas:** En tu panel o en *Solicitudes Cercanas*, observa la Solicitud (Cartón, Suba, ~800 m).
6. **Aceptar Solicitud:** Haz clic en **Aceptar Solicitud** (el estado pasa a *Aceptada* y se agrega a *Mi Ruta*).
7. **Registrar Recolección:** Ve a *Mis Recolecciones* o *Mi Ruta*, haz clic en **⚖️ Registrar Peso** e ingresa **28 kg** recolectados en sitio. *(Comprueba que **NO** se generan Ecopuntos aún)*.
8. **Registrar Entrega:** Ve a *Entregas & Pesaje*, registra el ingreso de los 28 kg en el *EcoCentro Suba Verde*.
9. **Validar Pesaje:** En la tabla de entregas, haz clic en **⚖️ Validar Pesaje (Centro)**, confirma el peso verificado en balanza digital (**27.5 kg**).
10. **Acreditación Automática:** El sistema aplica la regla `27.5 kg × 10 pts/kg = +275 Ecopuntos`.
11. **Consultar Ecopuntos:** Ve a *Mis Ecopuntos* y observa el nuevo saldo incrementado y el movimiento en tu historial.
12. **Canje de Beneficios:** Ve a *Beneficios & Canjes* y prueba canjear un beneficio con control estricto de saldo.

---

## 📁 Arquitectura Modular de Archivos

```
ecopoint/
├── index.php                      # Portada y presentación institucional
├── login.php                      # Inicio de sesión con autenticación segura
├── seleccionar_rol.php            # Selección de rol previo al formulario
├── registro.php                   # Formulario adaptativo por rol (reciclador / ciudadano)
├── recuperar.php                  # Solicitud de recuperación de contraseña
├── logout.php                     # Cierre y destrucción de sesión
│
├── config/
│   └── database.php               # Conexión PDO Singleton (MySQL / utf8mb4)
│
├── auth/
│   ├── auth.php                   # Sesiones, hash, helpers y regla de negocio de Ecopoints
│   └── permissions.php            # Middleware de protección de rutas y control de roles
│
├── includes/
│   ├── header.php                 # Header HTML, navegación y saldo en tiempo real
│   ├── sidebar.php                # Menú lateral dinámico exclusivo por rol
│   ├── alerts.php                 # Sistema de notificaciones flash
│   └── footer.php                 # Pie de página y carga de scripts
│
├── reciclador/
│   ├── dashboard.php              # Métricas, oportunidades cercanas y acceso a rutas
│   ├── solicitudes.php            # Oportunidades de recolección filtradas
│   ├── solicitud_detalle.php      # Vista detallada de solicitud y mapa
│   ├── ruta.php                   # Itinerario ordenado de paradas diarias
│   ├── recolecciones.php          # Mis recolecciones en curso y completadas
│   ├── registrar_recoleccion.php  # Registro de peso en sitio (sin generar puntos)
│   ├── centros.php                # Directorio de centros de acopio autorizados
│   ├── entregas.php               # Entrega y simulación de pesaje oficial
│   ├── ecopuntos.php              # Historial de puntos validados y saldo
│   ├── beneficios.php             # Catálogo de recompensas y canjes
│   ├── impacto.php                # Indicadores de impacto ambiental (árboles, CO2, agua)
│   ├── logros.php                 # Escalafón de reciclaje y medallas
│   └── perfil.php                 # Configuración de zona y vehículo
│
├── ciudadano/
│   ├── dashboard.php              # Resumen de solicitudes y botón de solicitar
│   ├── solicitar.php              # Formulario para publicar material reciclable
│   ├── solicitudes.php            # Monitoreo de solicitudes y opción de cancelar
│   ├── detalle_solicitud.php      # Trazabilidad en vivo con stepper
│   ├── historial.php              # Resumen histórico de kg donados
│   └── perfil.php                 # Configuración de dirección y preferencias
│
├── css/
│   └── style.css                  # Estilos oficiales Ecopoint (Poppins, verde, dorado)
│
├── js/
│   └── app.js                     # Interactividad, modales y cálculo de puntos
│
└── database/
    └── ecopoint.sql               # Esquema DDL + DML completo para phpMyAdmin
```

---

## 🛡️ Seguridad Implementada
- **Contraseñas:** Hasheadas con `password_hash()` (algoritmo BCRYPT) y verificadas con `password_verify()`.
- **Inyecciones SQL:** 100% de las consultas preparadas mediante PDO con bindings (`:parametro`).
- **XSS:** Escape de salidas con función `h()` (`htmlspecialchars`).
- **CSRF:** Tokens criptográficos obligatorios en formularios POST.
- **Sesiones:** Regeneración de `session_id()` tras login para prevenir Session Fixation.
- **Control de Acceso:** Middleware estricto por rol (`requireRole('reciclador')`, `requireRole('ciudadano')`).
