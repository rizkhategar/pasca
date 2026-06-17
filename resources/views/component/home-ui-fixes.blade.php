<style>
    /* Garis hover berita dibuat satu garis utuh, bukan border card yang terlihat terpotong. */
    .info-section .news-item,
    .news-area .news-item,
    .news-list .news-item {
        overflow: visible !important;
    }

    .info-section .news-item-link,
    .news-area .news-item-link,
    .news-list .news-item-link {
        position: relative !important;
        overflow: hidden !important;
        border-color: rgba(7, 43, 87, .10) !important;
        outline: 0 !important;
        background-clip: padding-box !important;
    }

    .info-section .news-item-link:hover,
    .news-area .news-item-link:hover,
    .news-list .news-item-link:hover {
        border-color: rgba(7, 43, 87, .10) !important;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .10), 0 -4px 0 #f7b500 !important;
    }

    .info-section .news-item-link::before,
    .news-area .news-item-link::before,
    .news-list .news-item-link::before {
        display: none !important;
        content: none !important;
    }

    .info-section .news-item-link::after,
    .news-area .news-item-link::after,
    .news-list .news-item-link::after {
        content: "" !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 4px !important;
        border-radius: 16px 16px 0 0 !important;
        background: #f7b500 !important;
        opacity: 0 !important;
        transform: none !important;
        pointer-events: none !important;
        z-index: 20 !important;
        transition: opacity .18s ease !important;
    }

    .info-section .news-item-link:hover::after,
    .news-area .news-item-link:hover::after,
    .news-list .news-item-link:hover::after {
        opacity: 1 !important;
    }

    /* Menu Layanan Mahasiswa: lebih fit dan tetap 2 kolom di mobile. */
    .service-area,
    .service-grid,
    .service-card,
    .edom-card-wrapper {
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .service-grid {
        width: 100% !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .service-card {
        aspect-ratio: 1 / .82 !important;
        min-height: 118px !important;
    }

    .service-card span {
        max-width: 100% !important;
        overflow-wrap: anywhere !important;
    }

    @media (max-width: 992px) {
        html,
        body {
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        .container,
        .footer .container,
        .info-section .container {
            width: min(100% - 28px, 1120px) !important;
            max-width: calc(100vw - 28px) !important;
        }

        .info-layout {
            width: 100% !important;
            max-width: 100% !important;
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .news-area,
        .service-area {
            width: 100% !important;
            max-width: 100% !important;
            padding-right: 0 !important;
            border-right: 0 !important;
        }

        .service-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .service-card,
        .edom-card-wrapper .service-card {
            width: 100% !important;
            min-width: 0 !important;
            min-height: 108px !important;
            aspect-ratio: 1 / .86 !important;
            padding: 12px 7px !important;
            border-radius: 18px !important;
        }

        .service-card svg {
            width: 32px !important;
            height: 32px !important;
            padding: 7px !important;
        }

        .service-card span {
            font-size: 10.5px !important;
            line-height: 1.14 !important;
            letter-spacing: -.03em !important;
        }
    }

    @media (max-width: 420px) {
        .container,
        .footer .container,
        .info-section .container {
            width: min(100% - 24px, 1120px) !important;
            max-width: calc(100vw - 24px) !important;
        }

        .service-grid {
            gap: 10px !important;
        }

        .service-card,
        .edom-card-wrapper .service-card {
            min-height: 100px !important;
            border-radius: 16px !important;
            padding: 10px 6px !important;
        }

        .service-card[data-service-url]::after {
            top: 8px !important;
            right: 8px !important;
            width: 20px !important;
            height: 20px !important;
            font-size: 11px !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serviceCards = Array.from(document.querySelectorAll('.service-grid .service-card'));
        let learningIndex = 0;

        serviceCards.forEach((card) => {
            const labelElement = card.querySelector('span');
            const rawLabel = (labelElement?.innerText || card.innerText || '').replace(/\s+/g, ' ').trim().toLowerCase();
            let url = null;
            let label = null;

            if (rawLabel.includes('e-learning')) {
                learningIndex += 1;

                if (learningIndex === 1) {
                    label = 'SIAKAD';
                    url = 'https://siakad.unw.ac.id';
                } else {
                    label = 'SIPOLIN';
                    url = 'https://sipolin.unw.ac.id';
                }
            }

            if (rawLabel.includes('perpustakaan')) {
                label = 'PERPUSTAKAAN<br>DIGITAL';
                url = 'https://play.google.com/store/apps/details?id=id.kubuku.kbk50635ea&hl=id&pli=1';
            }

            if (! url) return;

            if (labelElement && label) {
                labelElement.innerHTML = label;
            }

            card.dataset.serviceUrl = url;
            card.setAttribute('role', 'link');
            card.setAttribute('tabindex', '0');
            card.setAttribute('aria-label', `Buka ${labelElement?.innerText || label || rawLabel}`);
        });

        const openLinkedCard = function (event) {
            const card = event.target.closest('.service-card[data-service-url]');
            if (! card) return;

            event.preventDefault();
            event.stopImmediatePropagation();
            window.open(card.dataset.serviceUrl, '_blank', 'noopener,noreferrer');
        };

        document.addEventListener('click', openLinkedCard, true);
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;

            const card = event.target.closest('.service-card[data-service-url]');
            if (! card) return;

            event.preventDefault();
            event.stopImmediatePropagation();
            window.open(card.dataset.serviceUrl, '_blank', 'noopener,noreferrer');
        }, true);
    });
</script>
