/**
 * ============================================================================
 * JAVASCRIPT: DASHBOARD ADMINISTRATIVO Y MINERÍA DE DATOS ECOPOINTS
 * ============================================================================
 * Maneja la interacción en tiempo real del nuevo dashboard administrativo:
 * 1. Buscador y selector universal de recicladores con debounce.
 * 2. Carga reactiva de la ficha individual, métricas e historiales.
 * 3. Renderizado interactivo de los 4 modelos de Machine Learning.
 * 4. Filtrado dinámico de entregas por fecha y material.
 * 5. Re-ejecución forzada de inferencia vía AJAX con token CSRF.
 */

document.addEventListener('DOMContentLoaded', () => {
    const config = window.ecopointsAdminConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl = config.baseUrl || '';

    // Elementos del DOM
    const inputBuscador = document.getElementById('buscador-reciclador');
    const dropdownResultados = document.getElementById('dropdown-resultados');
    const chipsRapidos = document.querySelectorAll('.chip-reciclador-rapido');
    const contenedorFicha = document.getElementById('contenedor-ficha-reciclador');
    const loadingFicha = document.getElementById('loading-ficha');
    const btnActualizarAnalisis = document.getElementById('btn-actualizar-analisis');
    const filterFechaDesde = document.getElementById('filter-fecha-desde');
    const filterFechaHasta = document.getElementById('filter-fecha-hasta');
    const filterMaterial = document.getElementById('filter-material');
    const tablaEntregasBody = document.getElementById('tabla-entregas-body');
    const contadorEntregas = document.getElementById('contador-entregas-filtradas');

    let recicladorActualId = config.primerRecicladorId || 0;
    let cacheDetalleReciclador = null;
    let timerBusqueda = null;

    // Helper de formato numérico colombiano
    const numFmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 2 });
    const intFmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

    /**
     * Carga y renderiza la ficha individual de un reciclador
     */
    async function cargarFichaReciclador(idReciclador) {
        if (!idReciclador) return;
        recicladorActualId = idReciclador;

        // Mostrar spinner de carga y ocultar ficha previa
        if (loadingFicha) loadingFicha.style.display = 'flex';
        if (contenedorFicha) contenedorFicha.style.opacity = '0.35';

        try {
            const resp = await fetch(`${baseUrl}admin/reciclador/detalle?id=${idReciclador}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            if (!data.success) {
                mostrarNotificacion(data.error || 'No se pudo cargar el reciclador.', 'error');
                return;
            }

            cacheDetalleReciclador = data;
            renderizarFichaCompleta(data);

            // Resaltar chip activo en la lista superior si existe
            document.querySelectorAll('.chip-reciclador-rapido').forEach(chip => {
                const id = parseInt(chip.getAttribute('data-id'), 10);
                chip.classList.toggle('active', id === idReciclador);
            });

        } catch (err) {
            console.error('Error al cargar detalle del reciclador:', err);
            mostrarNotificacion('Error de conexión al obtener la ficha del reciclador.', 'error');
        } finally {
            if (loadingFicha) loadingFicha.style.display = 'none';
            if (contenedorFicha) contenedorFicha.style.opacity = '1';
        }
    }

    /**
     * Renderiza todos los bloques de la ficha individual
     */
    function renderizarFichaCompleta(data) {
        const p = data.perfil || {};
        const m = data.metricas || {};
        const ml = data.analisisML || {};

        // 1. Cabecera y datos personales
        setText('rec-nombre', p.nombre_completo || 'Sin nombre');
        setText('rec-id-badge', `#${p.id_usuario}`);
        setText('rec-correo', p.correo || 'Sin correo');
        setText('rec-telefono', p.telefono || 'Sin teléfono');
        setText('rec-localidad', p.localidad || 'Sin localidad');
        setText('rec-zona', p.zona_trabajo || 'Sin zona asignada');
        setText('rec-transporte', p.medio_transporte || 'No registrado');
        setText('rec-registro', p.fecha_registro ? p.fecha_registro.substring(0, 10) : '—');
        setText('rec-nivel-badge', p.nivel || 'Bronce');
        
        const badgeEstado = document.getElementById('rec-estado-badge');
        if (badgeEstado) {
            badgeEstado.textContent = p.estado === 'activo' ? 'Cuenta Activa' : 'Inactivo';
            badgeEstado.className = `badge ${p.estado === 'activo' ? 'badge-success' : 'badge-danger'}`;
        }

        // Avatar con iniciales
        const avatarEl = document.getElementById('rec-avatar-initials');
        if (avatarEl) {
            const iniciales = (p.nombre ? p.nombre.charAt(0) : '') + (p.apellido ? p.apellido.charAt(0) : '');
            avatarEl.textContent = iniciales.toUpperCase() || 'R';
        }

        // 2. Mini-KPIs del reciclador
        setText('kpi-rec-kg', numFmt.format(m.total_kg || 0) + ' kg');
        setText('kpi-rec-entregas', intFmt.format(m.total_entregas || 0));
        setText('kpi-rec-puntos', intFmt.format(m.ecopuntos_activos || 0) + ' pts');
        setText('kpi-rec-canjes', intFmt.format(m.total_canjes || 0));

        // 3. Manejo de estado: ¿Tiene entregas o es nuevo sin actividad?
        const bannerSinActividad = document.getElementById('banner-sin-actividad');
        const bloqueAnalitica = document.getElementById('bloque-analitica-ml');
        const bloqueMateriales = document.getElementById('bloque-materiales-reciclador');

        if (!m.tiene_actividad) {
            if (bannerSinActividad) bannerSinActividad.style.display = 'block';
            if (bloqueAnalitica) bloqueAnalitica.style.display = 'none';
            if (bloqueMateriales) bloqueMateriales.style.display = 'none';
        } else {
            if (bannerSinActividad) bannerSinActividad.style.display = 'none';
            if (bloqueAnalitica) bloqueAnalitica.style.display = 'block';
            if (bloqueMateriales) bloqueMateriales.style.display = 'block';
            renderizarMaterialesReciclador(data.materiales || []);
            renderizarModelosML(ml);
        }

        // 4. Historial de entregas y canjes
        renderizarHistorialEntregas(data.historialEntregas || []);
        renderizarHistorialCanjes(data.historialCanjes || []);
    }

    /**
     * Renderiza el desglose de materiales del reciclador
     */
    function renderizarMaterialesReciclador(materiales) {
        const cont = document.getElementById('lista-materiales-reciclador');
        if (!cont) return;
        cont.replaceChildren();

        if (materiales.length === 0) {
            cont.innerHTML = '<p class="text-muted" style="font-size:0.85rem;">No hay registros de materiales entregados.</p>';
            return;
        }

        const coloresMap = {
            'Cartón': '#F59E0B',
            'Papel': '#06B6D4',
            'Plástico': '#3B82F6',
            'Vidrio': '#10B981',
            'Metales': '#8B5CF6'
        };

        materiales.forEach(mat => {
            const color = coloresMap[mat.material] || '#087A3D';
            const row = document.createElement('div');
            row.style.marginBottom = '12px';
            row.innerHTML = `
                <div style="display:flex; justify-content:space-between; font-size:0.86rem; margin-bottom:4px;">
                    <span style="font-weight:600; color:var(--text);">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${color}; margin-right:6px;"></span>
                        ${escapeHTML(mat.material)}
                    </span>
                    <span style="color:var(--muted); font-size:0.82rem;">
                        <strong>${numFmt.format(mat.kg_material)} kg</strong> (${mat.porcentaje}%)
                    </span>
                </div>
                <div style="width:100%; height:7px; background:#EAEAEA; border-radius:4px; overflow:hidden;">
                    <div style="width:${mat.porcentaje}%; height:100%; background:${color}; border-radius:4px; transition:width 0.4s ease;"></div>
                </div>
            `;
            cont.appendChild(row);
        });
    }

    /**
     * Renderiza los 4 Modelos de Machine Learning
     */
    function renderizarModelosML(ml) {
        if (!ml || ml.estado === 'sin_actividad' || ml.estado === 'error') {
            return;
        }

        // 1. Regresión (RandomForestRegressor)
        const reg = ml.regresion || {};
        const puntosEst = reg.puntos_estimados !== undefined ? numFmt.format(reg.puntos_estimados) : '—';
        setText('ml-regresion-puntos', `${puntosEst} pts`);
        setText('ml-regresion-algo', reg.algoritmo || 'RandomForestRegressor');
        
        // 2. Clasificación (LogisticRegression)
        const clf = ml.clasificacion || {};
        const nivel = clf.nivel_actividad || 'Media';
        setText('ml-clasificacion-nivel', nivel);
        
        const badgeClf = document.getElementById('ml-clasificacion-badge');
        if (badgeClf) {
            badgeClf.textContent = `Nivel: ${nivel}`;
            badgeClf.className = `badge ${nivel === 'Alta' ? 'badge-success' : (nivel === 'Media' ? 'badge-warning' : 'badge-info')}`;
        }

        const probs = clf.probabilidades || {};
        setBarWidth('bar-prob-baja', probs.Baja || 0);
        setText('txt-prob-baja', `${probs.Baja || 0}%`);
        setBarWidth('bar-prob-media', probs.Media || 0);
        setText('txt-prob-media', `${probs.Media || 0}%`);
        setBarWidth('bar-prob-alta', probs.Alta || 0);
        setText('txt-prob-alta', `${probs.Alta || 0}%`);

        // 3. Segmentación K-Means
        const km = ml.segmentacion || {};
        setText('ml-kmeans-segmento', km.segmento || 'No asignado');
        setText('ml-kmeans-cluster-id', `Clúster #${km.cluster_id !== undefined ? km.cluster_id : '—'}`);
        
        let descSegmento = 'Perfil de recolección constante.';
        if (km.segmento && km.segmento.includes('Líderes')) {
            descSegmento = 'Reciclador de alto volumen y frecuencia constante en centros de acopio.';
        } else if (km.segmento && km.segmento.includes('Ocasionales')) {
            descSegmento = 'Entregas esporádicas o de menor volumen promedio. Requiere incentivos de canje.';
        } else {
            descSegmento = 'Actividad sostenida con entregas intermedias y diversidad balanceada.';
        }
        setText('ml-kmeans-desc', descSegmento);

        // 4. Detección de Anomalías (Isolation Forest)
        const iso = ml.anomalias || {};
        const esAnomalo = !!iso.es_anomalo;
        setText('ml-iso-estado', iso.estado_operativo || (esAnomalo ? 'Atípico' : 'Operación Normal'));
        setText('ml-iso-score', `Score de Aislamiento: ${iso.score_aislamiento !== undefined ? iso.score_aislamiento : '—'}`);
        
        const badgeIso = document.getElementById('ml-iso-badge');
        if (badgeIso) {
            badgeIso.textContent = esAnomalo ? 'Revisión Sugerida' : 'Normal';
            badgeIso.className = `badge ${esAnomalo ? 'badge-danger' : 'badge-success'}`;
        }

        // Metadatos de la corrida
        setText('ml-meta-fecha', ml.fecha_analisis ? ml.fecha_analisis : 'Reciente');
        setText('ml-meta-periodo', ml.periodo_analizado ? `Periodo evaluado: ${ml.periodo_analizado}` : '');
        setText('ml-meta-origen', ml.origen === 'cache' ? 'Desde caché analítico auditado' : 'Calculado en tiempo real con Python');

        // Recomendaciones
        const listaRecs = document.getElementById('ml-recomendaciones-lista');
        if (listaRecs) {
            listaRecs.replaceChildren();
            const recs = ml.recomendaciones || [];
            if (recs.length === 0) {
                listaRecs.innerHTML = '<li>Sin observaciones operativas adicionales.</li>';
            } else {
                recs.forEach(r => {
                    const li = document.createElement('li');
                    li.textContent = r;
                    listaRecs.appendChild(li);
                });
            }
        }
    }

    /**
     * Renderiza el historial de entregas con filtrado
     */
    function renderizarHistorialEntregas(entregas) {
        if (!tablaEntregasBody) return;
        tablaEntregasBody.replaceChildren();

        const desde = filterFechaDesde ? filterFechaDesde.value : '';
        const hasta = filterFechaHasta ? filterFechaHasta.value : '';
        const material = filterMaterial ? filterMaterial.value : '';

        const filtradas = entregas.filter(item => {
            if (desde && item.fecha_entrega.substring(0, 10) < desde) return false;
            if (hasta && item.fecha_entrega.substring(0, 10) > hasta) return false;
            if (material && item.material !== material) return false;
            return true;
        });

        if (contadorEntregas) {
            contadorEntregas.textContent = `${filtradas.length} entrega(s) encontrada(s)`;
        }

        if (filtradas.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="6" style="text-align:center; padding:24px; color:var(--muted);">No se encontraron entregas que coincidan con los filtros aplicados.</td>`;
            tablaEntregasBody.appendChild(tr);
            return;
        }

        filtradas.forEach(e => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border)';
            tr.innerHTML = `
                <td style="padding:10px 12px; font-weight:600; color:var(--text);">${escapeHTML(e.fecha_entrega)}</td>
                <td style="padding:10px 12px;">
                    <span class="badge" style="background:#E8F5E9; color:#1B5E20; font-weight:600;">
                        ${escapeHTML(e.material)}
                    </span>
                </td>
                <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--text);">${numFmt.format(e.cantidad_validada)} kg</td>
                <td style="padding:10px 12px; text-align:right; font-weight:700; color:#087A3D;">+${intFmt.format(e.ecopuntos_otorgados)} pts</td>
                <td style="padding:10px 12px; color:var(--muted);">${escapeHTML(e.centro_acopio || 'Centro Acopio')}</td>
                <td style="padding:10px 12px; text-align:center;">
                    <span class="badge ${e.estado === 'Validada' ? 'badge-success' : 'badge-warning'}">${escapeHTML(e.estado)}</span>
                </td>
            `;
            tablaEntregasBody.appendChild(tr);
        });
    }

    /**
     * Renderiza el historial de canjes
     */
    function renderizarHistorialCanjes(canjes) {
        const body = document.getElementById('tabla-canjes-body');
        if (!body) return;
        body.replaceChildren();

        if (canjes.length === 0) {
            body.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">Sin canjes de beneficios registrados para este usuario.</td></tr>`;
            return;
        }

        canjes.forEach(c => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border)';
            tr.innerHTML = `
                <td style="padding:10px 12px; font-weight:600;">${escapeHTML(c.fecha_canje)}</td>
                <td style="padding:10px 12px; font-weight:600; color:var(--text);">${escapeHTML(c.beneficio)}</td>
                <td style="padding:10px 12px; color:var(--muted);">${escapeHTML(c.categoria || 'General')}</td>
                <td style="padding:10px 12px; text-align:right; font-weight:700; color:#E65100;">-${intFmt.format(c.ecopuntos_utilizados)} pts</td>
                <td style="padding:10px 12px; text-align:center;">
                    <span class="badge ${c.estado === 'reclamado' ? 'badge-success' : 'badge-warning'}">${escapeHTML(c.estado)}</span>
                </td>
            `;
            body.appendChild(tr);
        });
    }

    /**
     * Buscador con autocompletado en tiempo real
     */
    if (inputBuscador && dropdownResultados) {
        inputBuscador.addEventListener('input', (e) => {
            const termino = e.target.value.trim();
            clearTimeout(timerBusqueda);

            if (termino.length < 1) {
                dropdownResultados.style.display = 'none';
                return;
            }

            timerBusqueda = setTimeout(async () => {
                try {
                    const resp = await fetch(`${baseUrl}admin/recicladores/buscar?q=${encodeURIComponent(termino)}`);
                    const data = await resp.json();

                    if (!data.success || !data.recicladores || data.recicladores.length === 0) {
                        dropdownResultados.innerHTML = `<div style="padding:12px; color:var(--muted); font-size:0.85rem; text-align:center;">No se encontraron recicladores con: "<strong>${escapeHTML(termino)}</strong>"</div>`;
                        dropdownResultados.style.display = 'block';
                        return;
                    }

                    dropdownResultados.replaceChildren();
                    data.recicladores.forEach(r => {
                        const item = document.createElement('div');
                        item.className = 'resultado-item-buscador';
                        item.style.padding = '10px 14px';
                        item.style.cursor = 'pointer';
                        item.style.borderBottom = '1px solid #F0F0F0';
                        item.style.display = 'flex';
                        item.style.justifyContent = 'space-between';
                        item.style.alignItems = 'center';
                        item.innerHTML = `
                            <div>
                                <div style="font-weight:700; font-size:0.88rem; color:var(--text);">
                                    #${r.id_usuario} — ${escapeHTML(r.nombre_completo)}
                                </div>
                                <div style="font-size:0.78rem; color:var(--muted);">
                                    ${escapeHTML(r.correo || '')} · 📍 ${escapeHTML(r.localidad || 'Bogotá')}
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge badge-success" style="font-size:0.75rem;">${intFmt.format(r.ecopuntos)} pts</span>
                            </div>
                        `;

                        item.addEventListener('click', () => {
                            dropdownResultados.style.display = 'none';
                            inputBuscador.value = `#${r.id_usuario} — ${r.nombre_completo}`;
                            cargarFichaReciclador(r.id_usuario);
                        });

                        item.addEventListener('mouseenter', () => item.style.background = '#F0FDF4');
                        item.addEventListener('mouseleave', () => item.style.background = '#FFFFFF');

                        dropdownResultados.appendChild(item);
                    });

                    dropdownResultados.style.display = 'block';

                } catch (err) {
                    console.error('Error al buscar recicladores:', err);
                }
            }, 250);
        });

        // Ocultar dropdown al hacer click afuera
        document.addEventListener('click', (e) => {
            if (!inputBuscador.contains(e.target) && !dropdownResultados.contains(e.target)) {
                dropdownResultados.style.display = 'none';
            }
        });
    }

    // Selección rápida desde chips
    chipsRapidos.forEach(chip => {
        chip.addEventListener('click', () => {
            const id = parseInt(chip.getAttribute('data-id'), 10);
            if (id) {
                if (inputBuscador) {
                    inputBuscador.value = chip.textContent.trim();
                }
                cargarFichaReciclador(id);
            }
        });
    });

    // Filtros de tabla de entregas
    if (filterFechaDesde) filterFechaDesde.addEventListener('change', () => {
        if (cacheDetalleReciclador) renderizarHistorialEntregas(cacheDetalleReciclador.historialEntregas || []);
    });
    if (filterFechaHasta) filterFechaHasta.addEventListener('change', () => {
        if (cacheDetalleReciclador) renderizarHistorialEntregas(cacheDetalleReciclador.historialEntregas || []);
    });
    if (filterMaterial) filterMaterial.addEventListener('change', () => {
        if (cacheDetalleReciclador) renderizarHistorialEntregas(cacheDetalleReciclador.historialEntregas || []);
    });

    // Botón de re-análisis forzado de modelos ML
    if (btnActualizarAnalisis) {
        btnActualizarAnalisis.addEventListener('click', async () => {
            if (!recicladorActualId) {
                mostrarNotificacion('Selecciona un reciclador primero.', 'warning');
                return;
            }

            btnActualizarAnalisis.disabled = true;
            btnActualizarAnalisis.innerHTML = '<span class="spinner-sm"></span> Ejecutando Python…';

            try {
                const resp = await fetch(`${baseUrl}admin/reciclador/analisis/actualizar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ id_reciclador: recicladorActualId })
                });
                const data = await resp.json();

                if (!data.success) {
                    mostrarNotificacion(data.error || 'Fallo en la inferencia.', 'error');
                    return;
                }

                mostrarNotificacion('Modelos de minería ejecutados exitosamente.', 'success');
                // Recargar la ficha completa
                await cargarFichaReciclador(recicladorActualId);

            } catch (err) {
                console.error('Error al forzar análisis ML:', err);
                mostrarNotificacion('Error de conexión con el motor de minería.', 'error');
            } finally {
                btnActualizarAnalisis.disabled = false;
                btnActualizarAnalisis.innerHTML = '⚡ Re-ejecutar Modelos ML';
            }
        });
    }

    // Tabs para alternar entre Entregas y Canjes
    const tabEntregas = document.getElementById('tab-btn-entregas');
    const tabCanjes = document.getElementById('tab-btn-canjes');
    const panelEntregas = document.getElementById('panel-tabla-entregas');
    const panelCanjes = document.getElementById('panel-tabla-canjes');

    if (tabEntregas && tabCanjes && panelEntregas && panelCanjes) {
        tabEntregas.addEventListener('click', () => {
            tabEntregas.classList.add('btn-p');
            tabEntregas.classList.remove('btn-o');
            tabCanjes.classList.add('btn-o');
            tabCanjes.classList.remove('btn-p');
            panelEntregas.style.display = 'block';
            panelCanjes.style.display = 'none';
        });

        tabCanjes.addEventListener('click', () => {
            tabCanjes.classList.add('btn-p');
            tabCanjes.classList.remove('btn-o');
            tabEntregas.classList.add('btn-o');
            tabEntregas.classList.remove('btn-p');
            panelCanjes.style.display = 'block';
            panelEntregas.style.display = 'none';
        });
    }

    // Funciones auxiliares
    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function setBarWidth(id, pct) {
        const el = document.getElementById(id);
        if (el) el.style.width = `${Math.min(100, Math.max(0, pct))}%`;
    }

    function escapeHTML(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function mostrarNotificacion(mensaje, tipo = 'info') {
        const toast = document.createElement('div');
        toast.className = `dashboard-toast toast-${tipo}`;
        toast.style.position = 'fixed';
        toast.style.bottom = '24px';
        toast.style.right = '24px';
        toast.style.zIndex = '9999';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '10px';
        toast.style.color = '#fff';
        toast.style.fontWeight = '600';
        toast.style.boxShadow = '0 6px 20px rgba(0,0,0,0.18)';
        toast.style.fontSize = '0.88rem';
        toast.style.transition = 'all 0.3s ease';

        if (tipo === 'success') toast.style.background = '#087A3D';
        else if (tipo === 'error') toast.style.background = '#D32F2F';
        else if (tipo === 'warning') toast.style.background = '#F57C00';
        else toast.style.background = '#1976D2';

        toast.textContent = mensaje;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Iniciar cargando el primer reciclador por defecto si está configurado
    if (recicladorActualId) {
        cargarFichaReciclador(recicladorActualId);
    }
});
