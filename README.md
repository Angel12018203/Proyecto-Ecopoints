# 🌿 EcoPoints — Plataforma de Gestión de Reciclaje y Recompensas

> **"Conecta tu trabajo con nuevas oportunidades de recolección y convierte tu esfuerzo en beneficios."**

---

## 📌 1. Objetivo del Proyecto

**EcoPoints** es una plataforma digital que articula y profesionaliza el ecosistema de reciclaje en Bogotá D.C., conectando de manera directa a tres actores:

$$\text{Ciudadanos} \longrightarrow \text{Recicladores} \longrightarrow \text{Centros de Acopio}$$

El **Reciclador es el actor principal del sistema**. La plataforma está concebida como una **herramienta profesional de trabajo** que le permite:
- Encontrar oportunidades y solicitudes de recolección cercanas.
- Optimizar y organizar recorridos y rutas de recolección.
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

- **Backend:** PHP 7.2+ / PHP 8.x nativo orientado a objetos bajo arquitectura **Modelo–Vista–Controlador (MVC)**.
- **Base de Datos:** MySQL 5.7+ / MySQL 8.0+ / MariaDB con PDO (PHP Data Objects).
- **Frontend:** HTML5 semántico, CSS3 moderno con variables HSL/Hex, JavaScript Vanilla interactivo.
- **Tipografía:** *Poppins* (Google Fonts) en pesos 400, 500, 600, 700 y 800.
- **Servidor Local:** Apache en XAMPP + phpMyAdmin.

---

## 🏛️ 3. Arquitectura del Proyecto y Estructura Limpia

