<x-filament-panels::page>
    {{ $this->form }}

    <x-filament-actions::modals />

    <script>
        (() => {
            if (window.__sintaHardcodedFetchAllCleanup) {
                window.__sintaHardcodedFetchAllCleanup();
            }

            /**
             * HARDCORE / HARDCODE JAM AUTO FETCH + IMPORT ALL.
             * Ubah dua angka di bawah ini untuk testing.
             * Contoh 14:35 => AUTO_FETCH_ALL_HOUR = 14, AUTO_FETCH_ALL_MINUTE = 35.
             *
             * TODO ketika memungkinkan patch PHP besar secara aman:
             * pindahkan dua nilai ini ke app/Filament/Resources/SintaLecturer/Pages/ImportSintaLecturers.php.
             */
            const AUTO_FETCH_ALL_HOUR = 0;
            const AUTO_FETCH_ALL_MINUTE = 0;
            const WATCH_INTERVAL_MS = 5000;
            const STATUS_TEXT_REWRITE_ATTEMPTS = 20;
            let intervalId = null;
            let statusTextIntervalId = null;
            let statusTextRewriteCount = 0;

            const hardcodedTimeLabel = () => {
                return `${String(AUTO_FETCH_ALL_HOUR).padStart(2, '0')}:${String(AUTO_FETCH_ALL_MINUTE).padStart(2, '0')}`;
            };

            const localDateKey = (date = new Date()) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const isHardcodedFetchAllTime = () => {
                const now = new Date();

                return now.getHours() === AUTO_FETCH_ALL_HOUR
                    && now.getMinutes() === AUTO_FETCH_ALL_MINUTE;
            };

            const rewriteAutomaticStatusText = () => {
                const text = `Automatic Fetch and Import All at ${hardcodedTimeLabel()}`;

                document.querySelectorAll('div').forEach((element) => {
                    const content = String(element.textContent || '');

                    if (! content.includes('Automatic Fetch All:')) {
                        return;
                    }

                    element.innerHTML = `<b>${text}</b>`;
                    element.style.padding = '0.75rem';
                    element.style.borderRadius = '0.5rem';
                    element.style.backgroundColor = 'rgba(14,165,233,0.1)';
                    element.style.border = '1px solid rgba(14,165,233,0.2)';
                    element.style.color = '#0369a1';
                    element.style.fontWeight = '500';
                });
            };

            const appendTerminal = (text) => {
                const output = document.getElementById('output-box');
                const terminal = document.getElementById('terminal-container');

                if (! output) {
                    return;
                }

                output.appendChild(document.createTextNode(text));

                if (terminal) {
                    terminal.scrollTop = terminal.scrollHeight;
                }
            };

            const triggerHardcodedFetchAll = () => {
                rewriteAutomaticStatusText();

                if (! isHardcodedFetchAllTime()) {
                    return;
                }

                const triggerKey = `hardcoded-fetch-all:${localDateKey()}:${AUTO_FETCH_ALL_HOUR}:${AUTO_FETCH_ALL_MINUTE}`;

                if (window.sessionStorage.getItem('sinta-hardcoded-fetch-all-triggered') === triggerKey) {
                    return;
                }

                const button = document.getElementById('btn-fetch-all-details');

                if (! button || button.disabled) {
                    return;
                }

                window.sessionStorage.setItem('sinta-hardcoded-fetch-all-triggered', triggerKey);
                appendTerminal(`[HARDCODE] Jam ${hardcodedTimeLabel()} terdeteksi. Menjalankan Fetch All otomatis.\n`);
                button.click();
            };

            const startStatusTextRewrite = () => {
                rewriteAutomaticStatusText();

                statusTextIntervalId = window.setInterval(() => {
                    statusTextRewriteCount += 1;
                    rewriteAutomaticStatusText();

                    if (statusTextRewriteCount >= STATUS_TEXT_REWRITE_ATTEMPTS && statusTextIntervalId) {
                        window.clearInterval(statusTextIntervalId);
                        statusTextIntervalId = null;
                    }
                }, 500);
            };

            startStatusTextRewrite();
            window.setTimeout(triggerHardcodedFetchAll, 1500);
            intervalId = window.setInterval(triggerHardcodedFetchAll, WATCH_INTERVAL_MS);

            window.__sintaHardcodedFetchAllCleanup = () => {
                if (intervalId) {
                    window.clearInterval(intervalId);
                    intervalId = null;
                }

                if (statusTextIntervalId) {
                    window.clearInterval(statusTextIntervalId);
                    statusTextIntervalId = null;
                }
            };
        })();
    </script>
</x-filament-panels::page>
