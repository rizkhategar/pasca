<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pascasarjana Universitas Ngudi Waluyo</title>

    <link rel="icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #072b57;
            --primary-dark: #052044;
            --yellow: #f7b500;
            --white: #ffffff;
            --light: #f1f6f7;
            --text: #111827;
            --gold: #d9a935;
            --green: #78927a;
            --blue: #0b5f9f;
            --muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--light);
            color: var(--text);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        button {
            font-family: inherit;
        }

        .container {
            width: min(1120px, 92%);
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 9999;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .top-header {
            width: 100%;
            background: #eef4f5;
            padding: 13px 0;
        }

        .brand-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            width: 100%;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .brand-unw {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            min-width: 0;
        }

        .brand-main {
            font-size: 42px;
            line-height: 1;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .brand-sub {
            margin-top: 4px;
            font-size: 8px;
            line-height: 1.2;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
        }

        .brand-divider {
            width: 2px;
            height: 43px;
            background: var(--primary);
            opacity: 0.7;
            flex-shrink: 0;
        }

        .brand-school {
            color: var(--primary);
            font-weight: 800;
            font-size: 16px;
            line-height: 1.25;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .navbar {
            background: var(--primary);
            min-height: 64px;
            position: relative;
            z-index: 1000;
        }

        .nav-content {
            display: flex;
            align-items: center;
            min-height: 64px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            height: 64px;
        }

        .nav-item {
            position: relative;
            height: 100%;
        }

        .nav-link {
            height: 100%;
            padding: 0 17px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            transition: 0.25s ease;
            white-space: nowrap;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .nav-link:hover,
        .nav-link.nav-click-active {
            background: var(--yellow);
            color: var(--white);
        }

        .nav-item.home-active .nav-link {
            color: var(--yellow);
            background: transparent;
        }

        .nav-item.home-active .nav-link:hover {
            color: var(--white);
            background: var(--yellow);
        }

        .nav-item.home-active::after {
            content: "";
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 0;
            height: 5px;
            border-radius: 8px 8px 0 0;
            background: var(--yellow);
        }

        .nav-item.home-active.hide-indicator::after {
            display: none;
        }

        .chevron {
            width: 7px;
            height: 7px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            transform-origin: center;
            display: inline-block;
            flex-shrink: 0;
            margin-left: 2px;
            transition: transform 0.25s ease;
        }

        .nav-item:hover .chevron {
            transform: rotate(225deg) translateY(-1px);
        }

        .dropdown {
            position: absolute;
            top: 64px;
            left: 0;
            min-width: 255px;
            background: var(--white);
            border-radius: 0 0 6px 6px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
            padding: 12px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: 0.25s ease;
            z-index: 2000;
        }

        .nav-item:hover .dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown a {
            display: block;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            transition: 0.2s ease;
        }

        .dropdown a:hover {
            background: #f3f4f6;
            color: var(--primary);
            padding-left: 23px;
        }

        .hamburger {
            display: none;
            margin-left: auto;
            width: 42px;
            height: 42px;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            width: 28px;
            height: 3px;
            background: var(--white);
            margin: 6px auto;
            border-radius: 10px;
        }

        .hero {
            position: relative;
            min-height: 360px;
            overflow: hidden;
            background: var(--primary-dark);
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.7s ease;
            background-size: cover;
            background-position: center;
            filter: brightness(1.15);
            z-index: 1;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg,
                    rgba(7, 43, 87, 0.50),
                    rgba(7, 43, 87, 0.35),
                    rgba(7, 43, 87, 0.55));
        }

        .hero-content {
            min-height: 360px;
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            max-width: 740px;
            margin-left: 80px;
        }

        .hero-title {
            font-size: 36px;
            line-height: 1.25;
            font-weight: 800;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .hero-subtitle {
            font-size: 19px;
            font-weight: 500;
            color: var(--white);
            margin-bottom: 20px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 240px;
            height: 51px;
            padding: 0 24px;
            background: var(--yellow);
            color: var(--white);
            font-size: 17px;
            font-weight: 800;
            border-radius: 5px;
            text-transform: uppercase;
            box-shadow: 0 8px 18px rgba(247, 181, 0, 0.28);
            transition: 0.25s ease;
        }

        .btn-primary:hover {
            background: #d99f00;
            transform: translateY(-2px);
        }

        .hero-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.92);
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            z-index: 5;
            transition: 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .hero-arrow:hover {
            background: var(--yellow);
            color: var(--white);
        }

        .hero-arrow.left {
            left: clamp(22px, 7vw, 96px);
        }

        .hero-arrow.right {
            right: clamp(22px, 7vw, 96px);
        }

        .hero-dots {
            position: absolute;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 6;
        }

        .hero-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: none;
            background: #e5e7eb;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .hero-dot.active {
            background: var(--yellow);
        }

        /* ==========================================================================
           PROGRAM STUDI PASCASARJANA - UPDATED
           ========================================================================== */

        .program-section {
            position: relative;
            overflow: hidden;
            padding: 58px 0 64px;
            background:
                radial-gradient(circle at 12% 8%, rgba(45, 156, 219, .16), transparent 26%),
                radial-gradient(circle at 88% 18%, rgba(247, 181, 0, .16), transparent 22%),
                linear-gradient(180deg, #ffffff 0%, #eef5f6 100%);
        }

        .program-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(7, 43, 87, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(7, 43, 87, .045) 1px, transparent 1px);
            background-size: 42px 42px;
            opacity: .7;
            pointer-events: none;
        }

        .program-section::after {
            content: "";
            position: absolute;
            right: -180px;
            top: -220px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(7, 43, 87, .12), transparent 68%);
            pointer-events: none;
        }

        .program-head {
            position: relative;
            z-index: 2;
            max-width: 780px;
            margin: 0 auto 34px;
            text-align: center;
        }

        .program-kicker {
            width: fit-content;
            margin: 0 auto 12px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 14px;
            border-radius: 999px;
            color: var(--primary);
            background: #ffffff;
            border: 1px solid rgba(7, 43, 87, .10);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .program-kicker i {
            color: var(--yellow);
        }

        .program-head h2 {
            margin: 0 0 12px;
            color: var(--primary);
            font-size: clamp(26px, 3.4vw, 40px);
            line-height: 1.18;
            font-weight: 800;
            letter-spacing: -.7px;
            text-transform: uppercase;
        }

        .program-head p {
            max-width: 660px;
            margin: 0 auto;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
            font-weight: 500;
        }

        .program-grid {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 22px;
        }

        .program-card {
            position: relative;
            overflow: hidden;
            min-height: 238px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px 22px 20px;
            border-radius: 24px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(255, 255, 255, .90)),
                #ffffff;
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: 0 16px 38px rgba(15, 23, 42, .09);
            text-align: left;
            transition: .28s ease;
            isolation: isolate;
        }

        .program-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 82% 12%, rgba(247, 181, 0, .18), transparent 26%),
                radial-gradient(circle at 12% 90%, rgba(45, 156, 219, .14), transparent 28%);
            opacity: .9;
            z-index: -2;
        }

        .program-card::after {
            content: "";
            position: absolute;
            right: -34px;
            top: -34px;
            width: 118px;
            height: 118px;
            border-radius: 50%;
            background: rgba(7, 43, 87, .07);
            transition: .28s ease;
            z-index: -1;
        }

        .program-card:hover {
            transform: translateY(-8px);
            border-color: rgba(247, 181, 0, .55);
            box-shadow: 0 24px 56px rgba(7, 43, 87, .18);
        }

        .program-card:hover::after {
            transform: scale(1.55);
            background: rgba(7, 43, 87, .10);
        }

        .program-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .program-icon {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            box-shadow: 0 16px 30px rgba(7, 43, 87, .24);
            transition: .28s ease;
        }

        .program-card:hover .program-icon {
            transform: rotate(-4deg) scale(1.05);
            background: linear-gradient(135deg, var(--yellow), #f4a900);
            color: var(--primary);
        }

        .program-icon svg {
            width: 34px;
            height: 34px;
            fill: currentColor;
        }

        .program-number {
            min-width: 38px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: var(--primary);
            background: rgba(247, 181, 0, .16);
            border: 1px solid rgba(247, 181, 0, .28);
            font-size: 12px;
            font-weight: 800;
        }

        .program-title {
            margin: 0 0 14px;
            color: var(--primary);
            font-size: 19px;
            line-height: 1.28;
            font-weight: 800;
            letter-spacing: -.2px;
        }

        .program-desc {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
            font-weight: 500;
        }

        .program-detail {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: auto;
            padding: 10px 13px;
            border-radius: 999px;
            color: var(--primary);
            background: #f8fafc;
            border: 1px solid rgba(7, 43, 87, .10);
            font-size: 12px;
            line-height: 1;
            font-weight: 800;
            transition: .25s ease;
        }

        .program-detail i {
            color: var(--yellow);
            font-size: 11px;
            transition: .25s ease;
        }

        .program-card:hover .program-detail {
            color: #ffffff;
            background: var(--primary);
            border-color: var(--primary);
        }

        .program-card:hover .program-detail i {
            transform: translateX(4px);
        }

        .program-scroll-hint {
            display: none;
            position: relative;
            z-index: 2;
            margin-top: 14px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .program-scroll-hint i {
            color: var(--yellow);
        }

        /* ----------------------------------------------------
           BAGIAN BERITA DAN LAYANAN MAHASISWA
           ---------------------------------------------------- */

        .info-section {
            background: #ffffff;
            padding: 18px 0 52px;
        }

        .info-layout {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 48px;
            align-items: start;
        }

        .news-area {
            width: 100%;
            padding-right: 40px;
            border-right: 1px solid #d1d5db;
        }

        .section-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 2px solid var(--primary);
        }

        .section-title {
            font-size: 24px;
            line-height: 1.3;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            margin: 0;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-btn,
        .page-number {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .page-number:hover,
        .page-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f3f4f6;
        }

        .page-number.active {
            background: var(--yellow);
            border-color: var(--yellow);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(247, 181, 0, 0.3);
        }

        .page-btn.dark {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
        }

        .page-btn.dark:hover {
            background: #052044;
            color: #ffffff;
        }

        .page-dots {
            color: #6b7280;
            font-weight: 600;
            letter-spacing: 2px;
            padding: 0 4px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 28px;
        }

        .cat-pill {
            padding: 8px 18px;
            border-radius: 20px;
            border: 1px solid #d1d5db;
            background: transparent;
            color: #4b5563;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cat-pill:hover,
        .cat-pill.active {
            background: var(--yellow);
            border-color: var(--yellow);
            color: var(--white);
        }

        .news-list {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .news-item {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            padding: 24px 0;
            border-bottom: 1px solid #e5e7eb;
            width: 100%;
        }

        .news-thumb {
            width: 220px;
            height: 140px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #f1f6f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .news-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .news-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .news-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            line-height: 1.2;
            font-weight: 800;
            color: var(--green);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .news-category svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        .news-title {
            font-size: 18px;
            line-height: 1.45;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
            width: 100%;
        }

        .news-excerpt {
            font-size: 14px;
            line-height: 1.6;
            color: #6b7280;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-date {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #9ca3af;
            font-size: 13px;
            font-weight: 600;
            margin-top: auto;
        }

        .news-date svg {
            width: 15px;
            height: 15px;
            fill: currentColor;
        }

        .service-area {
            position: relative;
            width: 100%;
        }

        .service-title {
            margin-bottom: 24px;
            font-size: 22px;
            color: var(--primary);
            text-transform: uppercase;
            font-weight: 800;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(135px, 1fr));
            gap: 20px 14px;
            align-items: stretch;
        }

        .service-card {
            width: 100%;
            min-height: 120px;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            background: #ffffff;
            color: #050505;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            padding: 16px;
        }

        .service-card:hover {
            background: var(--yellow);
            color: #ffffff;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(247, 181, 0, 0.25);
            border-color: var(--yellow);
        }

        .service-card svg {
            width: 38px;
            height: 38px;
            fill: var(--yellow);
            transition: 0.25s ease;
        }

        .service-card:hover svg {
            fill: #ffffff;
        }

        .service-card span {
            font-size: 14px;
            line-height: 1.25;
            font-weight: 800;
            text-transform: uppercase;
        }

        .edom-card-wrapper {
            position: relative;
            width: 100%;
            min-height: 120px;
            display: flex;
        }

        .edom-card-wrapper .service-card {
            width: 100%;
            height: 100%;
            min-height: 120px;
            flex: 1;
        }

        .edom-card-wrapper:hover .edom-popover,
        .edom-card-wrapper.show-popover .edom-popover {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .edom-card-wrapper:hover .service-card,
        .edom-card-wrapper.show-popover .service-card {
            background: var(--yellow);
            color: #ffffff;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(247, 181, 0, 0.25);
            border-color: var(--yellow);
        }

        .edom-card-wrapper:hover .service-card svg,
        .edom-card-wrapper.show-popover .service-card svg {
            fill: #ffffff;
        }

        .edom-popover {
            position: absolute;
            top: calc(100% + 14px);
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            width: 230px;
            background: #ffffff;
            border-radius: 9px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.24);
            padding: 16px 14px;
            z-index: 30;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: 0.22s ease;
        }

        .edom-popover::before {
            content: "";
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 12px solid transparent;
            border-right: 12px solid transparent;
            border-bottom: 13px solid #ffffff;
        }

        .edom-popover h4 {
            font-size: 13px;
            line-height: 1.25;
            font-weight: 800;
            margin-bottom: 12px;
            color: #050505;
        }

        .edom-popover p {
            font-size: 12px;
            line-height: 1.35;
            color: #050505;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .edom-popover .small-label {
            font-size: 11px;
            line-height: 1.3;
            color: #777;
            margin-bottom: 9px;
        }

        .edom-score {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .edom-score span {
            width: 24px;
            height: 24px;
            border-radius: 5px;
            background: #e5e5e5;
            color: #111;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1200px) {
            .container {
                width: min(100% - 40px, 1120px);
            }

            .nav-link {
                padding: 0 12px;
                font-size: 11px;
            }

            .hero-text {
                margin-left: 58px;
            }

            .info-layout {
                grid-template-columns: 1.6fr 1fr;
                gap: 32px;
            }

            .news-area {
                padding-right: 28px;
            }
        }

        @media (max-width: 992px) {
            .top-header {
                padding: 10px 0;
            }

            .brand-wrapper {
                justify-content: flex-start;
                gap: 12px;
            }

            .brand-main {
                font-size: 34px;
            }

            .brand-school {
                font-size: 13px;
            }

            .nav-content {
                flex-wrap: wrap;
            }

            .hamburger {
                display: block;
            }

            .nav-menu {
                display: none;
                width: 100%;
                height: auto;
                flex-direction: column;
                align-items: stretch;
                padding: 8px 0 14px;
            }

            .nav-menu.show {
                display: flex;
            }

            .nav-item {
                width: 100%;
                height: auto;
            }

            .nav-link {
                width: 100%;
                height: 48px;
                padding: 0 14px;
                justify-content: space-between;
                font-size: 12px;
                background: transparent;
                color: var(--white);
            }

            .nav-link:hover {
                background: transparent;
                color: var(--white);
            }

            .nav-link.nav-click-active {
                background: var(--yellow);
                color: var(--white);
            }

            .nav-item.home-active .nav-link {
                color: var(--yellow);
                background: transparent;
            }

            .nav-item.home-active .nav-link:hover {
                color: var(--yellow);
                background: transparent;
            }

            .nav-item.home-active::after {
                display: none;
            }

            .nav-item:hover .chevron {
                transform: rotate(45deg) translateY(-2px);
            }

            .nav-item.open .chevron {
                transform: rotate(225deg) translateY(-1px);
            }

            .dropdown {
                position: static;
                min-width: 100%;
                box-shadow: none;
                border-radius: 0;
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
                padding: 5px 0;
                background: #ffffff;
            }

            .nav-item:hover .dropdown {
                display: none;
            }

            .nav-item.open .dropdown {
                display: block;
            }

            .dropdown a {
                padding: 11px 20px;
                font-size: 12px;
            }

            .hero {
                min-height: 340px;
            }

            .hero-content {
                min-height: 340px;
            }

            .hero-text {
                margin-left: 42px;
                max-width: 620px;
            }

            .hero-title {
                font-size: 30px;
            }

            .hero-subtitle {
                font-size: 17px;
            }

            .hero-arrow.left {
                left: clamp(16px, 4vw, 42px);
            }

            .hero-arrow.right {
                right: clamp(16px, 4vw, 42px);
            }

            .program-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .program-card {
                min-height: 220px;
            }

            .info-layout {
                grid-template-columns: 1fr;
                gap: 52px;
            }

            .news-area {
                padding-right: 0;
                border-right: none;
            }

            .service-title {
                margin-bottom: 22px;
            }

            .service-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
                gap: 22px;
            }
        }

        @media (max-width: 768px) {
            .brand-logo {
                width: 52px;
                height: 52px;
            }

            .brand-unw {
                gap: 10px;
            }

            .brand-main {
                font-size: 31px;
            }

            .brand-school {
                font-size: 12px;
            }

            .brand-divider {
                height: 38px;
            }

            .hero {
                min-height: 330px;
            }

            .hero-content {
                min-height: 330px;
            }

            .hero-text {
                margin-left: 0;
                padding: 0 44px;
            }

            .hero-title {
                font-size: 27px;
            }

            .hero-subtitle {
                font-size: 15px;
            }

            .btn-primary {
                min-width: 210px;
                height: 47px;
                font-size: 15px;
            }

            .hero-arrow {
                width: 36px;
                height: 36px;
                font-size: 21px;
            }

            .hero-arrow.left {
                left: 16px;
            }

            .hero-arrow.right {
                right: 16px;
            }

            .hero-dot {
                width: 12px;
                height: 12px;
            }

            .program-section {
                padding: 42px 0 46px;
            }

            .program-head {
                text-align: left;
                margin-bottom: 22px;
            }

            .program-kicker {
                margin-left: 0;
                margin-right: 0;
            }

            .program-head h2 {
                font-size: 25px;
            }

            .program-head p {
                margin-left: 0;
                font-size: 14px;
            }

            .program-grid {
                display: flex !important;
                gap: 16px !important;
                overflow-x: auto !important;
                scroll-snap-type: x mandatory !important;
                padding: 8px 2px 18px !important;
                margin: 0 -2px;
                -webkit-overflow-scrolling: touch;
            }

            .program-grid::-webkit-scrollbar {
                display: none !important;
            }

            .program-card {
                flex: 0 0 270px !important;
                min-height: 222px;
                scroll-snap-align: start !important;
                border-radius: 22px;
            }

            .program-scroll-hint {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .info-section {
                padding: 36px 0 52px;
            }

            .news-item {
                flex-direction: column;
                gap: 16px;
            }

            .news-thumb {
                width: 100%;
                height: 200px;
            }

            .news-title {
                font-size: 17px;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: min(100% - 28px, 1120px);
            }

            .top-header {
                padding: 9px 0;
            }

            .brand-wrapper {
                justify-content: flex-start;
                gap: 9px;
            }

            .brand-logo {
                width: 46px;
                height: 46px;
            }

            .brand-unw {
                gap: 8px;
            }

            .brand-main {
                font-size: 28px;
            }

            .brand-sub {
                font-size: 6.5px;
            }

            .brand-divider {
                width: 1.5px;
                height: 34px;
            }

            .brand-school {
                font-size: 10px;
                white-space: normal;
            }

            .hamburger {
                width: 40px;
                height: 40px;
            }

            .hamburger span {
                width: 26px;
                height: 3px;
            }

            .hero {
                min-height: 315px;
            }

            .hero-content {
                min-height: 315px;
            }

            .hero-text {
                padding: 0 32px;
                text-align: left;
            }

            .hero-title {
                font-size: 23px;
                line-height: 1.28;
            }

            .hero-subtitle {
                font-size: 14px;
                line-height: 1.45;
                margin-bottom: 16px;
            }

            .btn-primary {
                min-width: 185px;
                height: 44px;
                padding: 0 18px;
                font-size: 13px;
            }

            .hero-arrow {
                width: 32px;
                height: 32px;
                font-size: 19px;
            }

            .hero-arrow.left {
                left: 10px;
            }

            .hero-arrow.right {
                right: 10px;
            }

            .hero-dots {
                bottom: 13px;
                gap: 7px;
            }

            .hero-dot {
                width: 11px;
                height: 11px;
            }

            .section-title {
                font-size: 21px;
            }

            .pagination {
                gap: 4px;
                flex-wrap: wrap;
            }

            .page-btn,
            .page-number {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }

            .news-item {
                padding: 20px 0;
            }

            .news-thumb {
                height: 180px;
            }

            .news-title {
                font-size: 16px;
            }

            .service-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .edom-popover {
                width: min(260px, calc(100vw - 48px));
                padding: 16px 14px;
            }
        }

        @media (max-width: 420px) {
            .brand-wrapper {
                align-items: center;
            }

            .brand-school,
            .brand-divider {
                display: none;
            }

            .hero-text {
                padding: 0 26px;
            }

            .hero-title {
                font-size: 20px;
            }

            .hero-subtitle {
                font-size: 13px;
            }

            .btn-primary {
                min-width: 170px;
                height: 42px;
                font-size: 12px;
            }

            .hero-arrow {
                width: 29px;
                height: 29px;
            }

            .hero-arrow.left {
                left: 8px;
            }

            .hero-arrow.right {
                right: 8px;
            }

            .program-card {
                flex-basis: 250px !important;
                padding: 22px 18px 18px;
            }

            .program-icon {
                width: 58px;
                height: 58px;
                flex-basis: 58px;
                border-radius: 20px;
            }

            .program-icon svg {
                width: 31px;
                height: 31px;
            }

            .program-title {
                font-size: 17px;
            }
        }

        @media (max-width: 640px) {
            .pagination#apiNewsPagination {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                justify-content: flex-start !important;
                white-space: nowrap !important;
                -webkit-overflow-scrolling: touch;
                padding: 10px 12px !important;
            }

            .pagination#apiNewsPagination .page-btn,
            .pagination#apiNewsPagination .page-number {
                flex-shrink: 0 !important;
            }

            .page-jump {
                border-top: none !important;
                padding-top: 0 !important;
                margin-left: 8px !important;
                border-left: 1px solid rgba(7, 43, 87, 0.15) !important;
                padding-left: 10px !important;
                display: flex !important;
            }
        }

        @media (max-width: 768px) {
            .footer {
                text-align: left !important;
            }

            .footer-content {
                grid-template-columns: 1fr !important;
                gap: 32px !important;
            }

            .footer-item {
                justify-content: flex-start !important;
            }

            .social-icons {
                justify-content: flex-start !important;
            }

            .map-container {
                margin: 0 !important;
                max-width: 100% !important;
            }

            .footer-map {
                max-width: 100% !important;
            }
        }
    </style>
</head>

<body>

    @include('component.header')

    <section class="hero">
        @if(isset($sliders) && $sliders->count() > 0)
            @foreach($sliders as $index => $slider)
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
                    style="background-image: url('{{ route('sliders.image', $slider->id) }}');"
                    data-duration="{{ $slider->duration_ms ?? 3000 }}">
                </div>
            @endforeach
        @else
            <div class="hero-slide active"
                style="background-image: url('{{ asset('assets/images/hero-campus.png') }}');"
                data-duration="3000"></div>
        @endif

        <button class="hero-arrow left" id="prevSlide" type="button" aria-label="Slide sebelumnya">‹</button>
        <button class="hero-arrow right" id="nextSlide" type="button" aria-label="Slide berikutnya">›</button>

        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">Pascasarjana<br>Universitas Ngudi Waluyo</h1>
                    <p class="hero-subtitle">Pascasarjana Universitas Ngudi Waluyo</p>
                    <a href="#" class="btn-primary">Daftar Sekarang</a>
                </div>
            </div>
        </div>

        <div class="hero-dots" id="heroDots">
            @if(isset($sliders) && $sliders->count() > 0)
                @foreach($sliders as $index => $slider)
                    <button class="hero-dot {{ $index === 0 ? 'active' : '' }}" type="button"
                        aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            @else
                <button class="hero-dot active" type="button" aria-label="Slide 1"></button>
            @endif
        </div>
    </section>

    <section class="program-section">
        <div class="container">
            <div class="program-head">
                <div class="program-kicker">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Program Pascasarjana</span>
                </div>

                <h2>Pilihan Program Magister</h2>

                <p>
                    Temukan program studi Pascasarjana Universitas Ngudi Waluyo yang sesuai dengan kebutuhan
                    pengembangan karier dan keilmuan Anda.
                </p>
            </div>

            <div class="program-grid">

                <a href="{{ route('akademik.show', 'magister-hukum') }}" class="program-card">
                    <div>
                        <div class="program-top">
                            <div class="program-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2a1 1 0 0 1 1 1v2h5a1 1 0 1 1 0 2h-1l2.5 5a3.5 3.5 0 0 1-7 0L15 7h-2v11h4a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2h4V7H9l2.5 5a3.5 3.5 0 0 1-7 0L7 7H6a1 1 0 1 1 0-2h5V3a1 1 0 0 1 1-1Zm-4 6-1.6 3h3.2L8 8Zm8 0-1.6 3h3.2L16 8Z" />
                                </svg>
                            </div>

                            <span class="program-number">04</span>
                        </div>

                        <h3 class="program-title">Magister<br>Hukum</h3>

                        <p class="program-desc">
                            Program lanjutan untuk penguatan kompetensi hukum, tata kelola, dan penyelesaian persoalan
                            hukum modern.
                        </p>
                    </div>

                    <span class="program-detail">
                        Detail Program
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>

                <a href="{{ route('akademik.show', 'magister-manajemen-pendidikan') }}" class="program-card">
                    <div>
                        <div class="program-top">
                            <div class="program-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" />
                                </svg>
                            </div>

                            <span class="program-number">03</span>
                        </div>

                        <h3 class="program-title">Magister Manajemen<br>Pendidikan</h3>

                        <p class="program-desc">
                            Mengembangkan kepemimpinan, manajemen, dan inovasi pendidikan yang adaptif terhadap
                            kebutuhan zaman.
                        </p>
                    </div>

                    <span class="program-detail">
                        Detail Program
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>

                <a href="{{ route('akademik.show', 'magister-kesehatan-masyarakat') }}" class="program-card">
                    <div>
                        <div class="program-top">
                            <div class="program-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 21s-7.5-4.6-9.7-9.2C.6 8.2 2.6 4 6.5 4c2.2 0 3.7 1.2 4.5 2.6C11.8 5.2 13.3 4 15.5 4c3.9 0 5.9 4.2 4.2 7.8C17.5 16.4 12 21 12 21Zm-1.3-7.7h2.6v-2.1h2.1V8.6h-2.1V6.5h-2.6v2.1H8.6v2.6h2.1v2.1Z" />
                                </svg>
                            </div>

                            <span class="program-number">02</span>
                        </div>

                        <h3 class="program-title">Magister Kesehatan<br>Masyarakat</h3>

                        <p class="program-desc">
                            Fokus pada pengembangan ilmu kesehatan masyarakat, kebijakan kesehatan, dan peningkatan
                            kualitas layanan.
                        </p>
                    </div>

                    <span class="program-detail">
                        Detail Program
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>

                <a href="{{ route('akademik.show', 'magister-keperawatan') }}" class="program-card">
                    <div>
                        <div class="program-top">
                            <div class="program-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2 6 4v5c0 3.7 2.5 7.1 6 8 3.5-.9 6-4.3 6-8V4l-6-2Zm1 3v2h2v2h-2v2h-2V9H9V7h2V5h2Zm-8 14c0-2.2 4.7-3.4 7-3.4s7 1.2 7 3.4V22H5v-3Z" />
                                </svg>
                            </div>

                            <span class="program-number">01</span>
                        </div>

                        <h3 class="program-title">Magister<br>Keperawatan</h3>

                        <p class="program-desc">
                            Mendukung peningkatan profesionalisme keperawatan melalui kajian lanjut, praktik, dan riset
                            kesehatan.
                        </p>
                    </div>

                    <span class="program-detail">
                        Detail Program
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>

            </div>

        </div>
    </section>

    <section class="info-section" id="layanan-mahasiswa">
        <div class="container">
            <div class="info-layout">

                <div class="news-area">
                    <div class="section-header">
                        <h2 class="section-title">Berita Terkini & Agenda</h2>

                        <div class="pagination">
                            <button class="page-btn" type="button">‹</button>
                            <button class="page-number active" type="button">1</button>
                            <button class="page-number" type="button">2</button>
                            <button class="page-number" type="button">3</button>
                            <button class="page-number" type="button">4</button>
                            <button class="page-number" type="button">5</button>
                            <span class="page-dots">...</span>
                            <button class="page-number" type="button">264</button>
                            <button class="page-btn dark" type="button">›</button>
                        </div>
                    </div>

                    <div class="category-filters">
                        <button class="cat-pill active">Semua</button>
                        <button class="cat-pill">Umum</button>
                        <button class="cat-pill">Kemahasiswaan</button>
                        <button class="cat-pill">Akademik</button>
                        <button class="cat-pill">LPPM</button>
                        <button class="cat-pill">Kehumasan</button>
                        <button class="cat-pill">Pengembangan & Perencanaan</button>
                        <button class="cat-pill">Alumni</button>
                        <button class="cat-pill">Panduan Akademik</button>
                        <button class="cat-pill">Dosen</button>
                        <button class="cat-pill">PMB</button>
                        <button class="cat-pill">Beasiswa</button>
                        <button class="cat-pill">Teknologi</button>
                        <button class="cat-pill">PKKS/PPKPT</button>
                    </div>

                    <div class="news-list">
                        @for ($i = 0; $i < 4; $i++)
                            <article class="news-item">
                                <div class="news-thumb">
                                    <svg viewBox="0 0 24 24" style="width: 50px; fill: #111;">
                                        <path
                                            d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2ZM8.5 11.5l2.5 3.01L14.5 10l4.5 6H5l3.5-4.5Z" />
                                    </svg>
                                </div>

                                <div class="news-content">
                                    <div class="news-category">
                                        <svg viewBox="0 0 24 24">
                                            <path
                                                d="M20.59 13.41 12 22l-8.59-8.59A2 2 0 0 1 3 12V4a1 1 0 0 1 1-1h8a2 2 0 0 1 1.41.59L22 12.17l-1.41 1.24ZM7.5 8A1.5 1.5 0 1 0 7.5 5 1.5 1.5 0 0 0 7.5 8Z" />
                                        </svg>
                                        Umum
                                    </div>

                                    <h3 class="news-title">
                                        Perkuat Jejaring Global dan Transformasi Pendidikan, Pascasarjana UNW Gelar
                                        Seminar Internasional serta Teken Kerja Sama dengan Mitra Thailand
                                    </h3>

                                    <p class="news-excerpt">
                                        Seminar internasional menghadirkan Executive director for internasional affair
                                        Thai global business administration technological college Thailand
                                    </p>

                                    <div class="news-date">
                                        <svg viewBox="0 0 24 24">
                                            <path
                                                d="M7 2h2v2h6V2h2v2h3a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3V2Zm12 8H5v10h14V10ZM5 8h14V6H5v2Zm2 4h3v3H7v-3Zm5 0h3v3h-3v-3Z" />
                                        </svg>
                                        03 Juni 2026
                                    </div>
                                </div>
                            </article>
                        @endfor
                    </div>
                </div>

                <div class="service-area">
                    <h2 class="service-title">Menu Layanan Mahasiswa</h2>

                    <div class="service-grid">
                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24">
                                <path
                                    d="M10 17v-3H3v-4h7V7l5 5-5 5ZM12 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8v-2h7V5h-7V3Z" />
                            </svg>
                            <span>Login<br>Mahasiswa</span>
                        </button>

                        <div class="edom-card-wrapper" id="edomCardWrapper">
                            <button class="service-card" type="button" id="edomService">
                                <svg viewBox="0 0 24 24">
                                    <path
                                        d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm-1 7V3.5L18.5 9H13ZM8 13h8v2H8v-2Zm0 4h8v2H8v-2Zm0-8h3v2H8V9Z" />
                                </svg>
                                <span>EDOM</span>
                            </button>

                            <div class="edom-popover">
                                <h4>Pilih Mata Kuliah :</h4>
                                <p>Kejelasan Penyampaian Materi Dosen?</p>
                                <div class="small-label">Kejelasan Penyampaian Materi Dosen?</div>
                                <div class="edom-score">
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                </div>
                            </div>
                        </div>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" />
                            </svg>
                            <span>E-Learning</span>
                        </button>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" />
                            </svg>
                            <span>Perpustakaan<br>Digital</span>
                        </button>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" />
                            </svg>
                            <span>E-Learning</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include('component.footer')

    <script>
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        if (hamburger && navMenu) {
            hamburger.addEventListener('click', function () {
                navMenu.classList.toggle('show');
            });
        }

        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');

        dropdownTriggers.forEach((trigger) => {
            trigger.addEventListener('click', function (event) {
                if (window.innerWidth <= 992) {
                    event.preventDefault();

                    const currentItem = trigger.closest('.nav-item');

                    document.querySelectorAll('.nav-item.has-dropdown').forEach((item) => {
                        if (item !== currentItem) {
                            item.classList.remove('open');
                        }
                    });

                    if (currentItem) {
                        currentItem.classList.toggle('open');
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.hero-dot');
            const prevSlide = document.getElementById('prevSlide');
            const nextSlide = document.getElementById('nextSlide');

            if (slides.length !== dots.length) {
                console.error("Error: Jumlah gambar tidak sama dengan jumlah bullet.");
                return;
            }

            if (slides.length === 0) return;

            let currentSlide = 0;
            let slideTimeout;

            function showSlide(index) {
                clearTimeout(slideTimeout);

                let nextIndex = index;
                if (nextIndex >= slides.length) nextIndex = 0;
                if (nextIndex < 0) nextIndex = slides.length - 1;

                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                currentSlide = nextIndex;

                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');

                startAutoSlide();
            }

            function startAutoSlide() {
                clearTimeout(slideTimeout);

                const activeSlide = slides[currentSlide];
                if (!activeSlide) return;

                let duration = parseInt(activeSlide.getAttribute('data-duration')) || 3000;

                slideTimeout = setTimeout(() => {
                    showSlide(currentSlide + 1);
                }, duration);
            }

            if (nextSlide) nextSlide.addEventListener('click', () => showSlide(currentSlide + 1));
            if (prevSlide) prevSlide.addEventListener('click', () => showSlide(currentSlide - 1));

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => showSlide(index));
            });

            startAutoSlide();
        });

        const edomNav = document.getElementById('edomNav');
        const homeNavItem = document.getElementById('homeNavItem');
        const navLinks = document.querySelectorAll('.nav-link');
        const edomCardWrapper = document.getElementById('edomCardWrapper');
        const edomService = document.getElementById('edomService');

        if (edomNav) {
            edomNav.addEventListener('click', function () {
                navLinks.forEach((link) => link.classList.remove('nav-click-active'));
                edomNav.classList.add('nav-click-active');

                if (homeNavItem) homeNavItem.classList.add('hide-indicator');

                if (window.innerWidth <= 992) {
                    if (navMenu) navMenu.classList.remove('show');

                    document.querySelectorAll('.nav-item.has-dropdown').forEach((item) => {
                        item.classList.remove('open');
                    });
                }
            });
        }

        if (edomService && edomCardWrapper) {
            edomService.addEventListener('click', function (event) {
                if (window.innerWidth <= 992) {
                    event.preventDefault();
                    edomCardWrapper.classList.toggle('show-popover');
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 992 && edomCardWrapper && !edomCardWrapper.contains(event.target)) {
                edomCardWrapper.classList.remove('show-popover');
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 992) {
                if (navMenu) navMenu.classList.remove('show');

                document.querySelectorAll('.nav-item.has-dropdown').forEach((item) => {
                    item.classList.remove('open');
                });

                if (edomCardWrapper) edomCardWrapper.classList.remove('show-popover');
            }
        });
    </script>

</body>

</html>