La estructura del proyecto está totalmente organizada sin archivos huérfanos ni redundancias:

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
│   │   ├── BaseModel.php          # Clase base PDO para todos los modelos
│   │   ├── CentroAcopio.php       # Consultas y registro de centros de acopio
│   │   ├── Ciudadano.php          # Métricas, solicitudes y perfil ciudadano
│   │   ├── Localidad.php          # Catálogo oficial de 20 localidades de Bogotá D.C.
│   │   ├── Reciclador.php         # Transacciones, perfil, entregas y Ecopoints
│   │   └── Usuario.php            # Autenticación segura, BCRYPT y registro multi-rol
│   │
│   ├── controllers/
│   │   ├── AuthController.php     # Login, Registro Multi-Rol y Logout seguro
│   │   ├── CentroAcopioController.php # Dashboard y recepción para centros de acopio
│   │   ├── CiudadanoController.php    # Dashboard y solicitudes para ciudadanos
│   │   └── RecicladorController.php   # Dashboard principal de alta fidelidad Figma
│   │
│   └── views/
│       ├── auth/
│       │   ├── login.php          # Login dividido Figma con logo contrapuesto y responsivo
│       │   └── registro.php       # Registro interactivo multi-rol con localidades
│       ├── ciudadano/
│       │   └── dashboard.php      # Vista para ciudadanos generadores
│       ├── centro/
│       │   └── dashboard.php      # Vista para centros receptores
│       ├── reciclador/
│       │   └── dashboard.php      # Panel operativo del reciclador (252px sidebar, métricas)
│       └── layouts/
│           ├── header.php         # Cabecera HTML con Favicon oficial e isotipo
│           ├── footer.php         # Scripts globales y cierre del documento
│           ├── sidebar.php        # Barra lateral fija con logotipo oficial transparente
│           ├── topbar.php         # Cabecera superior con píldora de puntos y avatar oficial
│           └── profile_panel.php  # Cajón lateral animado del perfil con isotipo
│
├── database/
│   ├── ecopoint.sql                                # ⭐️ SCRIPT MAESTRO ÚNICO: Estructura + 60 recicladores + 6.377 entregas + 1.380 canjes + Vista Diccionario
│   ├── BASE_ORIGINAL_Ecopoints_Mineria_Datos.csv  # Base cruda con problemas de calidad (725 registros, 10 columnas)
│   └── BASE_PREPARADA_Ecopoints_Mineria_Datos.csv # Base limpia y depurada para minería (641 registros, 13 columnas)
│
├── img/                                            # Recursos gráficos oficiales de la marca (logos e isotipos)
│
├── public/
│   ├── css/
│   │   └── styles.css                              # Sistema de diseño CSS completo (tokens, componentes y utilidades)
│   ├── js/
│   │   └── app.js                                  # Interactividad del frontend, menú hamburguesa y panel de perfil
│   └── img/                                        # Recursos públicos servidos por el servidor web
│
├── tests/
│   └── verify.php                                  # Suite CLI de 24 pruebas automatizadas de seguridad y lógica
│
├── .htaccess                                       # Enrutamiento amigable Apache Front Controller
├── index.php                                       # Enrutador Front Controller principal
└── README.md                                       # Documentación integral del proyecto
```

---

## 📊 4. Homologación con Minería de Datos y Diccionario de Datos Oficial

Para responder a los requerimientos de la asignatura **Minería de Datos (Proyecto Integrador 2026-2)** sin alterar las tablas operativas transaccionales, se homologó la base de datos con el **Diccionario de Datos oficial**:

### A. Granularidad y Mapeo
* **Granularidad:** Cada fila representa el desempeño mensual consolidado de un reciclador.
* **Dimensión:** 60 recicladores evaluados a lo largo de los 12 meses de 2025.
* **Integración Nativa en Tablas Operacionales:** Todas las transacciones (6.377 entregas validadas, 6.377 recolecciones, 6.377 solicitudes, 6.377 transacciones de ecopuntos y 1.380 canjes de beneficios) viven **dentro de las tablas reales de la base de datos**, sin necesidad de crear tablas secundarias ni redundantes.

### B. Datasets de Referencia en `database/`
Para trazabilidad académica y referencia metodológica del proceso ETL/EDA, se incluyen los dos datasets oficiales:
1. **`BASE_ORIGINAL_Ecopoints_Mineria_Datos.csv` (725 registros, 10 columnas):**
   * Contiene anomalías intencionales de calidad de datos para prácticas de limpieza: valores nulos, registros duplicados, tipos/formatos inconsistentes, valores textuales como *"trescientos"* o *"diez"*, categorías sin homologar (`PLASTICO`, `plastico`, `Cartón `), fechas erróneas (`2025/13/01`) y la columna irrelevante `observacion_interna`.
2. **`BASE_PREPARADA_Ecopoints_Mineria_Datos.csv` (641 registros, 13 columnas):**
   * Base depurada y limpia: sin nulos en variables requeridas, sin duplicados, tipos numéricos correctos, categorías homologadas, valores fuera de rango eliminados y 4 variables temporales/analíticas derivadas: `año`, `mes_num`, `dia_semana_inicio` y `nivel_actividad`.

### C. Vista SQL Consolidada (`vw_diccionario_ecopoints_mensual`)

La base de datos incluye la vista `vw_diccionario_ecopoints_mensual`, la cual proyecta de forma nativa las 9 columnas exactas exigidas por el Diccionario de Datos:

| Campo del Diccionario | Tipo de Dato | Origen Operacional Transaccional |
| :--- | :--- | :--- |
| `reciclador_id` | `BIGINT/INT` | `usuarios.id_usuario` (Reciclador con `id_rol = 1`) |
| `mes` | `DATE` | Primer día del mes (`YYYY-MM-01`) derivado de `entregas.fecha_entrega` |
| `material_principal` | `VARCHAR(60)` | Categoría predominante en kg validados (`Cartón`, `Plástico`, `Vidrio`, `Metales`, `Papel`) |
| `kg_recolectados` | `DECIMAL(10,2)` | Suma de `entregas.cantidad_validada` |
| `entregas` | `INT` | Conteo de `entregas.id_entrega` validadas |
| `diversidad_materiales` | `INT` | Conteo de materiales distintos (`solicitudes.id_material`) en el mes |
| `beneficios_redimidos` | `INT` | Conteo de `canjes.id_canje` reclamados en el mes |
| `puntos_obtenidos` | `DECIMAL(10,2)` | Suma de `ecopuntos.cantidad_puntos` generados en el mes |
| `puntos_mes_siguiente` | `DECIMAL(10,2)` | **Variable objetivo ($y$)** calculada con la función de ventana `LEAD(puntos_obtenidos, 1) OVER (PARTITION BY reciclador_id ORDER BY mes)` |

> [!NOTE]
> En el mes de diciembre (mes 12), el campo `puntos_mes_siguiente` resulta en `NULL` para los 60 recicladores, cumpliendo exactamente con la condición descrita en la guía de Minería de Datos (Página 10).

---

## 🎨 5. Integración de la Identidad Visual Oficial (`img/`)

Se integraron de forma estratégica los recursos gráficos oficiales de la marca EcoPoints:

* **Isotipo Independiente (`logo-isotipo.jpg`):** Configurado como **Favicon** oficial del navegador en todas las vistas, como **Avatar de usuario** en la barra superior (`topbar.php`) y en el panel lateral desplegable del perfil (`profile_panel.php`).
* **Logotipo Horizontal (`logo-horizontal.jpg`):** Incorporado en la cabecera del formulario de inicio de sesión (`login.php`).
* **Logotipo Compacto (`logo-compacto.jpg`):** Visible en la barra superior cuando el menú hamburguesa colapsa el sidebar.
* **Versión Contrapuesta sobre Fondo Verde (`logo-contrapuesto.jpg`):** Integrado en el panel izquierdo de alta fidelidad con degradado corporativo `#087A3D` en `login.php` y `registro.php`.
* **Versión Transparente PNG (`logo-transparente.png`):** Integrado en la barra lateral principal (`sidebar.php`) para una integración limpia y nítida.

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

