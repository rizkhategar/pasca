<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
                        pascasasarjana.unw.ac.id
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

        <div class="footer-bottom copyright-text">
            © {{ date('Y') }} Universitas Ngudi Waluyo. All Rights Reserved
        </div>
    </div>
</footer>

@include('components.frontend-enhancements')
@include('components.typography')