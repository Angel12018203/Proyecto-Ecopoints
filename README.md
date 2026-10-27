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
│   │   ├── Model.php              # Clase base PDO para todos los modelos
│   │   ├── CentroAcopio.php       # Consultas y registro de centros de acopio
│   │   ├── Ciudadano.php          # Métricas, perfil ciudadano y vínculos
│   │   ├── Localidad.php          # Catálogo oficial de 20 localidades de Bogotá D.C.
│   │   ├── Material.php           # Catálogo de materiales reciclables y puntos/kg
│   │   ├── Reciclador.php         # Transacciones, perfil, entregas y Ecopoints
│   │   ├── Solicitud.php          # Gestión y consulta segura de solicitudes (Fase 2)
│   │   └── Usuario.php            # Autenticación segura, BCRYPT y registro multi-rol
│   │
│   ├── controllers/
│   │   ├── AuthController.php         # Login, Registro Multi-Rol y Logout seguro
│   │   ├── CentroAcopioController.php # Dashboard y recepción para centros de acopio
│   │   ├── CiudadanoController.php    # Dashboard con métricas para ciudadanos
│   │   ├── RecicladorController.php   # Dashboard principal de alta fidelidad Figma
│   │   └── SolicitudController.php    # Creación y consulta de solicitudes (Fase 2)
│   │
│   └── views/
│       ├── auth/
│       │   ├── login.php          # Login dividido Figma con logo contrapuesto y responsivo
│       │   └── registro.php       # Registro interactivo multi-rol con sanitización estricta
│       ├── ciudadano/
│       │   ├── dashboard.php      # Vista para ciudadanos con KPIs reales
│       │   ├── crear_solicitud.php# Formulario de alta fidelidad para registrar recolección
│       │   └── solicitudes.php    # Listado visual de solicitudes con badges de estado
│       ├── centro_acopio/
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
│   ├── 01_fase2_solicitudes.sql                   # Migración SQL: Estado 'Pendiente' e índices
│   ├── ecopoint.sql                               # Script maestro: Estructura + Semillas
│   ├── BASE_ORIGINAL_Ecopoints_Mineria_Datos.csv  # Base cruda con problemas de calidad
│   └── BASE_PREPARADA_Ecopoints_Mineria_Datos.csv # Base limpia y depurada para minería
│
├── docs/
│   └── PRUEBAS_FUNCIONALES.md                     # Matriz de 42 pruebas funcionales auditadas
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

## 🧪 8. Ejecución de Pruebas Automatizadas (Fases 1 y 2)

El proyecto incluye una suite integral de pruebas CLI que valida **42 aserciones críticas** de seguridad, sanitización estricta, registro multi-rol, control de sesiones RBAC, y el ciclo de vida completo de la **Fase 2 (Solicitudes de Recolección)**:

Desde PowerShell o CMD ejecuta:
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\Proyecto_Ecopoints\tests\verify.php
```

Resultado obtenido en la ejecución real:
```text
====================================================================
   INICIANDO BATERÍA DE PRUEBAS AUTOMATIZADAS — ECOPOINTS (FASES 1 Y 2)
====================================================================

── [1] Base de Datos y Catálogos Maestros ──
  [PASS] Conexión PDO establecida con éxito a MySQL/MariaDB
  [PASS] El catálogo contiene exactamente 20 localidades oficiales de Bogotá
  [PASS] La localidad #11 es 'Suba'
  [PASS] El catálogo de materiales contiene al menos 5 categorías activas
  [PASS] Catálogo incluye 'Cartón', 'Plástico', 'Vidrio' y 'Metales'

── [2] Autenticación y Verificación Criptográfica ──
  [PASS] Usuario semilla 'reciclador@ecopoint.app' existe en la BD
  [PASS] El rol de Carlos es 'reciclador' (id_rol = 1)
  [PASS] Contraseña correcta 'Carlos123*' autentica con BCRYPT
  [PASS] Contraseña errónea 'PasswordErroneo' es rechazada
  [PASS] Generación de token CSRF (longitud 64 caracteres hex)
  [PASS] verify_csrf valida token legítimo con hash_equals
  [PASS] verify_csrf rechaza token falso o manipulado
  [PASS] Helper h() neutraliza etiquetas script peligrosas

── [3] Sanitización de Campos (Letras vs Números) ──
  [PASS] Sanitización rechaza nombre que contiene números ('Carlos123')
  [PASS] Sanitización acepta nombre alfabético con tilde y espacio ('María José')
  [PASS] Sanitización rechaza apellido que contiene números ('Perez99')
  [PASS] Sanitización rechaza teléfono que contiene letras ('311ABCD555')
  [PASS] Sanitización acepta teléfono en formato numérico válido ('311 555 4321')

