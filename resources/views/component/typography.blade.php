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
    }
</style>
