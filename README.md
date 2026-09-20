# 🌿 EcoPoints — Plataforma de Gestión de Reciclaje y Recompensas

> **"Conecta tu trabajo con nuevas oportunidades de recolección y convierte tu esfuerzo en beneficios."**

---

## 📌 1. Objetivo del Proyecto

**EcoPoints** es una plataforma digital que articula y profesionaliza el ecosistema de reciclaje en Bogotá D.C., conectando de manera directa a tres actores:

$$\text{Ciudadanos} \longrightarrow \text{Recicladores} \longrightarrow \text{Centros de Acopio}$$

El **Reciclador es el actor principal del sistema**. La plataforma está concebida como una **herramienta profesional de trabajo** que le permite:
- Encontrar oportunidades y solicitudes de recolección cercanas.
- Optimizar y organizar recorridos y rutas.
- Localizar centros de acopio autorizados para pesaje y entrega.
- Acumular **Ecopoints** certificados tras la validación oficial del centro.
- Canjear sus puntos acumulados por beneficios reales (equipamiento, bonos de alimentación, conectividad).
- Visualizar su impacto ambiental y avanzar en su nivel de reconocimiento.

### Regla Fundamental del Negocio
> ⚠️ **Los Ecopoints NO se generan al aceptar una solicitud ni al registrar la recolección.**  
> El ciclo real y seguro es:  
> $$\textbf{Aceptar} \longrightarrow \textbf{Recoger} \longrightarrow \textbf{Entregar} \longrightarrow \textbf{Pesar} \longrightarrow \textbf{Validar} \longrightarrow \textbf{Acreditar Ecopoints}$$

---

## 🛠️ 2. Tecnologías Utilizadas

Este proyecto sigue una arquitectura web limpia, robusta y sin dependencias innecesarias de frameworks modernos:

- **Backend:** PHP 7.2+ / PHP 8.x nativo orientado a objetos bajo arquitectura MVC.
- **Base de Datos:** MySQL 5.7+ / MySQL 8.0+ / MariaDB con PDO (PHP Data Objects).
- **Frontend:** HTML5 semántico, CSS3 moderno con variables HSL/Hex, JavaScript Vanilla interactivo.
- **Tipografía:** *Poppins* (Google Fonts) en pesos 400, 500, 600, 700 y 800.
- **Servidor Local:** Apache en XAMPP + phpMyAdmin.

---

## 🏛️ 3. Arquitectura Modelo–Vista–Controlador (MVC)

El proyecto separa estrictamente las responsabilidades para garantizar mantenibilidad, seguridad y escalabilidad:

