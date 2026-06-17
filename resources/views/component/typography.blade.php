<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    :root {
        --font-sans: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    }

    html {
        font-size: 16px;
        -webkit-text-size-adjust: 100%;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    body,
    button,
    input,
    textarea,
    select {
        font-family: var(--font-sans) !important;
    }

    body :where(*):not(i):not(.fa):not(.fas):not(.far):not(.fab):not([class^='fa-']):not([class*=' fa-']) {
        font-family: var(--font-sans) !important;
    }

    body {
        font-size: 16px;
        line-height: 1.65;
        letter-spacing: -0.01em;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .hero-title,
    .page-title,
    .section-title,
    .program-title,
    .news-title,
    .news-page-title,
    .contact-title,
    #siteHeader .brand-main,
    #siteHeader .brand-school {
        letter-spacing: -0.035em;
        line-height: 1.16;
        font-weight: 800;
    }

    p,
    li,
    .page-desc,
    .hero-subtitle,
    .news-excerpt,
    .news-page-excerpt,
    .contact-subtitle,
    .contact-info p,
    .contact-info a,
    .footer-item,
    .footer-links a {
        line-height: 1.65;
    }

    #siteHeader,
    #siteHeader .brand-main,
    #siteHeader .brand-sub,
    #siteHeader .brand-school,
    #siteHeader .nav-link,
    #siteHeader .dropdown a {
        font-family: var(--font-sans) !important;
    }

    .about-points .point-icon {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        color: #062f5f !important;
        border: 1px solid rgba(6, 47, 95, .12) !important;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .08) !important;
    }

    .about-points .point-icon img {
        width: 34px !important;
        height: 34px !important;
        object-fit: contain !important;
        filter: none !important;
        mix-blend-mode: normal !important;
    }

    .about-points .point-icon i {
        color: #062f5f !important;
    }

    .news-list .news-content,
    .info-section .news-content,
    .news-area .news-content {
        justify-content: center !important;
        gap: 0 !important;
    }

    .news-list .news-category,
    .info-section .news-category,
    .news-area .news-category {
        margin: 0 0 5px !important;
        line-height: 1.22 !important;
    }

    .news-list .news-title,
    .info-section .news-title,
    .news-area .news-title {
        margin: 0 !important;
        line-height: 1.26 !important;
        -webkit-line-clamp: 2 !important;
    }

    .news-list .news-excerpt,
    .info-section .news-excerpt,
    .news-area .news-excerpt {
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        margin: 6px 0 0 !important;
        line-height: 1.42 !important;
    }

    .news-list .news-date,
    .info-section .news-date,
    .news-area .news-date {
        margin-top: 8px !important;
        line-height: 1.2 !important;
    }

    .news-area .news-item-link,
    .info-section .news-item-link,
    .news-list .news-item-link {
        position: relative !important;
        overflow: hidden !important;
    }

    .news-area .news-item-link::before,
    .info-section .news-item-link::before,
    .news-list .news-item-link::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 4px;
        border-radius: 16px 16px 0 0;
        background: linear-gradient(90deg, #f7b500 0%, #ffc928 50%, #f7b500 100%);
        opacity: 0;
        transform: scaleX(0);
        transform-origin: left center;
        transition: opacity .24s ease, transform .24s ease;
        pointer-events: none;
        z-index: 5;
    }

    .news-area .news-item-link:hover::before,
    .info-section .news-item-link:hover::before,
    .news-list .news-item-link:hover::before {
        opacity: 1;
        transform: scaleX(1);
    }

    .service-area {
        position: sticky;
        top: 92px;
    }

    .service-title {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px !important;
        color: var(--primary) !important;
        letter-spacing: -0.04em;
    }

    .service-title::before {
        content: "";
        width: 12px;
        height: 28px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--yellow), #ffd45a);
        box-shadow: 0 10px 22px rgba(247, 181, 0, .25);
    }

    .service-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 16px !important;
    }

    .service-card {
        position: relative !important;
        min-height: 132px !important;
        width: 100% !important;
        overflow: hidden !important;
        isolation: isolate !important;
        border: 1px solid rgba(7, 43, 87, .10) !important;
        border-radius: 22px !important;
        background:
            radial-gradient(circle at 80% 12%, rgba(247, 181, 0, .16), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        color: var(--primary) !important;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .08) !important;
        padding: 18px 12px !important;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease, color .25s ease !important;
    }

    .service-card::before {
        content: "";
        position: absolute;
        inset: auto -28px -46px auto;
        width: 104px;
        height: 104px;
        border-radius: 50%;
        background: rgba(7, 43, 87, .055);
        transition: transform .25s ease, background .25s ease;
        z-index: -1;
    }

    .service-card svg {
        width: 38px !important;
        height: 38px !important;
        padding: 8px;
        border-radius: 16px;
        background: rgba(247, 181, 0, .14);
        fill: var(--yellow) !important;
        box-shadow: inset 0 0 0 1px rgba(247, 181, 0, .18);
        transition: transform .25s ease, background .25s ease, fill .25s ease;
    }

    .service-card span {
        font-size: 13px !important;
        line-height: 1.24 !important;
        font-weight: 900 !important;
        letter-spacing: -.02em;
    }

    .service-card:hover,
    .edom-card-wrapper:hover .service-card,
    .edom-card-wrapper.show-popover .service-card {
        transform: translateY(-6px) !important;
        border-color: rgba(247, 181, 0, .68) !important;
        background:
            radial-gradient(circle at 82% 10%, rgba(255, 255, 255, .18), transparent 28%),
            linear-gradient(135deg, #f7b500 0%, #ffc928 100%) !important;
        color: #062f5f !important;
        box-shadow: 0 24px 46px rgba(247, 181, 0, .25) !important;
    }

    .service-card:hover::before,
    .edom-card-wrapper:hover .service-card::before,
    .edom-card-wrapper.show-popover .service-card::before {
        transform: scale(1.45);
        background: rgba(255, 255, 255, .20);
    }

    .service-card:hover svg,
    .edom-card-wrapper:hover .service-card svg,
    .edom-card-wrapper.show-popover .service-card svg {
        fill: #062f5f !important;
        background: rgba(255, 255, 255, .42);
        transform: translateY(-2px) scale(1.04);
    }

    .service-card[data-service-url] {
        cursor: pointer !important;
    }

    .service-card[data-service-url]::after {
        content: "↗";
        position: absolute;
        top: 12px;
        right: 12px;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(7, 43, 87, .08);
        color: var(--primary);
        font-size: 13px;
        font-weight: 900;
        opacity: .82;
    }

    @media (max-width: 768px) {
        body {
            font-size: 16px !important;
            line-height: 1.7 !important;
        }

        p,
        li,
        .page-desc,
        .hero-subtitle,
        .news-page-excerpt,
        .contact-subtitle,
        .contact-info p,
        .contact-info a,
        .news-content-html,
        .footer-item,
        .footer-links a {
            font-size: max(16px, 1rem) !important;
            line-height: 1.7 !important;
        }

        .program-card .program-title,
        .program-section .program-title {
            font-size: 15px !important;
            line-height: 1.25 !important;
            font-weight: 900 !important;
            letter-spacing: -0.025em !important;
            margin-bottom: 10px !important;
        }

        .program-card p,
        .program-section .program-card p {
            font-size: 14px !important;
            line-height: 1.62 !important;
            font-weight: 500 !important;
        }

        .about-points .point-icon {
            background: #ffffff !important;
            border-color: rgba(6, 47, 95, .12) !important;
        }

        .about-points .point-icon img {
            width: 32px !important;
            height: 32px !important;
        }

        .news-list .news-content,
        .info-section .news-content,
        .news-area .news-content {
            justify-content: flex-start !important;
            gap: 0 !important;
        }

        .news-list .news-category,
        .info-section .news-category,
        .news-area .news-category {
            margin: 0 0 5px !important;
            line-height: 1.25 !important;
        }

        .news-list .news-title,
        .info-section .news-title,
        .news-area .news-title {
            margin: 0 !important;
            line-height: 1.28 !important;
            -webkit-line-clamp: 2 !important;
        }

        .news-list .news-excerpt,
        .info-section .news-excerpt,
        .news-area .news-excerpt {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            font-size: 13px !important;
            line-height: 1.45 !important;
            margin: 6px 0 0 !important;
            color: #64748b !important;
        }

        .news-list .news-date,
        .info-section .news-date,
        .news-area .news-date {
            margin-top: 8px !important;
            line-height: 1.25 !important;
        }

        .nav-link,
        #siteHeader .nav-link,
        .dropdown a,
        #siteHeader .dropdown a,
        .news-category,
        .news-date,
        .news-page-category,
        .news-page-date,
        .filter-btn,
        .news-filter-btn {
            line-height: 1.35 !important;
        }

        .service-area {
            position: relative !important;
            top: auto !important;
        }

        .service-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }

        .service-card,
        .edom-card-wrapper .service-card {
            min-height: 112px !important;
            border-radius: 18px !important;
            padding: 14px 8px !important;
        }

        .service-card svg {
            width: 34px !important;
            height: 34px !important;
            padding: 7px;
            border-radius: 14px;
        }

        .service-card span {
            font-size: 11px !important;
            line-height: 1.2 !important;
        }
    }

    @media (max-width: 420px) {
        .program-card .program-title,
        .program-section .program-title {
            font-size: 14px !important;
            line-height: 1.25 !important;
        }

        .program-card p,
        .program-section .program-card p {
            font-size: 13px !important;
            line-height: 1.58 !important;
        }

        .news-list .news-title,
        .info-section .news-title,
        .news-area .news-title {
            line-height: 1.24 !important;
        }

        .news-list .news-excerpt,
        .info-section .news-excerpt,
        .news-area .news-excerpt {
            -webkit-line-clamp: 2 !important;
            font-size: 12.5px !important;
            line-height: 1.42 !important;
            margin-top: 5px !important;
        }

        .news-list .news-date,
        .info-section .news-date,
        .news-area .news-date {
            margin-top: 7px !important;
        }

        .service-grid {
            gap: 10px !important;
        }

        .service-card,
        .edom-card-wrapper .service-card {
            min-height: 104px !important;
            border-radius: 16px !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serviceCards = Array.from(document.querySelectorAll('.service-grid .service-card'));
        let eLearningCounter = 0;

        serviceCards.forEach((card) => {
            const labelElement = card.querySelector('span');
            const label = (labelElement?.innerText || card.innerText || '').replace(/\s+/g, ' ').trim().toLowerCase();
            let url = null;

            if (label.includes('e-learning')) {
                eLearningCounter += 1;

                if (eLearningCounter === 1) {
                    url = 'https://siakad.unw.ac.id';
                    if (labelElement) labelElement.innerHTML = 'E-Learning';
                } else {
                    url = 'https://sipolin.unw.ac.id';
                    if (labelElement) labelElement.innerHTML = 'E-Learning 2';
                }
            }

            if (label.includes('perpustakaan')) {
                url = 'https://play.google.com/store/apps/details?id=id.kubuku.kbk50635ea&hl=id&pli=1';
            }

            if (!url) return;

            card.dataset.serviceUrl = url;
            card.setAttribute('role', 'link');
            card.setAttribute('tabindex', '0');
            card.setAttribute('aria-label', `Buka ${labelElement?.innerText || label}`);

            const openService = () => window.open(url, '_blank', 'noopener,noreferrer');

            card.addEventListener('click', openService);
            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openService();
                }
            });
        });
    });
</script>
