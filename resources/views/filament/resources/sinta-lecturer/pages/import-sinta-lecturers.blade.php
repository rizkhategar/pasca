<x-filament-panels::page>
    <style>
        .fi-page-header-actions {
            display: none !important;
        }

        #btn-set-timer-fetch-all,
        button[wire\:click="mountAction('setTimerFetchAll')"] {
            display: none !important;
        }
    </style>

    {{ $this->form }}

    <x-filament-actions::modals />

    <script>
        (() => {
            if (window.__sintaFetchAllQueueTerminalCleanup) {
                window.__sintaFetchAllQueueTerminalCleanup();
            }

            const statusUrl = @js(route('scrap.sintaFetchBatches.status'));
            const pollIntervalMs = 3000;
            const midnightWatchIntervalMs = 10000;
            let intervalId = null;
            let midnightIntervalId = null;
            let isPolling = false;
            let lastBatchId = null;
            let lastRunningKey = null;
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

                output.appendChild(document.createTextNode(text));

                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            };

            const normalize = (value) => String(value || '').trim();

            const hideTimerButton = () => {
                document.querySelectorAll('button').forEach((button) => {
                    if (normalize(button.innerText).toLowerCase() === 'set timer fetch all') {
                        button.style.display = 'none';
                    }
                });
            };

            const setFetchAllButtonProcessing = (isProcessing) => {
                const button = fetchAllButton();

                if (! button) {
                    return;
                }

                if (! button.dataset.originalText) {
                    button.dataset.originalText = button.innerText || 'Fetch All / Lanjutkan Otomatis';
                }

                button.disabled = isProcessing;
                button.innerText = isProcessing ? '⏳ Fetch All sedang berjalan...' : button.dataset.originalText;
                button.style.opacity = isProcessing ? '0.5' : '1';
                button.style.cursor = isProcessing ? 'not-allowed' : 'pointer';
            };

            const resetForBatch = (batchId, shouldPrint = true) => {
                if (lastBatchId === batchId) {
                    return;
                }

                lastBatchId = batchId;
                lastRunningKey = null;
                emittedDoneKeys.clear();

                if (shouldPrint) {
                    appendTerminal(`[QUEUE] Monitoring Fetch All batch #${batchId}\n`);
                }
            };

            const isFetchActive = (payload) => {
                const batch = payload.batch || {};
                const fetchCounts = payload.fetch_counts || {};
                const status = normalize(batch.status);
                const pending = Number(fetchCounts.pending || 0);
                const processing = Number(fetchCounts.processing || 0);

                if (payload.is_fetch_active || batch.is_fetch_active) {
                    return true;
                }

                if (['completed', 'paused', 'failed', 'cancelled'].includes(status)) {
                    return false;
                }

                return pending > 0 || processing > 0 || ['queued', 'running'].includes(status);
            };

            const appendRunning = (item) => {
                if (! item || item.status !== 'processing') {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.started_at || ''}`;

                if (key === lastRunningKey) {
                    return;
                }

                lastRunningKey = key;
                appendTerminal(`[RUN] SINTA ID ${item.sinta_id} - ${normalize(item.lecturer_name) || '-'} run\n`);
            };

            const appendDone = (item) => {
                if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                    return;
                }

                const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                if (emittedDoneKeys.has(key)) {
                    return;
                }

                emittedDoneKeys.add(key);

                const name = normalize(item.lecturer_name) || '-';

                if (item.status === 'failed') {
                    appendTerminal(`[FAILED] SINTA ID ${item.sinta_id} - ${name}. ${normalize(item.error_message)}\n`);
                    return;
                }

                const outputFile = item.output_file || `merged_data_${item.sinta_id}.xlsx`;
                const label = item.status === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
                appendTerminal(`[${label}] SINTA ID ${item.sinta_id} - ${name}. File made: ${outputFile}\n`);
            };

            const appendRecentDone = (payload) => {
                const recentItems = Array.isArray(payload.recent_fetch_items) ? payload.recent_fetch_items : [];

                if (recentItems.length > 0) {
                    recentItems.forEach(appendDone);
                    return;
                }

                appendDone(payload.latest_fetch_item);
            };

            const primeRecentDoneAsSeen = (payload) => {
                const recentItems = Array.isArray(payload.recent_fetch_items) ? payload.recent_fetch_items : [];

                recentItems.forEach((item) => {
                    if (! item || ! ['success', 'success_with_warning', 'failed'].includes(item.status)) {
                        return;
                    }

                    emittedDoneKeys.add(`${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`);
                });
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

            const handlePayload = (payload, options = {}) => {
                if (! payload.batch) {
                    setFetchAllButtonProcessing(false);
                    return false;
                }

                resetForBatch(payload.batch.id, options.printBatch !== false);

                if (options.primeOnly) {
                    primeRecentDoneAsSeen(payload);
                } else {
                    appendRecentDone(payload);
                }

                appendRunning(payload.current_fetch_item);

                if (isFetchActive(payload)) {
                    setFetchAllButtonProcessing(true);
                    return true;
                }

                const status = normalize(payload.batch.status);
                setFetchAllButtonProcessing(false);

                if (status === 'completed') {
                    appendTerminal('[DONE] Fetch All queue selesai. Semua data selesai diproses.\n');
                } else if (status === 'paused') {
                    appendTerminal('[PAUSED] Fetch All berhenti karena ada item gagal. Gunakan Resume atau Retry Failed.\n');
                } else if (status === 'cancelled') {
                    appendTerminal('[CANCELLED] Fetch All batch dibatalkan.\n');
                }

                return false;
            };

            const stopPolling = () => {
                if (intervalId) {
                    window.clearInterval(intervalId);
                    intervalId = null;
                }

                isPolling = false;
            };

            const pollStatus = async () => {
                try {
                    const payload = await readStatus();
                    const keepPolling = handlePayload(payload);

                    if (! keepPolling) {
                        stopPolling();
                    }
                } catch (error) {
                    appendTerminal(`[POLLING ERROR] ${error.message}\n`);
                    setFetchAllButtonProcessing(false);
                    stopPolling();
                }
            };

            const startPolling = (label = '[QUEUE] Fetch All berjalan di background. Terminal hanya menampilkan RUN dan DONE.\n') => {
                stopPolling();
                isPolling = true;
                appendTerminal(label);
                pollStatus();
                intervalId = window.setInterval(pollStatus, pollIntervalMs);
            };

            const resumeIfActive = async () => {
                try {
                    const payload = await readStatus();

                    if (isFetchActive(payload)) {
                        handlePayload(payload, { primeOnly: true, printBatch: true });

                        if (! isPolling) {
                            isPolling = true;
                            appendTerminal('[RESUME] Queue Fetch All masih berjalan. Menampilkan RUN/DONE baru mulai sekarang.\n');
                            intervalId = window.setInterval(pollStatus, pollIntervalMs);
                        }
                    }
                } catch (error) {
                    setFetchAllButtonProcessing(false);
                }
            };

            const localDateKey = (date = new Date()) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const isExactlyMidnightMinute = () => {
                const now = new Date();

                return now.getHours() === 0 && now.getMinutes() === 0;
            };

            const triggerMidnightFetchAll = async () => {
                hideTimerButton();

                if (! isExactlyMidnightMinute()) {
                    return;
                }

                const triggerKey = `midnight:${localDateKey()}`;

                if (window.sessionStorage.getItem('sinta-fetch-all-midnight-triggered') === triggerKey) {
                    return;
                }

                const payload = await readStatus().catch(() => null);

                if (payload && isFetchActive(payload)) {
                    window.sessionStorage.setItem('sinta-fetch-all-midnight-triggered', triggerKey);
                    appendTerminal('[MIDNIGHT] Jam 00:00 terdeteksi dan batch Fetch All sudah aktif. Monitoring otomatis dimulai.\n');
                    handlePayload(payload, { primeOnly: true, printBatch: true });

                    if (! isPolling) {
                        startPolling('');
                    }

                    return;
                }

                const button = fetchAllButton();

                if (! button || button.disabled) {
                    return;
                }

                window.sessionStorage.setItem('sinta-fetch-all-midnight-triggered', triggerKey);
                appendTerminal('[MIDNIGHT] Jam 00:00 terdeteksi. Menjalankan Fetch All otomatis.\n');
                button.click();
            };

            const clickHandler = (event) => {
                if (! event.target.closest('#btn-fetch-all-details')) {
                    return;
                }

                setFetchAllButtonProcessing(true);
                window.setTimeout(() => startPolling(), 1200);
            };

            document.addEventListener('click', clickHandler);
            window.setTimeout(hideTimerButton, 250);
            window.setTimeout(resumeIfActive, 1000);
            window.setTimeout(triggerMidnightFetchAll, 1500);
            midnightIntervalId = window.setInterval(triggerMidnightFetchAll, midnightWatchIntervalMs);

            window.__sintaFetchAllQueueTerminalCleanup = () => {
                stopPolling();

                if (midnightIntervalId) {
                    window.clearInterval(midnightIntervalId);
                    midnightIntervalId = null;
                }

                document.removeEventListener('click', clickHandler);
            };
        })();
    </script>
</x-filament-panels::page>