```text
c:/xampp/htdocs/Proyecto_Ecopoints/
│
├── app/
│   ├── config/
│   │   ├── config.php             # Constantes globales, sesiones seguras y BASE_URL
│   │   └── database.php           # Conexión Singleton PDO con emulación apagada y UTF-8
│   │
│   ├── helpers/
│   │   ├── auth_helper.php        # currentUser(), requireLogin(), requireRole(), logoutUser()
│   │   ├── csrf_helper.php        # csrf_token(), csrf_input(), verify_csrf()
│   │   └── view_helper.php        # h() contra XSS, url(), asset(), flash()
│   │
│   ├── middleware/
│   │   └── AuthMiddleware.php     # Interceptores de ruta por rol (reciclador, ciudadano, centro)
│   │
│   ├── models/
│   │   ├── Model.php              # Clase base PDO con soporte transaccional (commit/rollBack)
│   │   ├── Usuario.php            # Autenticación, hash BCRYPT y búsqueda por correo
│   │   ├── Localidad.php          # Catálogo oficial de las 20 localidades de Bogotá D.C.
│   │   ├── Reciclador.php         # Perfil reciclador, cálculo dinámico de nivel y métricas
│   │   ├── Ciudadano.php          # Perfil ciudadano domiciliario
│   │   └── CentroAcopio.php       # Perfil del establecimiento y horarios de pesaje
│   │
│   ├── controllers/
│   │   ├── Controller.php         # Clase base con view(), redirect() y json()
│   │   ├── AuthController.php     # Flujos de Login, Registro dinámico multi-rol y Logout
│   │   ├── RecicladorController.php # Dashboard del reciclador y gestión operativa
│   │   ├── CiudadanoController.php  # Panel del ciudadano generador
│   │   └── CentroAcopioController.php # Panel del centro receptor y pesaje
│   │
│   └── views/
│       ├── layouts/
│       │   ├── header.php         # Head HTML, meta viewport y fuentes Poppins
│       │   ├── sidebar.php        # Navegación lateral de 252px fiel a Figma
│       │   ├── topbar.php         # Barra superior con píldora de puntos y avatar
│       │   ├── profile_panel.php  # Panel lateral deslizable de perfil
│       │   └── footer.php         # Cierre de contenedor y carga de app.js
│       ├── auth/
│       │   ├── login.php          # Login split-screen de alta fidelidad Figma
│       │   └── registro.php       # Registro multi-rol con selector de localidades
│       ├── reciclador/
│       │   └── dashboard.php      # Dashboard principal ¡Hola, Carlos! con tarjetas métricas
│       ├── ciudadano/
│       │   └── dashboard.php      # Panel del ciudadano
│       └── centro_acopio/
│           └── dashboard.php      # Panel del centro de acopio
│
├── public/
│   ├── css/
│   │   └── styles.css             # Hoja de estilos unificada basada en el diseño Figma
│   ├── js/
│   │   ├── app.js                 # Control de sidebar, notificaciones y perfil
│   │   └── auth.js                # Alternancia de rol y validación de contraseñas
│   └── img/
│       └── [Logos e isotipos oficiales de EcoPoints]
│
├── database/
│   └── ecopoint.sql               # Script SQL completo y normalizado con localidades
│
├── tests/
│   └── verify.php                 # Suite automatizada de pruebas CLI
│
├── .htaccess                      # Configuración de URLs amigables en Apache
├── index.php                      # Enrutador Front Controller
└── README.md                      # Documentación del proyecto
```

---

## 🔒 4. Medidas de Seguridad Implementadas

1. **Hash Criptográfico de Contraseñas:**
   - Implementado mediante `password_hash($password, PASSWORD_BCRYPT)` con costo 10 y `password_verify()`. Nunca se almacenan ni comparan contraseñas en texto plano.
2. **Protección contra Inyecciones SQL:**
   - Conexiones administradas con PDO, sentencias preparadas nativas (`PDO::ATTR_EMULATE_PREPARES => false`) y paso de parámetros vinculados (`bindValue`).
3. **Protección contra CSRF (Cross-Site Request Forgery):**
   - Tokens criptográficos de 64 caracteres hexadecimales generados con `random_bytes(32)`. Se incrustan en formularios POST mediante `csrf_input()` y se verifican con `hash_equals()` en tiempo constante.
