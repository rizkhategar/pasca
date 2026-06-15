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

    @media (max-width: 768px) {
        body {
            font-size: 16px !important;
            line-height: 1.7 !important;
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
        .news-content-html,
        .footer-item,
        .footer-links a {
            font-size: max(16px, 1rem) !important;
            line-height: 1.7 !important;
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
</style>
