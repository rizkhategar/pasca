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
            let lastLatestDoneKey = null;
            let lastProgressLine = null;
            let lastFinalStatusKey = null;

            const outputBox = () => document.getElementById('output-box');
            const terminalContainer = () => document.getElementById('terminal-container');

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

            const appendCurrentItem = (item) => {
                if (! item || item.status !== 'processing') {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}`;

                if (key === lastCurrentKey) {
                    return;
                }

                lastCurrentKey = key;
                appendTerminal(`[RUNNING] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} sedang diproses...\n`);
            };

            const appendLatestDoneItem = (item) => {
                if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                if (key === lastLatestDoneKey) {
                    return;
                }

                lastLatestDoneKey = key;

                if (item.status === 'failed') {
                    appendTerminal(`[FAILED] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} gagal. ${normalize(item.error_message)}\n`);
                    return;
                }

                const statusLabel = item.status === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
                const outputFile = item.output_file || `merged_data_${item.sinta_id}.xlsx`;
                appendTerminal(`[${statusLabel}] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} selesai. Output: ${outputFile}\n`);
            };

            const shouldStopPolling = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const pending = Number(fetchCounts.pending || 0);
                const processing = Number(fetchCounts.processing || 0);
                const status = normalize(batch.status);

                return ['completed', 'paused', 'failed', 'cancelled'].includes(status) || (pending === 0 && processing === 0 && Number(batch.total_items || 0) > 0);
            };

            const appendFinalStatus = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const status = normalize(batch.status) || 'unknown';
                const key = `${batch.id}:${status}:${batch.finished_at || ''}:${fetchCounts.failed || 0}`;

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

            const pollStatus = async () => {
                try {
                    const response = await fetch(statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        appendTerminal(`[POLLING ERROR] Gagal membaca status batch. HTTP ${response.status}.\n`);
                        stopPolling();
                        return;
                    }

                    const payload = await response.json();

                    if (! payload.batch) {
                        appendTerminal('[POLLING] Belum ada batch Fetch All yang berjalan.\n');
                        return;
                    }

                    if (lastBatchId !== payload.batch.id) {
                        lastBatchId = payload.batch.id;
                        lastCurrentKey = null;
                        lastLatestDoneKey = null;
                        lastProgressLine = null;
                        lastFinalStatusKey = null;
                        appendTerminal(`[BATCH] Monitoring Fetch All batch #${payload.batch.id}.\n`);
                    }

                    appendCurrentItem(payload.current_fetch_item);
                    appendLatestDoneItem(payload.latest_fetch_item);

                    const currentProgress = progressText(payload);

                    if (currentProgress !== lastProgressLine) {
                        lastProgressLine = currentProgress;
                        appendTerminal(`${currentProgress}\n`);
                    }

                    if (shouldStopPolling(payload)) {
                        appendFinalStatus(payload);
                        stopPolling();
                    }
                } catch (error) {
                    appendTerminal(`[POLLING ERROR] ${error.message}\n`);
                    stopPolling();
                }
            };

            const startPolling = () => {
                stopPolling();
                lastBatchId = null;
                lastCurrentKey = null;
                lastLatestDoneKey = null;
                lastProgressLine = null;
                lastFinalStatusKey = null;
                appendTerminal('\n[POLLING] Monitoring background Fetch All progress setiap 3 detik...\n');
                pollStatus();
                intervalId = window.setInterval(pollStatus, pollIntervalMs);
            };

            function stopPolling() {
                if (intervalId) {
                    window.clearInterval(intervalId);
                    intervalId = null;
                }
            }

            const clickHandler = (event) => {
                if (! event.target.closest('#btn-fetch-all-details')) {
                    return;
                }

                window.setTimeout(startPolling, 1500);
            };

            document.addEventListener('click', clickHandler);

            window.__sintaQueuedFetchProgressCleanup = () => {
                stopPolling();
                document.removeEventListener('click', clickHandler);
            };
        })();
    </script>
</x-filament-panels::page>
