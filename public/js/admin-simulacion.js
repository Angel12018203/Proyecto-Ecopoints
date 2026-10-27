/**
 * ============================================================================
 * JAVASCRIPT: MODELADO Y SIMULACIÓN OPERATIVA ECOPOINTS (SimPy + Monte Carlo)
 * ============================================================================
 * Gestiona la interacción en tiempo real del módulo de simulación:
 * 1. Selección y precarga de los 3 escenarios operativos (A, B, C).
 * 2. Ejecución asíncrona de SimPy + Monte Carlo mediante subproceso seguro Python.
 * 3. Renderizado reactivo de KPIs, percentiles y gráficos de colas.
 * 4. Diagnóstico dinámico de cuellos de botella y contraste con Machine Learning.
 * 5. Descarga de resultados a CSV.
 */

document.addEventListener('DOMContentLoaded', () => {
    const config = window.ecopointsSimConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl = config.baseUrl || '';

    // Elementos del formulario y controles
    const selectEscenario = document.getElementById('sim-escenario');
    const inputSolicitudes = document.getElementById('sim-solicitudes');
    const inputRecicladores = document.getElementById('sim-recicladores');
    const inputPuestos = document.getElementById('sim-puestos');
    const inputDuracion = document.getElementById('sim-duracion');
    const inputReplicas = document.getElementById('sim-replicas');
    const inputSemilla = document.getElementById('sim-semilla');
    const btnEjecutar = document.getElementById('btn-ejecutar-simulacion');
    const btnExportar = document.getElementById('btn-exportar-csv');
    const bannerEstado = document.getElementById('sim-status-banner');
    const spinnerEjecucion = document.getElementById('sim-spinner');

    // Preconfiguración de los 3 escenarios
    const presetsEscenarios = {
        'A': { solicitudes: 40, recicladores: 30, puestos: 2, duracion: 7, replicas: 10, semilla: 42 },
        'B': { solicitudes: 70, recicladores: 30, puestos: 2, duracion: 7, replicas: 10, semilla: 42 },
        'C': { solicitudes: 70, recicladores: 45, puestos: 4, duracion: 7, replicas: 10, semilla: 42 }
    };

    // Formateadores numéricos
    const numFmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 1 });
    const intFmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

    // Cambio de escenario predefinido
    if (selectEscenario) {
        selectEscenario.addEventListener('change', () => {
            const val = selectEscenario.value;
            if (presetsEscenarios[val]) {
                const p = presetsEscenarios[val];
                if (inputSolicitudes) inputSolicitudes.value = p.solicitudes;
                if (inputRecicladores) inputRecicladores.value = p.recicladores;
                if (inputPuestos) inputPuestos.value = p.puestos;
                if (inputDuracion) inputDuracion.value = p.duracion;
                if (inputReplicas) inputReplicas.value = p.replicas;
                if (inputSemilla) inputSemilla.value = p.semilla;
            }
        });
    }

    // Ejecución de la simulación
    if (btnEjecutar) {
        btnEjecutar.addEventListener('click', async () => {
            btnEjecutar.disabled = true;
            if (spinnerEjecucion) spinnerEjecucion.style.display = 'inline-block';
            if (bannerEstado) {
                bannerEstado.style.display = 'block';
                bannerEstado.textContent = 'Ejecutando réplicas de SimPy y Monte Carlo en Python… Por favor espera.';
                bannerEstado.className = 'dashboard-alert alert-info';
            }

            const payload = {
                escenario: selectEscenario ? selectEscenario.value : 'A',
                solicitudes_dia: parseFloat(inputSolicitudes.value),
                num_recicladores: parseInt(inputRecicladores.value, 10),
                puestos_pesaje: parseInt(inputPuestos.value, 10),
                duracion_dias: parseFloat(inputDuracion.value),
                num_replicas: parseInt(inputReplicas.value, 10),
                semilla: parseInt(inputSemilla.value, 10)
            };

            try {
                const resp = await fetch(`${baseUrl}admin/simulacion/ejecutar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await resp.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error desconocido al ejecutar la simulación.');
                }

                if (bannerEstado) {
                    bannerEstado.textContent = `✓ Simulación finalizada con éxito (${data.configuracion.num_replicas} réplicas Monte Carlo procesadas).`;
                    bannerEstado.className = 'dashboard-alert alert-success';
                }

                // Renderizar los resultados
                renderizarResultadosSimulacion(data);

            } catch (err) {
                console.error('Fallo en la simulación:', err);
                if (bannerEstado) {
                    bannerEstado.textContent = `Error: ${err.message}`;
                    bannerEstado.className = 'dashboard-alert alert-danger';
                }
            } finally {
                btnEjecutar.disabled = false;
                if (spinnerEjecucion) spinnerEjecucion.style.display = 'none';
            }
        });
    }

    // Botón de exportación a CSV
    if (btnExportar) {
        btnExportar.addEventListener('click', () => {
            window.location.href = `${baseUrl}admin/simulacion/exportar`;
        });
    }

    /**
     * Actualiza la interfaz con los resultados de la simulación
     */
    function renderizarResultadosSimulacion(data) {
        const mc = data.resumen_monte_carlo || {};
        const diag = data.cuellos_botella || data.diagnostico_cuello_botella || {};
        const ml = data.modelo_ml_integrado || {};

        // 1. Panel de Indicadores Principales (Medias)
        setText('kpi-sim-completadas', intFmt.format(mc.solicitudes_completadas ? mc.solicitudes_completadas.media : 0));
        setText('kpi-sim-atendidas-pct', `${numFmt.format(mc.pct_atendidas ? mc.pct_atendidas.media : 0)}%`);
        setText('kpi-sim-kg', `${numFmt.format(mc.kg_totales ? mc.kg_totales.media : 0)} kg`);
        
        const tons = (mc.kg_totales ? mc.kg_totales.media : 0) / 1000.0;
        setText('kpi-sim-tons', `≈ ${numFmt.format(tons)} Toneladas`);

        setText('kpi-sim-puntos-regla', `${intFmt.format(mc.ecopuntos_regla_directa ? mc.ecopuntos_regla_directa.media : 0)} pts`);
        
        const ptsML = ml.ecopuntos_proyectados_media;
        setText('kpi-sim-puntos-ml', ptsML !== null && ptsML !== undefined ? `${numFmt.format(ptsML)} pts` : 'No disponible');

        setText('kpi-sim-espera-rec', `${numFmt.format(mc.espera_reciclador_min ? mc.espera_reciclador_min.media : 0)} min`);
        setText('kpi-sim-espera-pes', `${numFmt.format(mc.espera_pesaje_min ? mc.espera_pesaje_min.media : 0)} min`);
        setText('kpi-sim-ciclo-total', `${numFmt.format(mc.tiempo_ciclo_total_min ? mc.tiempo_ciclo_total_min.media : 0)} min`);

        // 2. Diagnóstico de Cuellos de Botella
        setText('diag-cuello-titulo', diag.diagnostico || diag.identificado || 'Sin cuello de botella crítico');
        setText('diag-cuello-recom', diag.recomendacion || 'Operación balanceada.');
        
        const badgeDiag = document.getElementById('diag-cuello-badge');
        if (badgeDiag) {
            const sev = (diag.severidad || '').toUpperCase();
            if (sev === 'ALTA') {
                badgeDiag.className = 'badge badge-danger';
                badgeDiag.textContent = 'Congestión Crítica';
            } else if (sev === 'MEDIA') {
                badgeDiag.className = 'badge badge-warning';
                badgeDiag.textContent = 'Alerta Operativa';
            } else {
                badgeDiag.className = 'badge badge-success';
                badgeDiag.textContent = 'Operación Óptima';
            }
        }

        // 3. Tabla de Variabilidad Monte Carlo (Percentiles)
        renderizarTablaMonteCarlo(mc);

        // 4. Tabla Comparativa de Escenarios
        if (data.comparativa_escenarios) {
            renderizarTablaComparativa(data.comparativa_escenarios);
        }

        // 5. Gráfico de Evolución de Colas (SVG interactivo)
        if (data.serie_colas_ejemplo && data.serie_colas_ejemplo.length > 0) {
            renderizarGraficoColas(data.serie_colas_ejemplo);
        }

        // 6. Explicación Dinámica y Diagnóstico del Gráfico
        generarExplicacionGraficoColas(data, data.serie_colas_ejemplo || []);
    }

    /**
     * Renderiza un gráfico SVG responsivo y ampliado de la evolución temporal de colas
     */
    function renderizarGraficoColas(puntos) {
        const svgCont = document.getElementById('grafico-colas-svg');
        if (!svgCont) return;
        svgCont.replaceChildren();

        if (!puntos || puntos.length === 0) {
            svgCont.innerHTML = '<span style="color:var(--muted); font-size:0.85rem;">No hay datos de colas disponibles para esta simulación.</span>';
            return;
        }

        const width = 1000;
        const height = 340;
        const padLeft = 65;
        const padRight = 35;
        const padTop = 35;
        const padBottom = 45;

        const maxTiempo = Math.max(...puntos.map(p => p.tiempo_h), 1);
        const maxColaVal = Math.max(
            ...puntos.map(p => Math.max(p.cola_recicladores || 0, p.cola_pesaje || 0)),
            4
        );
        // Redondear el eje Y a un múltiplo cómodo
        const maxCola = Math.ceil(maxColaVal * 1.15);

        const escalaX = (h) => padLeft + (h / maxTiempo) * (width - padLeft - padRight);
        const escalaY = (v) => height - padBottom - (v / maxCola) * (height - padTop - padBottom);

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.display = 'block';

        // Definiciones (Gradientes de área para sombreado)
        const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
        defs.innerHTML = `
            <linearGradient id="gradAreaRec" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#EF4444" stop-opacity="0.18"/>
                <stop offset="100%" stop-color="#EF4444" stop-opacity="0.01"/>
            </linearGradient>
            <linearGradient id="gradAreaPes" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.16"/>
                <stop offset="100%" stop-color="#2563EB" stop-opacity="0.01"/>
            </linearGradient>
        `;
        svg.appendChild(defs);

        // Cuadrícula horizontal (Gridlines Y)
        const numTicksY = 5;
        for (let i = 0; i <= numTicksY; i++) {
            const val = Math.round((maxCola / numTicksY) * i);
            const yPos = escalaY(val);

            // Línea de cuadrícula
            const gridLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            gridLine.setAttribute('x1', padLeft);
            gridLine.setAttribute('y1', yPos);
            gridLine.setAttribute('x2', width - padRight);
            gridLine.setAttribute('y2', yPos);
            gridLine.setAttribute('stroke', i === 0 ? '#CBD5E1' : '#E2E8F0');
            gridLine.setAttribute('stroke-width', i === 0 ? '1.5' : '1');
            if (i > 0) gridLine.setAttribute('stroke-dasharray', '4 3');
            svg.appendChild(gridLine);

            // Etiqueta del eje Y
            const lblY = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            lblY.setAttribute('x', padLeft - 10);
            lblY.setAttribute('y', yPos + 4);
            lblY.setAttribute('text-anchor', 'end');
            lblY.setAttribute('font-size', '11');
            lblY.setAttribute('font-weight', '600');
            lblY.setAttribute('fill', '#64748B');
            lblY.textContent = `${val} sol`;
            svg.appendChild(lblY);
        }

        // Ticks y marcas del eje X (Tiempo en Horas)
        const stepH = maxTiempo > 96 ? 24 : (maxTiempo > 48 ? 12 : 6);
        for (let h = 0; h <= maxTiempo; h += stepH) {
            const xPos = escalaX(h);
            const diaNum = Math.floor(h / 24) + 1;

            // Marca vertical sutil
            const markLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            markLine.setAttribute('x1', xPos);
            markLine.setAttribute('y1', height - padBottom);
            markLine.setAttribute('x2', xPos);
            markLine.setAttribute('y2', height - padBottom + 6);
            markLine.setAttribute('stroke', '#94A3B8');
            markLine.setAttribute('stroke-width', '1.2');
            svg.appendChild(markLine);

            // Texto hora
            const lblX = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            lblX.setAttribute('x', xPos);
            lblX.setAttribute('y', height - padBottom + 20);
            lblX.setAttribute('text-anchor', 'middle');
            lblX.setAttribute('font-size', '10.5');
            lblX.setAttribute('font-weight', '500');
            lblX.setAttribute('fill', '#64748B');
            lblX.textContent = `${h}h`;
            svg.appendChild(lblX);

            // Subtítulo de Día si es múltiplo de 24h
            if (h % 24 === 0 && h > 0) {
                const lblDia = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                lblDia.setAttribute('x', xPos);
                lblDia.setAttribute('y', height - padBottom + 33);
                lblDia.setAttribute('text-anchor', 'middle');
                lblDia.setAttribute('font-size', '9.5');
                lblDia.setAttribute('font-weight', '700');
                lblDia.setAttribute('fill', '#087A3D');
                lblDia.textContent = `Día ${diaNum}`;
                svg.appendChild(lblDia);
            }
        }

        // Eje X principal
        const ejeX = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        ejeX.setAttribute('x1', padLeft); ejeX.setAttribute('y1', height - padBottom);
        ejeX.setAttribute('x2', width - padRight); ejeX.setAttribute('y2', height - padBottom);
        ejeX.setAttribute('stroke', '#64748B'); ejeX.setAttribute('stroke-width', '1.8');
        svg.appendChild(ejeX);

        // Eje Y principal
        const ejeY = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        ejeY.setAttribute('x1', padLeft); ejeY.setAttribute('y1', padTop);
        ejeY.setAttribute('x2', padLeft); ejeY.setAttribute('y2', height - padBottom);
        ejeY.setAttribute('stroke', '#64748B'); ejeY.setAttribute('stroke-width', '1.8');
        svg.appendChild(ejeY);

        // 1. ÁREA BAJO LA CURVA: Recicladores (Rojo translúcido)
        let dAreaRec = `M ${escalaX(puntos[0].tiempo_h)} ${escalaY(0)}`;
        puntos.forEach(p => { dAreaRec += ` L ${escalaX(p.tiempo_h)} ${escalaY(p.cola_recicladores || 0)}`; });
        dAreaRec += ` L ${escalaX(puntos[puntos.length - 1].tiempo_h)} ${escalaY(0)} Z`;
        const pRecArea = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pRecArea.setAttribute('d', dAreaRec);
        pRecArea.setAttribute('fill', 'url(#gradAreaRec)');
        svg.appendChild(pRecArea);

        // 2. ÁREA BAJO LA CURVA: Báscula (Azul translúcido)
        let dAreaPes = `M ${escalaX(puntos[0].tiempo_h)} ${escalaY(0)}`;
        puntos.forEach(p => { dAreaPes += ` L ${escalaX(p.tiempo_h)} ${escalaY(p.cola_pesaje || 0)}`; });
        dAreaPes += ` L ${escalaX(puntos[puntos.length - 1].tiempo_h)} ${escalaY(0)} Z`;
        const pPesArea = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pPesArea.setAttribute('d', dAreaPes);
        pPesArea.setAttribute('fill', 'url(#gradAreaPes)');
        svg.appendChild(pPesArea);

        // 3. CURVA PRINCIPAL: Cola de Recicladores (Rojo sólido)
        let pathRec = `M ${escalaX(puntos[0].tiempo_h)} ${escalaY(puntos[0].cola_recicladores || 0)}`;
        let maxRecPoint = puntos[0];
        puntos.forEach(p => {
            const y = escalaY(p.cola_recicladores || 0);
            pathRec += ` L ${escalaX(p.tiempo_h)} ${y}`;
            if ((p.cola_recicladores || 0) > (maxRecPoint.cola_recicladores || 0)) {
                maxRecPoint = p;
            }
        });
        const pRecEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pRecEl.setAttribute('d', pathRec);
        pRecEl.setAttribute('fill', 'none');
        pRecEl.setAttribute('stroke', '#EF4444');
        pRecEl.setAttribute('stroke-width', '3');
        pRecEl.setAttribute('stroke-linejoin', 'round');
        pRecEl.setAttribute('stroke-linecap', 'round');
        svg.appendChild(pRecEl);

        // 4. CURVA PRINCIPAL: Cola de Báscula (Azul discontinua)
        let pathPes = `M ${escalaX(puntos[0].tiempo_h)} ${escalaY(puntos[0].cola_pesaje || 0)}`;
        let maxPesPoint = puntos[0];
        puntos.forEach(p => {
            const y = escalaY(p.cola_pesaje || 0);
            pathPes += ` L ${escalaX(p.tiempo_h)} ${y}`;
            if ((p.cola_pesaje || 0) > (maxPesPoint.cola_pesaje || 0)) {
                maxPesPoint = p;
            }
        });
        const pPesEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        pPesEl.setAttribute('d', pathPes);
        pPesEl.setAttribute('fill', 'none');
        pPesEl.setAttribute('stroke', '#2563EB');
        pPesEl.setAttribute('stroke-width', '3');
        pPesEl.setAttribute('stroke-dasharray', '6 4');
        pPesEl.setAttribute('stroke-linejoin', 'round');
        pPesEl.setAttribute('stroke-linecap', 'round');
        svg.appendChild(pPesEl);

        // Marcador del Pico Máximo de Cola de Recicladores (Punto Rojo)
        if (maxRecPoint && maxRecPoint.cola_recicladores > 0) {
            const cx = escalaX(maxRecPoint.tiempo_h);
            const cy = escalaY(maxRecPoint.cola_recicladores);

            const dotRec = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            dotRec.setAttribute('cx', cx);
            dotRec.setAttribute('cy', cy);
            dotRec.setAttribute('r', '5');
            dotRec.setAttribute('fill', '#EF4444');
            dotRec.setAttribute('stroke', '#fff');
            dotRec.setAttribute('stroke-width', '2');
            svg.appendChild(dotRec);

            const txtPicoRec = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            txtPicoRec.setAttribute('x', Math.min(cx, width - 110));
            txtPicoRec.setAttribute('y', Math.max(cy - 9, 18));
            txtPicoRec.setAttribute('font-size', '10');
            txtPicoRec.setAttribute('font-weight', '700');
            txtPicoRec.setAttribute('fill', '#DC2626');
            txtPicoRec.textContent = `Pico Rec: ${maxRecPoint.cola_recicladores} sol`;
            svg.appendChild(txtPicoRec);
        }

        // Marcador del Pico Máximo de Báscula (Punto Azul)
        if (maxPesPoint && maxPesPoint.cola_pesaje > 0) {
            const cx = escalaX(maxPesPoint.tiempo_h);
            const cy = escalaY(maxPesPoint.cola_pesaje);

            const dotPes = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            dotPes.setAttribute('cx', cx);
            dotPes.setAttribute('cy', cy);
            dotPes.setAttribute('r', '5');
            dotPes.setAttribute('fill', '#2563EB');
            dotPes.setAttribute('stroke', '#fff');
            dotPes.setAttribute('stroke-width', '2');
            svg.appendChild(dotPes);

            const txtPicoPes = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            txtPicoPes.setAttribute('x', Math.min(cx, width - 110));
            txtPicoPes.setAttribute('y', Math.min(cy + 18, height - padBottom - 5));
            txtPicoPes.setAttribute('font-size', '10');
            txtPicoPes.setAttribute('font-weight', '700');
            txtPicoPes.setAttribute('fill', '#1D4ED8');
            txtPicoPes.textContent = `Pico Báscula: ${maxPesPoint.cola_pesaje} rec`;
            svg.appendChild(txtPicoPes);
        }

        // Título del Eje Y
        const titY = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        titY.setAttribute('x', padLeft);
        titY.setAttribute('y', 18);
        titY.setAttribute('font-size', '11');
        titY.setAttribute('font-weight', '700');
        titY.setAttribute('fill', '#334155');
        titY.textContent = `▲ Cantidad en espera (Solicitudes / Recicladores)`;
        svg.appendChild(titY);

        // Título del Eje X
        const titX = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        titX.setAttribute('x', width - padRight);
        titX.setAttribute('y', height - padBottom - 6);
        titX.setAttribute('text-anchor', 'end');
        titX.setAttribute('font-size', '11');
        titX.setAttribute('font-weight', '700');
        titX.setAttribute('fill', '#334155');
        titX.textContent = `Horas Simuladas ▶`;
        svg.appendChild(titX);

        svgCont.appendChild(svg);
    }

    /**
     * Genera una explicación dinámica, breve y concisa debajo del gráfico
     * con especial énfasis didáctico en el Escenario Personalizado.
     */
    function generarExplicacionGraficoColas(data, puntos) {
        const txtCont = document.getElementById('explicacion-contenido-texto');
        const tagEsc = document.getElementById('explicacion-escenario-tag');
        const boxCont = document.getElementById('explicacion-grafico-colas');
        if (!txtCont) return;

        const cfg = data.configuracion || {};
        const mc = data.resumen_monte_carlo || {};
        const diag = data.cuellos_botella || data.diagnostico_cuello_botella || {};
        const escSeleccionado = (selectEscenario ? selectEscenario.value : 'A');
        const esc = cfg.escenario || escSeleccionado;

        // Calcular picos y estadísticas de las curvas del gráfico
        let maxColaRec = 0;
        let maxColaPes = 0;
        let horasSim = 0;
        if (puntos && puntos.length > 0) {
            maxColaRec = Math.max(...puntos.map(p => p.cola_recicladores || 0));
            maxColaPes = Math.max(...puntos.map(p => p.cola_pesaje || 0));
            horasSim = Math.round(Math.max(...puntos.map(p => p.tiempo_h || 0)));
        }

        const esperaRec = mc.espera_reciclador_min ? mc.espera_reciclador_min.media : 0;
        const esperaPes = mc.espera_pesaje_min ? mc.espera_pesaje_min.media : 0;
        const cicloTotal = mc.tiempo_ciclo_total_min ? mc.tiempo_ciclo_total_min.media : 0;
        const pctAtendidas = mc.pct_atendidas ? mc.pct_atendidas.media : 0;
        const solCompletadas = mc.solicitudes_completadas ? mc.solicitudes_completadas.media : 0;

        let tagTexto = 'Escenario';
        let tagColor = '#087A3D';
        let tagBg = '#EEF8EE';
        let htmlExplicacion = '';

        if (esc === 'CUSTOM' || esc === 'Personalizado') {
            tagTexto = `🧪 Personalizado: ${cfg.num_recicladores || 0} rec / ${cfg.puestos_pesaje || 0} bás / ${cfg.solicitudes_dia || 0} sol/día`;
            tagColor = '#7C3AED';
            tagBg = '#F3E8FF';
            if (boxCont) boxCont.style.borderLeft = '5px solid #8B5CF6';

            // Diagnóstico específico para los parámetros definidos por el usuario
            let balanceTexto = '';
            let recomendacionTexto = '';
            const ratioDemandaFlota = (cfg.solicitudes_dia || 40) / Math.max(cfg.num_recicladores || 30, 1);
            const ratioFlotaBascula = (cfg.num_recicladores || 30) / Math.max(cfg.puestos_pesaje || 2, 1);

            if (esperaRec > 45 || maxColaRec >= 12 || ratioDemandaFlota > 1.8) {
                balanceTexto = `
                    <div style="margin-bottom:8px; color:#B91C1C;">
                        <strong>🚨 Flota de Recicladores Insuficiente:</strong> La demanda de <strong>${cfg.solicitudes_dia} solicitudes/día</strong> supera la capacidad de los <strong>${cfg.num_recicladores} recicladores</strong> en calle. Esto provoca que la <strong>curva roja</strong> se dispare (pico de <strong>${maxColaRec} solicitudes</strong> acumuladas), elevando el tiempo de espera ciudadano a <strong>${numFmt.format(esperaRec)} min</strong>.
                    </div>
                `;
                recomendacionTexto = `Se recomienda sumar al menos <strong>${Math.ceil((cfg.solicitudes_dia - cfg.num_recicladores * 1.2) + 5)} recicladores adicionales</strong> a la zona para aplanar la cola roja.`;
            } else if (esperaPes > 22 || maxColaPes >= 7 || ratioFlotaBascula > 22) {
                balanceTexto = `
                    <div style="margin-bottom:8px; color:#B45309;">
                        <strong>⚠️ Cuello de Botella en Centro de Acopio:</strong> Disponer de solo <strong>${cfg.puestos_pesaje} báscula(s)</strong> para ${cfg.num_recicladores} recicladores crea filas de espera al momento de entregar el material. La <strong>curva azul discontinua</strong> presenta congestión (pico de <strong>${maxColaPes} recicladores</strong> esperando turno de pesaje, promedio <strong>${numFmt.format(esperaPes)} min</strong>).
                    </div>
                `;
                recomendacionTexto = `Habilitar <strong>${(cfg.puestos_pesaje || 2) + 1} puestos de pesaje simultáneos</strong> para desahogar las entregas y reducir el tiempo de ciclo total.`;
            } else if (pctAtendidas < 80) {
                balanceTexto = `
                    <div style="margin-bottom:8px; color:#B91C1C;">
                        <strong>⚠️ Cumplimiento Crítico (${numFmt.format(pctAtendidas)}%):</strong> El sistema acumuló <strong>${numFmt.format(mc.solicitudes_pendientes ? mc.solicitudes_pendientes.media : 0)} solicitudes pendientes</strong> sin procesar al culminar la simulación de ${horasSim} horas.
                    </div>
                `;
                recomendacionTexto = `Se sugiere un balance conjunto: reforzar tanto la flota de recicladores como los horarios de recepción en el centro de acopio.`;
            } else {
                balanceTexto = `
                    <div style="margin-bottom:8px; color:#15803D;">
                        <strong>✅ Configuración Óptima y Balanceada:</strong> La relación entre ${cfg.num_recicladores} recicladores y ${cfg.puestos_pesaje} báscula(s) absorbe satisfactoriamente la demanda de ${cfg.solicitudes_dia} solicitudes/día. Ambas colas permanecen bajas y controladas (pico máx: ${Math.max(maxColaRec, maxColaPes)} solicitudes), con una tasa de cumplimiento del <strong>${numFmt.format(pctAtendidas)}%</strong>.
                    </div>
                `;
                recomendacionTexto = `Esta estructura operativa garantiza un flujo logístico ágil con tiempos de espera contenidos y alta satisfacción de los ciudadanos.`;
            }

            htmlExplicacion = `
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div>
                        ${balanceTexto}
                    </div>
                    <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:8px; padding:10px 14px; font-size:0.83rem;">
                        <strong>📊 Resumen de tu Simulación Personalizada:</strong> En ${horasSim} horas simuladas se atendieron <strong>${numFmt.format(solCompletadas)} recolecciones</strong>, logrando un tiempo total de ciclo de <strong>${numFmt.format(cicloTotal)} min</strong> (espera en calle: ${numFmt.format(esperaRec)} min | espera en báscula: ${numFmt.format(esperaPes)} min).
                    </div>
                    <div style="font-size:0.82rem; color:var(--muted); margin-top:2px;">
                        📌 <em>Sugerencia de optimización:</em> ${recomendacionTexto}
                    </div>
                </div>
            `;

        } else if (esc === 'B') {
            tagTexto = 'Escenario B: Alta Demanda (+75%)';
            tagColor = '#B45309';
            tagBg = '#FEF3C7';
            if (boxCont) boxCont.style.borderLeft = '5px solid #F59E0B';

            htmlExplicacion = `
                <div style="color:var(--text);">
                    <div style="margin-bottom:6px; color:#92400E; font-weight:700;">
                        ⚠️ Impacto del incremento repentino de demanda ciudadana (70 sol/día vs 30 recicladores):
                    </div>
                    <p style="margin:0 0 8px;">
                        Al mantener la misma flota base (30 recicladores y 2 básculas) frente a un incremento del 75% en solicitudes, la <strong>curva roja asciende con rapidez</strong> alcanzando un pico de <strong>${maxColaRec} solicitudes</strong> en cola. Los recicladores quedan saturados en ruta, elevando la espera de asignación a <strong>${numFmt.format(esperaRec)} min</strong> y reduciendo el cumplimiento al <strong>${numFmt.format(pctAtendidas)}%</strong>.
                    </p>
                    <div style="font-size:0.82rem; color:var(--muted);">
                        📌 <em>Conclusión didáctica:</em> Simula el escenario posterior a una campaña de sensibilización ambiental masiva sin fortalecimiento previo de la flota operativa.
                    </div>
                </div>
            `;

        } else if (esc === 'C') {
            tagTexto = 'Escenario C: Expansión de Capacidad';
            tagColor = '#15803D';
            tagBg = '#DCFCE7';
            if (boxCont) boxCont.style.borderLeft = '5px solid #10B981';

            htmlExplicacion = `
                <div style="color:var(--text);">
                    <div style="margin-bottom:6px; color:#166534; font-weight:700;">
                        ✅ Estabilización exitosa mediante incremento proporcional de recursos (45 rec / 4 básculas):
                    </div>
                    <p style="margin:0 0 8px;">
                        Bajo la misma alta demanda de 70 solicitudes/día, la adición de 15 recicladores y 2 básculas logra <strong>aplanar de forma contundente ambas colas</strong>. La curva roja no supera las <strong>${maxColaRec} solicitudes</strong> en cola y el tiempo de báscula disminuye a <strong>${numFmt.format(esperaPes)} min</strong>, recuperando un cumplimiento del <strong>${numFmt.format(pctAtendidas)}%</strong> con un ciclo ágil de <strong>${numFmt.format(cicloTotal)} min</strong>.
                    </p>
                    <div style="font-size:0.82rem; color:var(--muted);">
                        📌 <em>Conclusión didáctica:</em> Demuestra cuantitativamente el retorno operacional de invertir en mayor número de recicladores y balanzas certificadas.
                    </div>
                </div>
            `;

        } else {
            // Escenario A por defecto
            tagTexto = 'Escenario A: Referencia Histórica';
            tagColor = '#087A3D';
            tagBg = '#EEF8EE';
            if (boxCont) boxCont.style.borderLeft = '5px solid #087A3D';

            htmlExplicacion = `
                <div style="color:var(--text);">
                    <div style="margin-bottom:6px; color:#065C2D; font-weight:700;">
                        🌿 Comportamiento estándar equilibrado del ecosistema EcoPoints en Bogotá:
                    </div>
                    <p style="margin:0 0 8px;">
                        Con 40 solicitudes/día, 30 recicladores y 2 puestos de pesaje, la simulación opera en <strong>régimen estacionario saludable</strong>. Las colas se mantienen transitorias (pico de <strong>${maxColaRec} sol</strong> en calle y <strong>${maxColaPes} en pesaje</strong>), logrando atender al <strong>${numFmt.format(pctAtendidas)}%</strong> de los ciudadanos con un tiempo de ciclo promedio de <strong>${numFmt.format(cicloTotal)} min</strong>.
                    </p>
                    <div style="font-size:0.82rem; color:var(--muted);">
                        📌 <em>Conclusión didáctica:</em> Sirve como línea base de comparación para evaluar cualquier incremento futuro en la generación de residuos reciclables.
                    </div>
                </div>
            `;
        }

        if (tagEsc) {
            tagEsc.textContent = tagTexto;
            tagEsc.style.color = tagColor;
            tagEsc.style.background = tagBg;
        }
        txtCont.innerHTML = htmlExplicacion;
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
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

    // Si ya vino una simulación inicial cargada desde PHP, renderizarla
    if (config.simulacionInicial && config.simulacionInicial.success) {
        renderizarResultadosSimulacion(config.simulacionInicial);
    }
});

