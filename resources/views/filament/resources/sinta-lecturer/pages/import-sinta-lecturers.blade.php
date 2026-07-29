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
                activeBatchId: null,
                awaitingManualBatch: false,
                manualRequestedAt: 0,
                fetchWasActive: false,
                importActive: false,
                fetchWatcherStartedKeys: new Set(),
                fetchRunKeys: new Set(),
                fetchDoneKeys: new Set(),
                prodiStartKeys: new Set(),
                prodiSummaryKeys: new Set(),
                prodiDetailKeys: new Set(),
                fetchStopKeys: new Set(),
                importRunKeys: new Set(),
                importDoneKeys: new Set(),
                automaticLogKeys: new Set(),
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

                outputBox.appendChild(document.createTextNode(String(text || '')));
                terminalContainer.scrollTop = terminalContainer.scrollHeight;
            };

            const appendTerminalHighlight = (text) => {
                const outputBox = output();
                const terminalContainer = terminal();

                if (! outputBox || ! terminalContainer) {
                    return;
                }

                const line = document.createElement('span');
                line.textContent = String(text || '');
                line.style.backgroundColor = '#4ade80';
                line.style.color = '#000000';
                line.style.padding = '0 0.25rem';
                line.style.borderRadius = '0.25rem';
                line.style.fontWeight = '700';

                outputBox.appendChild(line);
                outputBox.appendChild(document.createTextNode('\n'));
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

            const batchIdOf = (payload) => Number(payload?.batch?.id || 0);

            const parseDateTime = (value) => {
                const text = normalizeText(value);

                if (! text) {
                    return 0;
                }

                const parsed = Date.parse(text.includes('T') ? text : text.replace(' ', 'T'));

                return Number.isFinite(parsed) ? parsed : 0;
            };

            const isFetchActive = (payload) => {
                const batch = payload?.batch || {};
                const counts = payload?.fetch_counts || {};
                const status = normalizeText(batch.status);
                const pending = Number(counts.pending || 0);
                const processing = Number(counts.processing || 0);

                if (payload?.is_fetch_active || batch?.is_fetch_active) {
                    return true;
                }

                if (['completed', 'paused', 'failed', 'cancelled'].includes(status)) {
                    return false;
                }

                return pending > 0 || processing > 0 || ['queued', 'running'].includes(status);
            };

            const isCompleted = (payload) => normalizeText(payload?.batch?.status) === 'completed';

            const isProdiSyncFinished = (payload) => {
                const message = normalizeText(payload?.batch?.error_message).toLowerCase();

                return isCompleted(payload) && message.includes('study program settings synced');
            };

            const bindBatchWhenReady = (payload, fetchActive) => {
                const batchId = batchIdOf(payload);

                if (batchId <= 0) {
                    return false;
                }

                if (state.activeBatchId === batchId) {
                    return true;
                }

                if (fetchActive) {
                    state.activeBatchId = batchId;
                    state.awaitingManualBatch = false;
                    return true;
                }

                if (! state.awaitingManualBatch) {
                    return false;
                }

                const startedAt = parseDateTime(payload?.batch?.started_at);

                // Batch yang sangat cepat dapat selesai sebelum polling pertama. Tetap ikat
                // jika dibuat setelah tombol Fetch All manual diklik.
                if (startedAt > 0 && startedAt >= state.manualRequestedAt - 60000) {
                    state.activeBatchId = batchId;
                    state.awaitingManualBatch = false;
                    return true;
                }

                return false;
            };

            const isBoundBatch = (payload) => {
                const batchId = batchIdOf(payload);

                return batchId > 0 && Number(state.activeBatchId || 0) === batchId;
            };

            const appendFetchRunLine = (item) => {
                if (! item || item.status !== 'processing') {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.started_at || ''}`;

                if (state.fetchRunKeys.has(key)) {
                    return;
                }

                state.fetchRunKeys.add(key);
                appendTerminal('[RUN] SINTA ID ' + item.sinta_id + ' - ' + (normalizeText(item.lecturer_name) || '-') + ' run\n');
            };

            const appendFetchDoneLine = (item) => {
                if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                if (state.fetchDoneKeys.has(key)) {
                    return;
                }

                state.fetchDoneKeys.add(key);
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

            const appendFetchWatcherStart = (payload) => {
                const batchId = batchIdOf(payload);
                const key = `${batchId}:fetch-watcher-start`;

                if (state.fetchWatcherStartedKeys.has(key)) {
                    return;
                }

                state.fetchWatcherStartedKeys.add(key);
                appendTerminal('[WATCHER] Fetch All sedang berjalan. Menampilkan progress scraping sampai selesai.\n');
            };

            const appendProdiStart = (payload) => {
                if (! isCompleted(payload) || ! isBoundBatch(payload)) {
                    return;
                }

                const batchId = batchIdOf(payload);
                const key = `${batchId}:prodi-start`;

                if (state.prodiStartKeys.has(key)) {
                    return;
                }

                state.prodiStartKeys.add(key);
                appendTerminal('[DONE] Fetch All selesai. Menjalankan pendaftaran study program dosen dari merged Excel ke sinta_lecturer_study_program_settings...\n');
            };

            const appendProdiSummary = (payload) => {
                if (! isProdiSyncFinished(payload) || ! isBoundBatch(payload)) {
                    return;
                }

                const batchId = batchIdOf(payload);
                const message = normalizeText(payload?.batch?.error_message);
                const key = `${batchId}:${message}`;

                if (state.prodiSummaryKeys.has(key)) {
                    return;
                }

                state.prodiSummaryKeys.add(key);
                appendTerminal('[DONE] Pendaftaran study program dosen selesai. ' + message + '\n');
            };

            const formatStudyProgramLabel = (program) => {
                if (! program) {
                    return '';
                }

                const level = normalizeText(program.jenjang_nama_singkat || program.jenjang);
                const name = normalizeText(program.nama || program.display_name);

                return normalizeText([level, name].filter(Boolean).join(' '));
            };

            const appendProdiDetailLogs = async (statusPayload) => {
                if (! isProdiSyncFinished(statusPayload) || ! isBoundBatch(statusPayload)) {
                    return 0;
                }

                const batchId = batchIdOf(statusPayload);
                const payload = await getJson(routes.studyProgramSettings);
                const items = Array.isArray(payload?.items) ? payload.items : [];
                const programs = Array.isArray(payload?.programs) ? payload.programs : [];
                const programsById = new Map(programs.map((program) => [Number(program.id), program]));
                let emitted = 0;

                items.forEach((item) => {
                    const sintaId = normalizeText(item.sinta_id);
                    const name = normalizeText(item.lecturer_name) || '-';
                    const fetchStatus = normalizeText(item.fetch_status);

                    if (! sintaId || ! ['success', 'success_with_warning'].includes(fetchStatus)) {
                        return;
                    }

                    const selectedIds = Array.isArray(item.study_program_ids)
                        ? item.study_program_ids.map((id) => Number(id)).filter((id) => id > 0)
                        : [];
                    const detectedStudyProgram = normalizeText(item.detected_study_program);
                    const key = `${batchId}:${sintaId}:${selectedIds.join(',') || 'null'}:${detectedStudyProgram || 'empty'}`;

                    if (state.prodiDetailKeys.has(key)) {
                        return;
                    }

                    state.prodiDetailKeys.add(key);
                    emitted++;

                    if (selectedIds.length > 0) {
                        const labels = selectedIds
                            .map((id) => formatStudyProgramLabel(programsById.get(id)))
                            .filter(Boolean)
                            .join(', ');

                        appendTerminal('[DONE] ' + sintaId + ', ' + name + ', di daftarkan ke prodi [' + (labels || '-') + ']\n');
                        return;
                    }

                    if (! detectedStudyProgram) {
                        appendTerminalHighlight('[WARNING] ' + sintaId + ', ' + name + ', di daftarkan ke prodi [null] karena kolom program studi di Excel kosong.');
                        return;
                    }

                    appendTerminalHighlight('[WARNING] ' + sintaId + ', ' + name + ', di daftarkan ke prodi [null] karena prodi Excel "' + detectedStudyProgram + '" belum cocok dengan study_programs.');
                });

                return emitted;
            };

            const appendFetchStop = (payload) => {
                if (! isProdiSyncFinished(payload) || ! isBoundBatch(payload)) {
                    return;
                }

                const batchId = batchIdOf(payload);
                const key = `${batchId}:fetch-stop`;

                if (state.fetchStopKeys.has(key)) {
                    return;
                }

                state.fetchStopKeys.add(key);
                appendTerminal('[DONE] Manual Fetch All selesai sampai pendaftaran study program dosen dan tidak lanjut Import All.\n');
            };

            const appendAutomaticLogs = async () => {
                const payload = await getJson(routes.automaticRuns);
                const logs = Array.isArray(payload?.logs) ? payload.logs : [];
                const latestLog = logs[0] || null;
                const message = normalizeText(latestLog?.summary_message);

                if (! latestLog || ! message) {
                    return;
                }

                const key = `${latestLog.id}:${message}`;

                if (state.automaticLogKeys.has(key)) {
                    return;
                }

                state.automaticLogKeys.add(key);
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
                        if (! state.importRunKeys.has(keyBase)) {
                            state.importRunKeys.add(keyBase);
                            appendTerminal('[RUN] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + ' run\n');
                        }

                        return;
                    }

                    if (['imported', 'success'].includes(status)) {
                        const key = keyBase + ':' + status;

                        if (! state.importDoneKeys.has(key)) {
                            state.importDoneKeys.add(key);
                            appendTerminal('[DONE] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + ' imported to database\n');
                        }

                        return;
                    }

                    if (['import_failed', 'failed', 'error'].includes(status)) {
                        const key = keyBase + ':' + status;

                        if (! state.importDoneKeys.has(key)) {
                            state.importDoneKeys.add(key);
                            appendTerminal('[FAILED] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + '. ' + normalizeText(item.import_error || item.error_message) + '\n');
                        }
                    }
                });
            };

            document.addEventListener('click', (event) => {
                if (! event.target.closest('#btn-fetch-all-details')) {
                    return;
                }

                state.awaitingManualBatch = true;
                state.manualRequestedAt = Date.now();
                state.activeBatchId = null;
                state.fetchWasActive = false;
            }, true);

            const tick = async () => {
                try {
                    const statusPayload = await getJson(routes.status);
                    const fetchActive = isFetchActive(statusPayload);
                    const importCounts = statusPayload?.import_counts || {};
                    const importActive = Number(importCounts.queued || 0) > 0
                        || Number(importCounts.importing || 0) > 0;

                    await appendAutomaticLogs();

                    const batchBound = bindBatchWhenReady(statusPayload, fetchActive);

                    if (batchBound && isBoundBatch(statusPayload)) {
                        if (fetchActive) {
                            appendFetchWatcherStart(statusPayload);
                            state.fetchWasActive = true;
                            toggleButton('#btn-fetch-all-details', true, 'Fetch All / Lanjutkan Otomatis');
                            appendFetchProgress(statusPayload);
                        } else {
                            // Selalu baca status final item scraping lebih dulu agar [DONE] file
                            // terakhir tidak terlewat sebelum masuk tahap prodi.
                            appendFetchProgress(statusPayload);

                            if (state.fetchWasActive) {
                                state.fetchWasActive = false;
                                toggleButton('#btn-fetch-all-details', false, 'Fetch All / Lanjutkan Otomatis');
                            }

                            if (isCompleted(statusPayload)) {
                                appendProdiStart(statusPayload);

                                if (isProdiSyncFinished(statusPayload)) {
                                    appendProdiSummary(statusPayload);
                                    const emitted = await appendProdiDetailLogs(statusPayload);

                                    if (emitted > 0) {
                                        appendFetchStop(statusPayload);
                                    }
                                }
                            }
                        }
                    }

                    if (importActive) {
                        if (! state.importActive) {
                            appendTerminal('[WATCHER] Import All sedang berjalan.\n');
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
                    // Polling berikutnya akan mencoba kembali tanpa mengganggu halaman utama.
                }
            };

            window.setTimeout(tick, 1000);
            window.setInterval(tick, 3000);
        })();
    </script>
</x-filament-panels::page>
