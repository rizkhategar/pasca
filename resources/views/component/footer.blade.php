<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .footer {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 8% 12%, rgba(45, 156, 219, .22), transparent 26%),
            radial-gradient(circle at 88% 10%, rgba(255, 193, 7, .12), transparent 24%),
            linear-gradient(135deg, #031f42 0%, #022B63 55%, #07457d 100%);
        color: #fff;
        padding: 52px 0 22px;
    }

    .footer::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, .045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, .045) 1px, transparent 1px);
        background-size: 42px 42px;
        opacity: .42;
        pointer-events: none;
    }

    .footer::after {
        content: "";
        position: absolute;
        right: -170px;
        top: -190px;
        width: 520px;
        height: 520px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, .14), transparent 68%);
        pointer-events: none;
    }

    .footer .container {
        position: relative;
        z-index: 2;
        width: min(100% - 64px, 1180px);
        margin: 0 auto;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr .8fr;
        gap: 42px;
        align-items: flex-start;
    }

    .footer-column {
        min-width: 0;
        margin: 0;
    }

    .footer-column h3 {
        position: relative;
        width: fit-content;
        font-size: 15px;
        line-height: 1.2;
        font-weight: 900;
        margin: 0 0 18px;
        padding-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #ffffff;
    }

    .footer-column h3::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 52px;
        height: 3px;
        border-radius: 99px;
        background: #FFC107;
    }

    .footer-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        line-height: 1.6;
        font-weight: 600;
    }

    .footer-item:last-child {
        margin-bottom: 0;
    }

    .footer-item i {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #FFC107;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .13);
        border-radius: 12px;
        font-size: 15px;
        text-align: center;
    }

    .footer-item span,
    .footer-item a {
        padding-top: 5px;
    }

    .footer-item a {
        color: rgba(255, 255, 255, .88);
        text-decoration: none;
        word-break: break-word;
        transition: .25s ease;
    }

    .footer-item a:hover {
        color: #FFC107;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 14px;
    }

    .footer-links li:last-child {
        margin-bottom: 0;
    }

    .footer-links a {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        line-height: 1.45;
        font-weight: 700;
        text-decoration: none;
        transition: .25s ease;
    }

    .footer-links a::before {
        content: "\f105";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #FFC107;
        background: rgba(255, 255, 255, .10);
        border-radius: 9px;
        font-size: 12px;
        transition: .25s ease;
    }

    .footer-links a:hover {
        color: #FFC107;
        transform: translateX(4px);
    }

    .social-icons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .social-icons a {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFC107;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 13px;
        font-size: 17px;
        text-decoration: none;
        transition: .25s ease;
    }

    .social-icons a:hover {
        transform: translateY(-4px);
        background: #FFC107;
        color: #022B63;
        border-color: #FFC107;
        box-shadow: 0 14px 28px rgba(255, 193, 7, .22);
    }

    .footer-bottom {
        margin-top: 38px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, .22);
        text-align: center;
        color: rgba(255, 255, 255, .78);
        font-size: 14px;
        line-height: 1.6;
        font-weight: 600;
    }

    .footer-bottom strong {
        color: #ffffff;
        font-weight: 900;
    }

    .map-container {
        width: 100%;
        max-width: 280px;
        overflow: hidden;
        border-radius: 18px;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .15);
        box-shadow: 0 16px 34px rgba(0, 0, 0, .18);
        margin: 0;
    }

    .footer-map {
        width: 100%;
        height: 180px;
        border: none;
        border-radius: 18px;
        display: block;
        filter: saturate(1.05) contrast(1.02);
    }

    @media(max-width: 992px) {
        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 36px;
        }

        .map-container {
            max-width: 100%;
        }

        .social-icons {
            flex-wrap: wrap;
            white-space: normal;
        }
    }

    @media(max-width: 768px) {
        .footer {
            text-align: left !important;
            padding: 40px 0 20px !important;
        }

        .footer .container {
            width: min(100% - 28px, 1180px);
        }

        .footer-content {
            grid-template-columns: 1fr !important;
            gap: 34px !important;
        }

        .footer-column {
            margin: 0 !important;
        }

        .footer-column h3 {
            text-align: left !important;
            margin: 0 0 16px !important;
        }

        .footer-item {
            justify-content: flex-start !important;
            align-items: flex-start !important;
        }

        .footer-links {
            margin: 0 !important;
        }

        .footer-links li {
            text-align: left !important;
            margin-bottom: 14px !important;
        }

        .footer-links li:last-child {
            margin-bottom: 0 !important;
        }

        .social-icons {
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            white-space: normal !important;
            margin-top: 0 !important;
        }

        .map-container {
            margin: 0 !important;
            max-width: 100% !important;
        }

        .footer-map {
            height: 210px !important;
        }

        .footer-bottom {
            text-align: left !important;
            line-height: 1.6;
        }
    }

    @media(max-width: 480px) {
        .footer-item {
            font-size: 13px;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 14px;
            font-size: 18px;
        }
    }
</style>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>KONTAK</h3>

                <div class="footer-item">
                    <i class="fas fa-location-dot"></i>
                    <span>Universitas Ngudi Waluyo</span>
                </div>

                <div class="footer-item">
                    <i class="fas fa-phone-volume"></i>
                    <a href="tel:0246925408">(024)-6925408</a>
                </div>

                <div class="footer-item">
                    <i class="fab fa-whatsapp"></i>
                    <a href="https://wa.me/6285730339469" target="_blank" rel="noopener">
                        +62 857-3033-9469
                    </a>
                </div>

                <div class="footer-item">
                    <i class="fas fa-globe"></i>
                    <a href="https://pascasarjana.unw.ac.id" target="_blank" rel="noopener">
                        pascasarjana.unw.ac.id
                    </a>
                </div>
            </div>

            <div class="footer-column">
                <h3>LOKASI</h3>

                <div class="map-container">
                    <iframe
                        class="footer-map"
                        src="https://maps.google.com/maps?q=Universitas%20Ngudi%20Waluyo&t=&z=15&ie=UTF8&iwloc=&output=embed"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <div class="footer-column">
                <h3>LINK CEPAT</h3>

                <ul class="footer-links">
                    <li><a href="#">Akreditasi</a></li>
                    <li><a href="https://pmb.unw.ac.id/" target="_blank" rel="noopener">Admisi</a></li>
                    <li><a href="#">Penjaminan Mutu</a></li>
                    <li><a href="#">Riset & PDM</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>MEDIA SOSIAL</h3>

                <div class="social-icons">
                    <a
                        href="https://www.facebook.com/UniversitasNgudiWaluyo/?locale=id_ID"
                        target="_blank"
                        rel="noopener"
                        aria-label="Facebook Universitas Ngudi Waluyo">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a
                        href="https://www.instagram.com/universitas_ngudiwaluyo/?hl=id"
                        target="_blank"
                        rel="noopener"
                        aria-label="Instagram Universitas Ngudi Waluyo">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <a
                        href="https://x.com/unw_ungaran"
                        target="_blank"
                        rel="noopener"
                        aria-label="X Twitter Universitas Ngudi Waluyo">
                        <i class="fab fa-x-twitter"></i>
                    </a>

                    <a
                        href="https://www.youtube.com/@UNWTV"
                        target="_blank"
                        rel="noopener"
                        aria-label="YouTube UNW TV">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © {{ date('Y') }} <strong>Universitas Ngudi Waluyo</strong>. All Rights Reserved
        </div>
    </div>
</footer>

@include('component.frontend-enhancements')
@include('component.typography')