4. **Protección contra XSS (Cross-Site Scripting):**
   - Función auxiliar `h($string)` que escapa caracteres con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` en todas las salidas dinámicas.
5. **Prevención de Session Fixation:**
   - Ejecución obligatoria de `session_regenerate_id(true)` inmediatamente después de cada inicio de sesión exitoso.
6. **Control de Acceso Basado en Roles (RBAC):**
   - Interceptores en `AuthMiddleware.php` y `auth_helper.php` (`requireLogin()`, `requireRole()`). Un usuario no puede saltarse la protección escribiendo la URL manualmente en el navegador.

---

## 🗺️ 5. Catálogo Normalizado de Localidades

De acuerdo al requerimiento #17, las localidades están completamente normalizadas en la tabla `localidades` con clave foránea `id_localidad` en usuarios y entidades:

| ID | # | Localidad | ID | # | Localidad |
|:---:|:---:|:---|:---:|:---:|:---|
| 1 | 1 | Usaquén | 11 | 11 | Suba |
| 2 | 2 | Chapinero | 12 | 12 | Barrios Unidos |
| 3 | 3 | Santa Fe | 13 | 13 | Teusaquillo |
| 4 | 4 | San Cristóbal | 14 | 14 | Los Mártires |
| 5 | 5 | Usme | 15 | 15 | Antonio Nariño |
| 6 | 6 | Tunjuelito | 16 | 16 | Puente Aranda |
| 7 | 7 | Bosa | 17 | 17 | La Candelaria |
| 8 | 8 | Kennedy | 18 | 18 | Rafael Uribe Uribe |
| 9 | 9 | Fontibón | 19 | 19 | Ciudad Bolívar |
| 10 | 10 | Engativá | 20 | 20 | Sumapaz |

---

## 🚀 6. Instalación y Puesta en Marcha en XAMPP

### Paso 1: Ubicación del Proyecto
Coloca la carpeta del proyecto en la raíz web de XAMPP:
```text
C:\xampp\htdocs\Proyecto_Ecopoints\
```

### Paso 2: Iniciar Servidores en XAMPP
Abre el **XAMPP Control Panel** y presiona **Start** en los módulos:
- **Apache** (Puerto 80 / 443)
- **MySQL** (Puerto 3306)

### Paso 3: Importar la Base de Datos
1. Abre tu navegador y accede a phpMyAdmin:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Ve a la pestaña **Importar**.
3. Selecciona el archivo:
   ```text
   C:\xampp\htdocs\Proyecto_Ecopoints\database\ecopoint.sql
   ```
4. Haz clic en **Continuar** / **Importar**.
*(El script creará automáticamente la base de datos `ecopoint` con todas las tablas, localidades y usuarios semilla).*

### Paso 4: Acceder a la Aplicación
Abre en tu navegador la URL:
```text
http://localhost/Proyecto_Ecopoints/
```
*(Serás redirigido automáticamente a la pantalla de Inicio de Sesión).*

---

## 🔑 7. Cuentas y Credenciales de Prueba

Para probar de inmediato los diferentes flujos y paneles por rol:

| Rol | Correo Electrónico | Contraseña | Panel Destino |
|:---|:---|:---|:---|
| **Reciclador** *(Actor Principal)* | `reciclador@ecopoint.app` | `Carlos123*` | `/reciclador` (Dashboard ¡Hola, Carlos!) |
| **Ciudadano** | `ciudadano@ecopoint.app` | `Maria123*` | `/ciudadano` (Panel de Solicitudes) |
| **Centro de Acopio** | `centro@ecopoint.app` | `Centro123*` | `/centro-acopio` (Recepción y Pesaje) |

---

## 🧪 8. Ejecución de Pruebas Automatizadas

El proyecto incluye una suite de pruebas CLI que valida 24 aserciones críticas de seguridad y base de datos:

Desde PowerShell o CMD ejecuta:
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\Proyecto_Ecopoints\tests\verify.php
```

Resultado esperado:
```text
RESULTADOS DE LAS PRUEBAS: 24 / 24 PASADAS CON ÉXITO
ESTADO: 🎉 TODAS LAS PRUEBAS DE LA FASE 1 CUMPLIDAS SATISFACTORIAMENTE
```

---

## 📅 9. Plan de Fases del Proyecto

- [x] **Fase 1 (Actual):** Arquitectura MVC + Base de Datos + Localidades + Registro Multi-Rol + Login + Sesiones + Roles + Seguridad + Diseño Figma.
- [ ] **Fase 2:** Módulo de Solicitudes Domiciliarias del Ciudadano.
- [ ] **Fase 3:** Gestión de Solicitudes Cercanas para el Reciclador (Filtros y Detalle).
- [ ] **Fase 4:** Mi Ruta y Organización de Recorridos.
- [ ] **Fase 5:** Directorio y Mapa de Centros de Acopio.
- [ ] **Fase 6:** Entregas, Pesaje Oficial y Validación en Centro de Acopio.
- [ ] **Fase 7:** Generación y Acreditación de Ecopoints.
- [ ] **Fase 8:** Catálogo de Beneficios y Sistema de Canjes.
- [ ] **Fase 9:** Mi Impacto Ambiental, Estadísticas y Reconocimientos.
