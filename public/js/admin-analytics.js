(() => {
    const configuration = window.ecopointsAnalytics;
    if (!configuration) return;

    const numberFormat = new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 2
    });

    function appendText(parent, tag, text, className) {
        const element = document.createElement(tag);
        element.textContent = text;
        if (className) element.className = className;
        parent.appendChild(element);
        return element;
    }

    function svgElement(parent, tag, attributes, text) {
        const element = document.createElementNS('http://www.w3.org/2000/svg', tag);
        Object.entries(attributes || {}).forEach(([name, value]) => {
            element.setAttribute(name, String(value));
        });
        if (text !== undefined) element.textContent = text;
        parent.appendChild(element);
        return element;
    }

    function createChart(container, title, width, height) {
        container.replaceChildren();
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', title);
        svg.classList.add('analytics-chart-svg');
        svgElement(svg, 'title', {}, title);
        container.appendChild(svg);
        return svg;
    }

    function renderRegressionChart(container, samples) {
        const width = 900;
        const rowHeight = 42;
        const top = 54;
        const height = top + samples.length * rowHeight + 24;
        const svg = createChart(
            container,
            'Puntos estimados comparados con el target observado de un mes consecutivo',
            width,
            height
        );
        const values = samples.reduce((all, sample) => {
            all.push(Number(sample.puntos_observados), Number(sample.puntos_estimados));
            return all;
        }, []);
        const minValue = Math.min(0, ...values);
        const maxValue = Math.max(1, ...values);
        const plotX = 245;
        const plotWidth = 610;
        const scale = (value) => plotX + ((value - minValue) / (maxValue - minValue)) * plotWidth;
        const zeroX = scale(0);

        svgElement(svg, 'rect', { x: 10, y: 12, width: 12, height: 12, fill: '#087A3D' });
        svgElement(svg, 'text', { x: 28, y: 22, class: 'chart-label' }, 'Target observado');
        svgElement(svg, 'rect', { x: 158, y: 12, width: 12, height: 12, fill: '#60A5FA' });
        svgElement(svg, 'text', { x: 176, y: 22, class: 'chart-label' }, 'Estimación');
        svgElement(svg, 'line', {
            x1: zeroX, y1: 34, x2: zeroX, y2: height - 16,
            stroke: '#94A3B8', 'stroke-dasharray': '4 4'
        });

        samples.forEach((sample, index) => {
            const y = top + index * rowHeight;
            svgElement(
                svg,
                'text',
                { x: 10, y: y + 12, class: 'chart-label' },
                sample.etiqueta + ' · ' + sample.periodo + ' → ' + sample.periodo_objetivo_observado
            );
            [
                { value: Number(sample.puntos_observados), y: y + 1, color: '#087A3D' },
                { value: Number(sample.puntos_estimados), y: y + 19, color: '#60A5FA' }
            ].forEach((item) => {
                const endX = scale(item.value);
                svgElement(svg, 'rect', {
                    x: Math.min(zeroX, endX),
                    y: item.y,
                    width: Math.max(1, Math.abs(endX - zeroX)),
                    height: 11,
                    rx: 3,
                    fill: item.color
                });
                svgElement(
                    svg,
                    'text',
                    {
                        x: Math.min(width - 42, endX + (item.value < 0 ? -38 : 6)),
                        y: item.y + 10,
                        class: 'chart-value'
                    },
                    numberFormat.format(item.value)
                );
            });
        });
    }

    function renderCountChart(container, title, entries, colors) {
        const width = 720;
        const rowHeight = 42;
        const top = 24;
        const height = Math.max(100, top + entries.length * rowHeight + 24);
        const svg = createChart(container, title, width, height);
        const maxCount = Math.max(1, ...entries.map((entry) => entry.count));
        const plotX = 210;
        const plotWidth = 430;

        entries.forEach((entry, index) => {
            const y = top + index * rowHeight;
            const color = colors[entry.label] || '#087A3D';
            const barWidth = (entry.count / maxCount) * plotWidth;
            svgElement(svg, 'text', { x: 10, y: y + 17, class: 'chart-label' }, entry.label);
            svgElement(svg, 'rect', {
                x: plotX, y, width: Math.max(2, barWidth), height: 23, rx: 5, fill: color
            });
            svgElement(svg, 'text', {
                x: plotX + barWidth + 9, y: y + 17, class: 'chart-value'
            }, String(entry.count));
        });
    }

    function renderAssociationChart(container, response) {
        const rules = response.reglas;
        if (!Array.isArray(rules) || rules.length === 0) {
            appendText(container, 'p', 'El servicio no devolvió reglas para graficar.');
            return;
        }

        const width = 760;
        const height = 360;
        const left = 68;
        const top = 24;
        const plotWidth = 620;
        const plotHeight = 260;
        const svg = createChart(
            container,
            'Reglas Apriori: soporte frente a confianza; el tamaño indica lift',
            width,
            height
        );
        [0, 0.25, 0.5, 0.75, 1].forEach((tick) => {
            const x = left + tick * plotWidth;
            const y = top + (1 - tick) * plotHeight;
            svgElement(svg, 'line', {
                x1: x, y1: top, x2: x, y2: top + plotHeight, stroke: '#E2E8F0'
            });
            svgElement(svg, 'line', {
                x1: left, y1: y, x2: left + plotWidth, y2: y, stroke: '#E2E8F0'
            });
            svgElement(svg, 'text', {
                x: x - 10, y: top + plotHeight + 19, class: 'chart-axis'
            }, numberFormat.format(tick * 100) + '%');
            svgElement(svg, 'text', {
                x: 18, y: y + 4, class: 'chart-axis'
            }, numberFormat.format(tick * 100) + '%');
        });
        svgElement(svg, 'text', {
            x: left + plotWidth / 2 - 32, y: height - 8, class: 'chart-label'
        }, 'Soporte');
        svgElement(svg, 'text', {
            x: 5, y: 13, class: 'chart-label'
        }, 'Confianza');

        rules.forEach((rule) => {
            const x = left + Number(rule.soporte) * plotWidth;
            const y = top + (1 - Number(rule.confianza)) * plotHeight;
            const radius = Math.max(5, Math.min(12, 5 + Number(rule.lift) * 2));
            const point = svgElement(svg, 'circle', {
                cx: x,
                cy: y,
                r: radius,
                fill: Number(rule.lift) >= 1 ? '#087A3D' : '#F59E0B',
                opacity: 0.78
            });
            svgElement(
                point,
                'title',
                {},
                rule.antecedente + ' → ' + rule.consecuente +
                    ' | soporte ' + numberFormat.format(Number(rule.soporte) * 100) +
                    '% | confianza ' + numberFormat.format(Number(rule.confianza) * 100) +
                    '% | lift ' + numberFormat.format(Number(rule.lift))
            );
        });

        const summary = document.createElement('div');
        summary.className = 'analytics-rule-summary';
        appendText(summary, 'p', response.fuente + ' Lift mayor que 1 indica asociación positiva en los datos analizados.');
        const list = document.createElement('ul');
        rules.slice().sort((first, second) => second.confianza - first.confianza)
            .slice(0, 5)
            .forEach((rule) => {
                appendText(
                    list,
                    'li',
                    rule.antecedente + ' → ' + rule.consecuente +
                        ' · confianza ' + numberFormat.format(rule.confianza * 100) +
                        '% · lift ' + numberFormat.format(rule.lift)
                );
            });
        summary.appendChild(list);
        container.appendChild(svg);
        container.appendChild(summary);
    }

    function renderModelCharts(payload) {
        if (!Array.isArray(payload.muestra) || payload.muestra.length === 0) {
            throw new Error('No hay registros con target observado para generar las gráficas.');
        }

        const charts = document.querySelector('[data-model-charts]');
        renderRegressionChart(charts.querySelector('[data-chart-content="regression"]'), payload.muestra);

        const countValues = (field) => {
            const counts = {};
            payload.muestra.forEach((sample) => {
                counts[sample[field]] = (counts[sample[field]] || 0) + 1;
            });
            return Object.entries(counts).map(([label, count]) => ({ label, count }));
        };
        renderCountChart(
            charts.querySelector('[data-chart-content="classification"]'),
            'Cantidad de perfiles por nivel de actividad predicho',
            countValues('nivel_actividad'),
            { Alta: '#087A3D', Media: '#F59E0B', Baja: '#60A5FA' }
        );
        renderCountChart(
            charts.querySelector('[data-chart-content="segmentation"]'),
            'Cantidad de recicladores por perfil histórico',
            countValues('segmento'),
            {
                'Líderes (Alto)': '#087A3D',
                'Constantes (Medio)': '#60A5FA',
                'Ocasionales (Base)': '#F59E0B'
            }
        );
        const coverage = payload.muestra.map((sample) => {
            return sample.etiqueta + ': ' + sample.periodos_historia + ' meses';
        }).join('; ');
        appendText(
            charts.querySelector('[data-chart-content="segmentation"]'),
            'p',
            'Cobertura de los promedios usados en esta muestra: ' + coverage + '.',
            'analytics-chart-caption'
        );
        const anomalyCounts = [
            {
                label: 'Normal según el detector',
                count: payload.muestra.filter((sample) => !sample.es_atipico).length
            },
            {
                label: 'Atípico: requiere revisión',
                count: payload.muestra.filter((sample) => sample.es_atipico).length
            }
        ];
        renderCountChart(
            charts.querySelector('[data-chart-content="anomalies"]'),
            'Registros normales y atípicos en la muestra del detector',
            anomalyCounts,
            { 'Normal según el detector': '#087A3D', 'Atípico: requiere revisión': '#F59E0B' }
        );
        renderAssociationChart(
            charts.querySelector('[data-chart-content="association"]'),
            payload.reglas
        );
        appendText(
            charts.querySelector('[data-chart-content="regression"]'),
            'p',
            'Cada barra compara periodos consecutivos: el target observado del mes siguiente frente a la estimación.',
            'analytics-chart-caption'
        );
    }

    async function loadModelCharts(button) {
        if (!configuration.chartsRoute || !button) return;
        button.disabled = true;
        button.textContent = 'Actualizando…';
        try {
            const response = await fetch(configuration.chartsRoute, {
                headers: { Accept: 'application/json' }
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'No fue posible cargar las gráficas de los modelos.');
            }
            renderModelCharts(data);
        } catch (error) {
            const charts = document.querySelector('[data-model-charts]');
            charts.querySelectorAll('[data-chart-content]').forEach((container) => {
                container.replaceChildren();
                appendText(
                    container,
                    'p',
                    error instanceof Error ? error.message : 'No fue posible cargar las gráficas.',
                    'analytics-chart-error'
                );
            });
        } finally {
            button.disabled = false;
            button.textContent = 'Actualizar gráficas';
        }
    }

    function renderResult(container, payload, operation) {
        container.replaceChildren();
        container.classList.remove('is-error');

        if (operation === 'predecir-puntos') {
            appendText(container, 'strong', numberFormat.format(payload.ecopuntos_estimados) + ' Ecopoints estimados', 'analytics-result-title');
            appendText(container, 'p', 'Horizonte: siguiente observación disponible. El periodo calendario objetivo aún no se puede determinar.');
            appendText(container, 'p', payload.advertencia_temporal, 'analytics-result-note');
            appendText(container, 'p', payload.interpretacion, 'analytics-result-note');
            return;
        }

        if (operation === 'clasificar-nivel') {
            appendText(container, 'strong', 'Nivel estimado: ' + payload.nivel_actividad, 'analytics-result-title');
            const list = document.createElement('ul');
            list.className = 'analytics-probabilities';
            Object.entries(payload.probabilidades || {}).forEach(([label, value]) => {
                appendText(list, 'li', label + ': ' + numberFormat.format(value * 100) + '%');
            });
            container.appendChild(list);
            appendText(container, 'p', payload.interpretacion, 'analytics-result-note');
            return;
        }

        if (operation === 'segmentar-reciclador') {
            appendText(container, 'strong', 'Perfil: ' + payload.segmento, 'analytics-result-title');
            appendText(container, 'p', 'Clúster del modelo: ' + payload.cluster_id + '.');
            appendText(container, 'p', payload.interpretacion, 'analytics-result-note');
            return;
        }

        if (operation === 'evaluar-anomalia') {
            appendText(container, 'strong', payload.estado, 'analytics-result-title');
            appendText(container, 'p', 'Puntuación del detector: ' + numberFormat.format(payload.score_anomalia) + '.');
            appendText(container, 'p', payload.interpretacion, 'analytics-result-note');
            return;
        }

        if (operation === 'reglas-asociacion') {
            appendText(container, 'p', 'Reglas encontradas: ' + payload.cantidad + '. ' + payload.fuente, 'analytics-result-note');
            if (!Array.isArray(payload.reglas) || payload.reglas.length === 0) {
                appendText(container, 'p', 'No hay reglas para los filtros indicados.');
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'analytics-table-wrap';
            const table = document.createElement('table');
            table.className = 'analytics-table';
            const header = document.createElement('thead');
            const headerRow = document.createElement('tr');
            ['Antecedente', 'Consecuente', 'Soporte', 'Confianza', 'Lift'].forEach((label) => {
                appendText(headerRow, 'th', label);
            });
            header.appendChild(headerRow);
            table.appendChild(header);

            const body = document.createElement('tbody');
            payload.reglas.forEach((rule) => {
                const row = document.createElement('tr');
                appendText(row, 'td', rule.antecedente);
                appendText(row, 'td', rule.consecuente);
                appendText(row, 'td', numberFormat.format(rule.soporte * 100) + '%');
                appendText(row, 'td', numberFormat.format(rule.confianza * 100) + '%');
                appendText(row, 'td', numberFormat.format(rule.lift));
                body.appendChild(row);
            });
            table.appendChild(body);
            wrapper.appendChild(table);
            container.appendChild(wrapper);
        }
    }

    document.querySelectorAll('[data-analytics-form]').forEach((form) => {
        const operation = form.dataset.operation;
        const result = form.querySelector('[data-analytics-result]');
        const button = form.querySelector('button[type="submit"]');
        if (!operation || !result || !button || !configuration.routes[operation]) return;

        if (operation === 'predecir-puntos') {
            const periodField = form.elements.namedItem('periodo_referencia');
            const currentMonth = new Intl.DateTimeFormat('en', {
                year: 'numeric',
                month: '2-digit',
                timeZone: 'America/Bogota'
            }).formatToParts(new Date());
            periodField.value =
                currentMonth.find((part) => part.type === 'year').value + '-' +
                currentMonth.find((part) => part.type === 'month').value;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            result.hidden = false;
            result.classList.remove('is-error');
            result.textContent = 'Consultando el servicio de analítica…';
            button.disabled = true;

            try {
                const formData = new FormData(form);
                const payload = {};
                formData.forEach((value, key) => {
                    if (value === '') return;
                    const field = form.elements.namedItem(key);
                    if (field instanceof HTMLInputElement && field.type === 'number') {
                        payload[key] = field.step === '1' ? Number.parseInt(value, 10) : Number(value);
                    } else {
                        payload[key] = value;
                    }
                });

                if (operation === 'predecir-puntos') {
                    payload.mes_num = Number(payload.periodo_referencia.slice(5, 7));
                }

                let url = configuration.routes[operation];
                const options = { headers: { Accept: 'application/json' } };
                if (operation === 'reglas-asociacion') {
                    const query = new URLSearchParams(payload);
                    if (query.toString()) url += '&' + query.toString();
                } else {
                    options.method = 'POST';
                    options.headers['Content-Type'] = 'application/json';
                    options.headers['X-CSRF-Token'] = configuration.csrfToken;
                    options.body = JSON.stringify(payload);
                }

                const response = await fetch(url, options);
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'No fue posible completar la consulta.');
                }
                renderResult(result, data, operation);
            } catch (error) {
                result.replaceChildren();
                result.classList.add('is-error');
                appendText(result, 'p', error instanceof Error ? error.message : 'No fue posible completar la consulta.');
            } finally {
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('[data-recycler-analysis]').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('tr');
            const resultRow = row ? row.nextElementSibling : null;
            const result = resultRow ? resultRow.querySelector('[data-recycler-result]') : null;
            if (!result || !configuration.recyclerAnalysisRoute) return;

            resultRow.hidden = false;
            result.replaceChildren();
            appendText(result, 'p', 'Consultando el último periodo observado y el historial…');
            button.disabled = true;

            try {
                const response = await fetch(configuration.recyclerAnalysisRoute, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': configuration.csrfToken
                    },
                    body: JSON.stringify({
                        reciclador_id: Number.parseInt(button.dataset.recyclerId, 10)
                    })
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'No fue posible analizar este reciclador.');
                }

                result.replaceChildren();
                appendText(result, 'strong', 'Análisis del periodo observado: ' + data.periodo_observado, 'analytics-result-title');
                const modelGrid = document.createElement('div');
                modelGrid.className = 'recycler-model-grid';

                const classification = document.createElement('section');
                appendText(classification, 'strong', 'Clasificación del periodo');
                appendText(classification, 'p', data.clasificacion.nivel_actividad);
                modelGrid.appendChild(classification);

                const prediction = document.createElement('section');
                appendText(prediction, 'strong', 'Estimación orientativa');
                appendText(prediction, 'p', numberFormat.format(data.prediccion.ecopuntos_estimados) + ' Ecopoints');
                appendText(
                    prediction,
                    'small',
                    'Horizonte: siguiente observación disponible. El periodo calendario objetivo aún no se puede determinar.'
                );
                modelGrid.appendChild(prediction);

                const segment = document.createElement('section');
                appendText(segment, 'strong', 'Segmento histórico');
                appendText(segment, 'p', data.segmentacion.segmento);
                appendText(
                    segment,
                    'small',
                    'Calculado con promedios de ' + data.periodos_historia + ' periodos mensuales disponibles.'
                );
                modelGrid.appendChild(segment);

                const anomaly = document.createElement('section');
                appendText(anomaly, 'strong', 'Revisión de anomalías');
                appendText(anomaly, 'p', data.anomalia.estado);
                appendText(anomaly, 'small', data.anomalia.interpretacion);
                modelGrid.appendChild(anomaly);

                result.appendChild(modelGrid);
                appendText(result, 'strong', 'Orientaciones basadas en tu actividad');
                const recommendations = document.createElement('ul');
                recommendations.className = 'recycler-recommendations';
                data.recomendaciones.forEach((recommendation) => {
                    appendText(recommendations, 'li', recommendation);
                });
                result.appendChild(recommendations);
            } catch (error) {
                result.replaceChildren();
                result.classList.add('is-error');
                appendText(result, 'p', error instanceof Error ? error.message : 'No fue posible analizar este reciclador.');
            } finally {
                button.disabled = false;
            }
        });
    });

    const chartsButton = document.querySelector('[data-load-model-charts]');
    if (chartsButton) {
        chartsButton.addEventListener('click', () => loadModelCharts(chartsButton));
        loadModelCharts(chartsButton);
    }
})();
