@if (isset($whatsappAdmins) && count($whatsappAdmins))
    <div class="wa-admin-modal" id="waAdminModal" aria-hidden="true">
        <script type="application/json" data-primary-admin-json>@json($whatsappAdmins[0] ?? null)</script>

        <div class="wa-admin-modal__backdrop" data-wa-modal-close></div>

        <section class="wa-admin-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="waAdminModalTitle">
            <div class="wa-admin-modal__head">
                <div class="wa-admin-modal__eyebrow">
                    <i class="fab fa-whatsapp"></i>
                    Admin WhatsApp
                </div>
                <h2 class="wa-admin-modal__title" id="waAdminModalTitle">Pilih Admin WhatsApp</h2>
                <button class="wa-admin-modal__close" type="button" aria-label="Tutup pilihan admin WhatsApp" data-wa-modal-close>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="wa-admin-modal__body">
                <p>Silakan pilih salah satu admin untuk membuka chat WhatsApp secara langsung.</p>

                <div class="wa-admin-list">
                    @foreach ($whatsappAdmins as $admin)
                        @if (!empty($admin['url']))
                            <a class="wa-admin-option" href="{{ $admin['url'] }}" target="_blank" rel="noopener noreferrer">
                                <span class="wa-admin-option__icon"><i class="fab fa-whatsapp"></i></span>
                                <span class="wa-admin-option__content">
                                    <span class="wa-admin-option__name">{{ $admin['name'] }}</span>
                                    <span class="wa-admin-option__number">{{ $admin['number'] }}</span>
                                </span>
                                <i class="fas fa-arrow-up-right-from-square wa-admin-option__arrow"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endif
