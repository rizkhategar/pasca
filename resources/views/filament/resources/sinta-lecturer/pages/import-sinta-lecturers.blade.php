<x-filament-panels::page>
    <style>
        .fi-page-header-actions {
            display: none !important;
        }
    </style>

    {{ $this->form }}

    <x-filament-actions::modals />

    <script>
        (() => {
            if (window.__sintaQueuedFetchProgressCleanup) {
                window.__sintaQueuedFetchProgressCleanup();
            }

            const statusUrl = @js(route('scrap.sintaFetchBatches.status'));
            const pollIntervalMs = 3000;
            let intervalId = null;
            let lastBatchId = null;
            let lastCurrentKey = null;
            let lastProgressLine = null;
            let lastFinalStatusKey = null;
            let isPolling = false;
            const emittedDoneKeys = new Set();

            const outputBox = () => document.getElementById('output-box');
            const terminalContainer = () => document.getElementById('terminal-container');
            const fetchAllButton = () => document.getElementById('btn-fetch-all-details');

            const appendTerminal = (text) => {
                const output = outputBox();
                const container = terminalContainer();

                if (! output) {
                    return;
                }

                output.innerHTML += text;

                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            };

            const normalize = (value) => String(value || '').trim();

            const setFetchAllButtonProcessing = (isProcessing) => {
                const button = fetchAllButton();

                if (! button) {
                    return;
                }

                if (! button.dataset.originalText) {
                    button.dataset.originalText = button.innerText || 'Fetch All Registered Lecturers';
                }

                button.disabled = isProcessing;
                button.innerText = isProcessing ? '⏳ Fetch All sedang berjalan...' : button.dataset.originalText;
                button.style.opacity = isProcessing ? '0.5' : '1';
                button.style.cursor = isProcessing ? 'not-allowed' : 'pointer';
            };

            const progressText = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const processed = Number(batch.processed_items || 0);
                const total = Number(batch.total_items || 0);
                const success = Number(fetchCounts.success || 0);
                const warning = Number(fetchCounts.success_with_warning || 0);
                const failed = Number(fetchCounts.failed || 0);
                const pending = Number(fetchCounts.pending || 0);
                const processing = Number(fetchCounts.processing || 0);

                return `[PROGRESS] ${processed}/${total} processed | success: ${success} | warning: ${warning} | failed: ${failed} | pending: ${pending} | running: ${processing}`;
            };

            const isFetchActive = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const pending = Number(fetchCounts.pending || 0);
                const processing = Number(fetchCounts.processing || 0);
                const status = normalize(batch.status);

                if (payload.is_fetch_active || batch.is_fetch_active) {
                    return true;
                }

                if (['completed', 'paused', 'failed', 'cancelled'].includes(status)) {
                    return false;
                }

                return pending > 0 || processing > 0 || ['queued', 'running'].includes(status);
            };

            const appendCurrentItem = (item) => {
                if (! item || item.status !== 'processing') {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.started_at || ''}`;

                if (key === lastCurrentKey) {
                    return;
                }

                lastCurrentKey = key;
                appendTerminal(`[RUNNING] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} sedang diproses...\n`);
            };

            const appendDoneItem = (item) => {
                if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                if (emittedDoneKeys.has(key)) {
                    return;
                }

                emittedDoneKeys.add(key);

                if (item.status === 'failed') {
                    appendTerminal(`[FAILED] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} gagal. ${normalize(item.error_message)}\n`);
                    return;
                }

                const statusLabel = item.status === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
                const outputFile = item.output_file || `merged_data_${item.sinta_id}.xlsx`;
                appendTerminal(`[${statusLabel}] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} selesai. Output: ${outputFile}\n`);
            };

            const appendDoneHistory = (payload) => {
                const recentItems = Array.isArray(payload.recent_fetch_items) ? payload.recent_fetch_items : [];

                if (recentItems.length > 0) {
                    recentItems.forEach(appendDoneItem);
                    return;
                }

                appendDoneItem(payload.latest_fetch_item);
            };

            const appendFinalStatus = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const status = normalize(batch.status) || 'unknown';
                const key = `${batch.id}:${status}:${batch.finished_at || ''}:${fetchCounts.failed || 0}:${fetchCounts.pending || 0}:${fetchCounts.processing || 0}`;

                if (key === lastFinalStatusKey) {
                    return;
                }

                lastFinalStatusKey = key;

                if (status === 'paused' || Number(fetchCounts.failed || 0) > 0) {
                    appendTerminal(`[PAUSED] Fetch All berhenti karena ada item gagal. Failed: ${fetchCounts.failed || 0}. Gunakan Retry Failed / Resume setelah dicek.\n`);
                    return;
                }

                if (status === 'cancelled') {
                    appendTerminal('[CANCELLED] Fetch All batch dibatalkan.\n');
                    return;
                }

                appendTerminal(`[FINISHED] Fetch All selesai. Success: ${fetchCounts.success || 0}, Warning: ${fetchCounts.success_with_warning || 0}, Failed: ${fetchCounts.failed || 0}.\n`);
            };

            const resetTrackingForBatch = (batchId) => {
                if (lastBatchId === batchId) {
                    return;
                }

                lastBatchId = batchId;
                lastCurrentKey = null;
                lastProgressLine = null;
                lastFinalStatusKey = null;
                emittedDoneKeys.clear();
                appendTerminal(`[BATCH] Monitoring Fetch All batch #${batchId}.\n`);
            };

            const handleStatusPayload = (payload) => {
                if (! payload.batch) {
                    setFetchAllButtonProcessing(false);
                    return false;
                }

                resetTrackingForBatch(payload.batch.id);
                appendDoneHistory(payload);
                appendCurrentItem(payload.current_fetch_item);

                const currentProgress = progressText(payload);

                if (currentProgress !== lastProgressLine) {
                    lastProgressLine = currentProgress;
                    appendTerminal(`${currentProgress}\n`);
                }

                if (isFetchActive(payload)) {
                    setFetchAllButtonProcessing(true);
                    return true;
                }

                appendFinalStatus(payload);
                setFetchAllButtonProcessing(false);
                return false;
            };

            const readStatus = async () => {
                const response = await fetch(statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            };

            const pollStatus = async () => {
                try {
                    const payload = await readStatus();
                    const keepPolling = handleStatusPayload(payload);

                    if (! keepPolling) {
                        stopPolling();
                    }
                } catch (error) {
                    appendTerminal(`[POLLING ERROR] ${error.message}\n`);
                    setFetchAllButtonProcessing(false);
                    stopPolling();
                }
            };

            const startPolling = (label = '[POLLING] Monitoring background Fetch All progress setiap 3 detik...') => {
                stopPolling(false);
                isPolling = true;
                appendTerminal(`\n${label}\n`);
                pollStatus();
                intervalId = window.setInterval(pollStatus, pollIntervalMs);
            };

            function stopPolling(resetState = true) {
                if (intervalId) {
                    window.clearInterval(intervalId);
                    intervalId = null;
                }

                if (resetState) {
                    isPolling = false;
                }
            }

            const resumeIfQueueIsRunning = async () => {
                try {
                    const payload = await readStatus();

                    if (handleStatusPayload(payload)) {
                        if (! isPolling) {
                            isPolling = true;
                            appendTerminal('\n[RESUME] Queue Fetch All masih berjalan. Terminal melanjutkan monitoring otomatis...\n');
                            intervalId = window.setInterval(pollStatus, pollIntervalMs);
                        }
                    }
                } catch (error) {
                    setFetchAllButtonProcessing(false);
                }
            };

            const clickHandler = (event) => {
                if (! event.target.closest('#btn-fetch-all-details')) {
                    return;
                }

                setFetchAllButtonProcessing(true);
                window.setTimeout(() => startPolling(), 1500);
            };

            document.addEventListener('click', clickHandler);
            window.setTimeout(resumeIfQueueIsRunning, 1000);

            window.__sintaQueuedFetchProgressCleanup = () => {
                stopPolling();
                document.removeEventListener('click', clickHandler);
            };
        })();
    </script>
</x-filament-panels::page>
