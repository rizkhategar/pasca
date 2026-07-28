<x-filament-panels::page>
    {{ $this->form }}

    <x-filament-actions::modals />

    <script>
        (() => {
            if (window.__sintaAutomaticFetchImportWatcher) {
                return;
            }

            window.__sintaAutomaticFetchImportWatcher = true;

            const routes = {
                status: @json(route('scrap.sintaFetchBatches.status')),
                automaticRuns: @json(route('scrap.sintaFetchBatches.automaticRuns.latest')),
                studyProgramSettings: @json(route('scrap.sintaFetchBatches.studyProgramSettings')),
            };

            const state = {
                fetchActive: false,
                importActive: false,
                lastFetchRunningKey: null,
                emittedFetchDoneKeys: new Set(),
                emittedImportRunKeys: new Set(),
                emittedImportDoneKeys: new Set(),
                emittedAutomaticLogKeys: new Set(),
                emittedManualFetchDoneKeys: new Set(),
                emittedBatchMessageKeys: new Set(),
            };

            const normalizeText = (value) => String(value || '').trim();

            const terminal = () => document.getElementById('terminal-container');
            const output = () => document.getElementById('output-box');

            const appendTerminal = (text) => {
                const outputBox = output();
                const terminalContainer = terminal();

                if (! outputBox || ! terminalContainer) {
                    return;
                }

                outputBox.appendChild(document.createTextNode(text));
                terminalContainer.scrollTop = terminalContainer.scrollHeight;
            };

            const toggleButton = (selector, active, label) => {
                const button = document.querySelector(selector);

                if (! button) {
                    return;
                }

                button.disabled = active;
                button.innerText = active ? '⏳ Processing...' : label;
                button.style.opacity = active ? '0.5' : '1';
            };

            const getJson = async (url) => {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return response.json();
            };

            const isFetchActive = (payload) => {
                const batch = payload?.batch || {};
                const fetchCounts = payload?.fetch_counts || {};
                const status = normalizeText(batch.status);
                const pending = Number(fetchCounts.pending || 0);
                const processing = Number(fetchCounts.processing || 0);

                if (payload?.is_fetch_active || batch?.is_fetch_active) {
                    return true;
                }

                if (['completed', 'paused', 'failed', 'cancelled'].includes(status)) {
                    return false;
                }

                return pending > 0 || processing > 0 || ['queued', 'running'].includes(status);
            };

            const appendFetchRunLine = (item) => {
                if (! item || item.status !== 'processing') {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.started_at || ''}`;

                if (state.lastFetchRunningKey === key) {
                    return;
                }

                state.lastFetchRunningKey = key;
                appendTerminal('[RUN] SINTA ID ' + item.sinta_id + ' - ' + (normalizeText(item.lecturer_name) || '-') + ' run\n');
            };

            const appendFetchDoneLine = (item) => {
                if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                if (state.emittedFetchDoneKeys.has(key)) {
                    return;
                }

                state.emittedFetchDoneKeys.add(key);
                const name = normalizeText(item.lecturer_name) || '-';

                if (item.status === 'failed') {
                    appendTerminal('[FAILED] SINTA ID ' + item.sinta_id + ' - ' + name + '. ' + normalizeText(item.error_message) + '\n');
                    return;
                }

                const outputFile = item.output_file || ('merged_data_' + item.sinta_id + '.xlsx');
                const label = item.status === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
                appendTerminal('[' + label + '] SINTA ID ' + item.sinta_id + ' - ' + name + '. File made: ' + outputFile + '\n');
            };

            const appendFetchProgress = (payload) => {
                const recentItems = Array.isArray(payload?.recent_fetch_items) ? payload.recent_fetch_items : [];

                if (recentItems.length > 0) {
                    recentItems.forEach(appendFetchDoneLine);
                } else {
                    appendFetchDoneLine(payload?.latest_fetch_item);
                }

                appendFetchRunLine(payload?.current_fetch_item);
            };

            const appendManualFetchProdiStart = (payload) => {
                const batch = payload?.batch || {};
                const status = normalizeText(batch.status);

                if (! batch.id || status !== 'completed') {
                    return;
                }

                const key = `${batch.id}:fetch-done-prodi-sync-started`;

                if (state.emittedManualFetchDoneKeys.has(key)) {
                    return;
                }

                state.emittedManualFetchDoneKeys.add(key);
                appendTerminal('[DONE] Fetch All selesai. Menjalankan pendaftaran study program dosen dari merged Excel ke sinta_lecturer_study_program_settings...\n');
            };

            const appendBatchStatusMessage = (payload) => {
                const batch = payload?.batch || {};
                const message = normalizeText(batch.error_message);

                if (! batch.id || ! message) {
                    return;
                }

                if (message === 'Queued fetch-all job is running in background.') {
                    return;
                }

                const key = `${batch.id}:${message}`;

                if (state.emittedBatchMessageKeys.has(key)) {
                    return;
                }

                state.emittedBatchMessageKeys.add(key);

                const lowerMessage = message.toLowerCase();

                if (lowerMessage.includes('study program settings synced') || lowerMessage.includes('pendaftaran study program')) {
                    appendTerminal('[DONE] Pendaftaran study program dosen selesai. ' + message + '\n');
                    return;
                }

                if (lowerMessage.includes('registering') || lowerMessage.includes('study program') || lowerMessage.includes('sync')) {
                    appendTerminal('[RUN] ' + message + '\n');
                    return;
                }

                if (['completed', 'paused', 'failed', 'cancelled'].includes(normalizeText(batch.status))) {
                    appendTerminal('[INFO] ' + message + '\n');
                }
            };

            const appendAutomaticLogs = async () => {
                const payload = await getJson(routes.automaticRuns);
                const logs = Array.isArray(payload?.logs) ? payload.logs : [];
                const latestLog = logs[0] || null;

                if (! latestLog) {
                    return;
                }

                const message = normalizeText(latestLog?.summary_message);

                if (! message) {
                    return;
                }

                const key = `${latestLog.id}:${message}`;

                if (state.emittedAutomaticLogKeys.has(key)) {
                    return;
                }

                state.emittedAutomaticLogKeys.add(key);
                appendTerminal('[AUTO] ' + message + '\n');
            };

            const appendImportProgress = async () => {
                const payload = await getJson(routes.studyProgramSettings);
                const items = Array.isArray(payload?.items) ? payload.items : [];

                items.forEach((item) => {
                    const status = normalizeText(item.import_status || item.importStatus);
                    const keyBase = `${item.sinta_id || item.id}`;
                    const name = normalizeText(item.lecturer_name) || '-';

                    if (['queued', 'importing', 'processing', 'running'].includes(status)) {
                        if (! state.emittedImportRunKeys.has(keyBase)) {
                            state.emittedImportRunKeys.add(keyBase);
                            appendTerminal('[RUN] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + ' run\n');
                        }

                        return;
                    }

                    if (['imported', 'success'].includes(status)) {
                        const key = keyBase + ':' + status;

                        if (! state.emittedImportDoneKeys.has(key)) {
                            state.emittedImportDoneKeys.add(key);
                            appendTerminal('[DONE] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + ' imported to database\n');
                        }

                        return;
                    }

                    if (['import_failed', 'failed', 'error'].includes(status)) {
                        const key = keyBase + ':' + status;

                        if (! state.emittedImportDoneKeys.has(key)) {
                            state.emittedImportDoneKeys.add(key);
                            appendTerminal('[FAILED] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + '. ' + normalizeText(item.import_error || item.error_message) + '\n');
                        }
                    }
                });
            };

            const tick = async () => {
                try {
                    const statusPayload = await getJson(routes.status);
                    const fetchActive = isFetchActive(statusPayload);
                    const importCounts = statusPayload?.import_counts || {};
                    const importActive = Number(importCounts.queued || 0) > 0 || Number(importCounts.importing || 0) > 0;

                    await appendAutomaticLogs();
                    appendBatchStatusMessage(statusPayload);

                    if (fetchActive) {
                        if (! state.fetchActive) {
                            appendTerminal('[WATCHER] Auto watcher mendeteksi Fetch All sedang berjalan. Menampilkan progress mulai sekarang.\n');
                        }

                        state.fetchActive = true;
                        toggleButton('#btn-fetch-all-details', true, 'Fetch All / Lanjutkan Otomatis');
                        appendFetchProgress(statusPayload);
                    } else if (state.fetchActive) {
                        appendFetchProgress(statusPayload);
                        state.fetchActive = false;
                        toggleButton('#btn-fetch-all-details', false, 'Fetch All / Lanjutkan Otomatis');
                        appendManualFetchProdiStart(statusPayload);
                        appendBatchStatusMessage(statusPayload);
                        appendTerminal('[DONE] Fetch All watcher selesai memantau batch. Manual Fetch All berhenti setelah pendaftaran study program dosen, tidak lanjut Import All.\n');
                    } else {
                        appendManualFetchProdiStart(statusPayload);
                    }

                    if (importActive) {
                        if (! state.importActive) {
                            appendTerminal('[WATCHER] Auto watcher mendeteksi Import All sedang berjalan.\n');
                        }

                        state.importActive = true;
                        toggleButton('#btn-import-all', true, 'Import All to Database');
                        await appendImportProgress();
                    } else if (state.importActive) {
                        state.importActive = false;
                        toggleButton('#btn-import-all', false, 'Import All to Database');
                        await appendImportProgress();
                        appendTerminal('[DONE] Import All watcher selesai memantau batch.\n');
                    }
                } catch (error) {
                    // Jangan ganggu halaman utama jika watcher gagal sementara.
                }
            };

            window.setTimeout(tick, 1500);
            window.setInterval(tick, 5000);
        })();
    </script>
</x-filament-panels::page>