### Paso 3: Importar la Base de Datos Única
1. Abre tu navegador y accede a phpMyAdmin:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Ve a la pestaña **Importar**.
3. Selecciona el archivo maestro:
   ```text
   C:\xampp\htdocs\Proyecto_Ecopoints\database\ecopoint.sql
   ```
4. Haz clic en **Importar**.  
   *(Este único archivo crea la base de datos `ecopoint`, todas sus 13 tablas, los catálogos de Bogotá, los 60 recicladores, las 6.377 entregas operativas, los 1.380 canjes y la vista analítica del Diccionario de Datos).*

### Paso 4: Acceder a la Aplicación
Abre en tu navegador la URL:
```text
http://localhost/Proyecto_Ecopoints/
```
*(Serás redirigido automáticamente a la pantalla de Inicio de Sesión).*

---

## 🔑 7. Cuentas y Credenciales de Prueba

| Rol | Correo Electrónico | Contraseña | Panel Destino |
|:---|:---|:---|:---|
| **Reciclador #1** *(Actor Principal)* | `reciclador@ecopoint.app` | `Carlos123*` | `/reciclador` (Dashboard ¡Hola, Carlos!) |
| **Reciclador #10** | `angel@ecopoint.app` | `Reciclador2025*` | `/reciclador` (Dashboard ¡Hola, Angel David!) |
| **Recicladores #2 al #60** | `reciclador_2@ecopoint.app` ... `reciclador_60@ecopoint.app` | `Reciclador2025*` | `/reciclador` |
| **Ciudadano** | `ciudadano@ecopoint.app` | `Maria123*` | `/ciudadano` (Panel de Solicitudes) |
| **Centro de Acopio** | `centro@ecopoint.app` | `Centro123*` | `/centro-acopio` (Recepción y Pesaje) |
| **Administrador** | `admin@ecopoint.app` | `Admin123*` | `/admin` (Panel Administrativo y Analítica de Datos) |

---

## 🧪 8. Ejecución de Pruebas Automatizadas

El proyecto incluye una suite de pruebas CLI que valida 30 aserciones críticas de seguridad, roles, consultas analíticas, integridad de caracteres especiales UTF-8 y base de datos:

Desde PowerShell o CMD ejecuta:
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\Proyecto_Ecopoints\tests\verify.php
```

Resultado esperado:
```text
RESULTADOS DE LAS PRUEBAS: 30 / 30 PASADAS CON ÉXITO
ESTADO: 🎉 TODAS LAS PRUEBAS DE LA FASE 1 CUMPLIDAS SATISFACTORIAMENTE
```

---

## 📈 9. Preparación para el Futuro Dashboard Administrativo de Datos

Toda la base de datos está estructurada y poblada para alimentar directamente el futuro **Panel Administrativo**, permitiendo visualizar en tiempo real:

1. **KPIs Globales del Ecosistema:**
   * **Total de Material Recuperado:** Suma de 6.377 entregas oficiales validadas.
   * **Total de Ecopoints Otorgados:** Puntos acumulados por los recicladores.
   * **Total de Beneficios Redimidos:** 1.380 canjes registrados y trazables.
   * **Padrón Activo de Recicladores:** 60 recicladores caracterizados con sus zonas de trabajo y medios de transporte en las 20 localidades de Bogotá D.C.
2. **Distribución y Métricas Analíticas:**
   * **Comportamiento por Material:** Participación porcentual de Cartón, Plástico, Vidrio, Metales y Papel.
   * **Desempeño Temporal Mensual:** Vía la vista nativa `vw_diccionario_ecopoints_mensual` (con granularidad mes-reciclador).
   * **Variables para Futura Minería/Predicción:** Base lista con variables explicativas (`kg_recolectados`, `entregas`, `diversidad_materiales`, `beneficios_redimidos`, `puntos_obtenidos`) y la variable objetivo (`puntos_mes_siguiente`).