── [4] Registro y Perfiles Multi-Rol ──
  [PASS] El modelo rechaza intento de registro con correo duplicado
  [PASS] Registro de Ciudadano creado con perfil y localidad vinculada (#2 Chapinero)

── [5] Sesiones y Control de Acceso por Roles (RBAC) ──
  [PASS] loginUser() establece usuario autenticado en sesión
  [PASS] currentUser() retorna datos de Carlos
  [PASS] hasRole('reciclador') retorna true para cuenta de reciclador
  [PASS] hasRole('ciudadano') retorna false para cuenta de reciclador
  [PASS] logoutUser() destruye la sesión y limpia credenciales

── [6] Fase 2: Solicitudes de Recolección (Registro y Persistencia) ──
  [PASS] Modelo Solicitud rechaza cantidad igual o menor a cero (0 kg)
  [PASS] Solicitud válida creada con éxito en MySQL (ID: #6408)
  [PASS] La solicitud persiste en MySQL y está vinculada a Chapinero
  [PASS] El material asociado es 'Cartón'
  [PASS] El estado inicial de la solicitud es 'Pendiente'
  [PASS] La cantidad estimada almacenada es 12.50 kg
  [PASS] La prioridad asignada es 'Normal'
  [PASS] getByCiudadano() retorna las solicitudes del ciudadano autenticado
  [PASS] La solicitud #6408 figura en la lista del ciudadano
  [PASS] Aislamiento seguro: findByIdAndCiudadano() rechaza acceso a usuario no propietario
  [PASS] Acceso autorizado: findByIdAndCiudadano() permite consulta al propietario legítimo
  [PASS] Métricas del ciudadano contabilizan al menos 1 solicitud pendiente
  [PASS] Métricas acumulan el peso estimado correctamente

── [7] Pruebas de Regresión de Vistas y Paneles ──
  [PASS] CiudadanoController->dashboard() renderiza panel con métricas y enlaces reales
  [PASS] SolicitudController->crear() renderiza formulario con catálogo de materiales y CSRF
  [PASS] SolicitudController->misSolicitudes() renderiza listado con la solicitud #6408

── [8] Limpieza de Registros Temporales ──
  [PASS] Limpieza de solicitudes y usuarios de prueba completada sin alterar registros reales

--------------------------------------------------------------------
RESULTADOS DE LAS PRUEBAS: 42 / 42 PASADAS CON ÉXITO
ESTADO: 🎉 TODAS LAS PRUEBAS DE LAS FASES 1 Y 2 CUMPLIDAS SATISFACTORIAMENTE
====================================================================
```

Para consultar la matriz completa de casos y el informe de auditoría, revisa [docs/PRUEBAS_FUNCIONALES.md](file:///c:/xampp/htdocs/Proyecto_Ecopoints/docs/PRUEBAS_FUNCIONALES.md).

---

## 📈 9. Dashboard Administrativo y Métricas Actuales

El panel administrativo ya consume las tablas operacionales y `vw_diccionario_ecopoints_mensual`. En la base local verificada:

1. **KPIs Globales del Ecosistema:**
   * **Entregas validadas, solicitudes, recolecciones y transacciones de Ecopoints:** 6.407 de cada tipo.
   * **Beneficios redimidos:** 1.380 canjes registrados y trazables.
   * **Padrón activo:** 65 recicladores, incluidos los cinco perfiles sintéticos identificados y descritos en la sección 10.
2. **Distribución y Métricas Analíticas:**
   * **Comportamiento por Material:** Participación porcentual de Cartón, Plástico, Vidrio, Metales y Papel.
   * **Desempeño mensual:** 656 filas entre enero y noviembre de 2025 en `vw_diccionario_ecopoints_mensual`.
   * **Variables analíticas:** `kg_recolectados`, `entregas`, `diversidad_materiales`, `beneficios_redimidos`, `puntos_obtenidos` y `puntos_mes_siguiente`.

Estos totales corresponden a la base activa local. El script inicial `database/ecopoint.sql` conserva el conjunto base; las seis cuentas sintéticas y sus transacciones adicionales se describen en la sección 10.

## 10. Integración backend con el servicio de analítica

El backend administrativo actúa como intermediario entre la sesión PHP y el servicio FastAPI local. No hay llamadas directas desde el navegador a FastAPI ni escrituras en MySQL. El análisis por reciclador, solicitado bajo demanda desde el Top, consulta en modo lectura la vista mensual existente para preparar las entradas de los modelos.

- El servicio cliente está en `app/services/AnaliticaEcopointsService.php` y el controlador protegido en `app/controllers/AnaliticaController.php`.
- Las rutas `admin/analitica/predecir-puntos`, `clasificar-nivel`, `segmentar-reciclador`, `evaluar-anomalia` y `analizar-reciclador` reciben `POST` con JSON y token CSRF en `X-CSRF-Token`.
- `admin/analitica/reglas-asociacion` y `admin/analitica/graficas-modelos` reciben `GET`; los filtros permitidos para reglas son `antecedente`, `consecuente` y `limite`.
- Todas las rutas requieren la sesión y el rol administrador existentes. El backend limita las solicitudes, impone tiempos de espera y devuelve errores genéricos sin trazas de FastAPI.
- FastAPI debe estar disponible en `127.0.0.1:8001`. Configura `ECOPOINTS_API_TOKEN` para el proceso Apache con el mismo valor usado al iniciar FastAPI; no lo guardes en el código, Git ni JavaScript. Por ejemplo, configura `SetEnv ECOPOINTS_API_TOKEN "valor-local-compartido"` en la configuración local de Apache y reinicia Apache.

El puente backend no modifica registros ni el esquema. Las pruebas de `tests/verify.php` no deben usarse para esta integración porque crean y eliminan registros operacionales.

### Herramientas analíticas del administrador

El panel administrativo presenta formularios para estimación de Ecopoints, clasificación de actividad, segmentación histórica, evaluación de anomalías y consulta de reglas. Además, el Top conserva su orden operativo por Ecopoints acumulados y permite analizar a cada reciclador bajo demanda: clasificación y regresión usan su último periodo observado; segmentación usa los promedios de los periodos disponibles. La distribución conserva cantidades reales de entregas validadas, kg y porcentaje del peso. La tabla mensual muestra targets disponibles junto al periodo realmente observado. Ninguna consulta modifica datos. La predicción conserva su advertencia temporal, las anomalías se presentan solo como casos para revisión y las reglas aclaran que no se recalculan contra la base operacional.

#### Cinco visualizaciones durante la ejecución

Al abrir el panel, `graficas-modelos` obtiene una muestra de hasta 10 recicladores con target disponible y solicita inferencias a los cuatro modelos `.joblib`. Se muestran: (1) regresión, estimación frente a `puntos_mes_siguiente` observado; (2) distribución de niveles de clasificación; (3) distribución de perfiles de segmentación; (4) registros normales/atípicos de Isolation Forest, solo para revisión humana; y (5) reglas Apriori con soporte, confianza y lift del CSV exportado. Las gráficas usan SVG y JavaScript local, sin CDN. El CSV de Apriori no se recalcula con las transacciones nuevas.

El análisis individual incluye recomendaciones descriptivas que comparan las entregas, kg y diversidad del último periodo con el historial del mismo perfil, además de mostrar la clasificación, segmento y señal del detector. No cambian puntos, permisos ni beneficios. La regresión conserva la salvedad de que el target significa siguiente registro observado, no necesariamente el siguiente mes calendario.

#### Datos sintéticos de prueba en la base activa

La base activa incorpora cinco recicladores de prueba (**IDs 143–147**) y un ciudadano sintético compartido (**ID 142**) para mantener la relación de solicitudes. Cada reciclador tiene dos entregas mensuales en **2025-09, 2025-10 y 2025-11**: 30 solicitudes, recolecciones, entregas validadas y acreditaciones de Ecopoints, agrupadas en 15 filas mensuales. Los registros llevan el marcador `SINTETICO_ECOPOINTS_PRUEBA_2025`; los correos usan el dominio reservado `.invalid` y las contraseñas se generaron aleatoriamente, por lo que no son cuentas para iniciar sesión. No se agregaron canjes ni se modificaron cuentas existentes o el esquema. El archivo `database/ecopoint.sql` conserva la carga inicial y no contiene estos datos adicionales.

Como son perfiles de bajo volumen y solo tres periodos, el detector puede marcarlos para revisión debido a su diferencia frente al conjunto de entrenamiento; eso no es una conclusión de fraude. La interfaz solo lo comunica como revisión humana. En este equipo, `ECOPOINTS_API_TOKEN` quedó configurado localmente en `C:\xampp\apache\conf\extra\httpd-xampp.conf`; el valor se conserva fuera del repositorio con permisos restringidos. FastAPI escucha en `127.0.0.1:8001`. Tras reiniciar el equipo, vuelve a iniciar FastAPI con el mismo token guardado en `%LOCALAPPDATA%\EcoPoints\analytics-token`; nunca copies el valor a este README, Git o el navegador.
