@if (isset($whatsappAdmins) && count($whatsappAdmins))
    <div
        class="wa-admin-modal fixed inset-0 z-[100000] hidden items-center justify-center px-4 py-8 [&.is-open]:flex"
        id="waAdminModal"
        aria-hidden="true">
        <script type="application/json" data-primary-admin-json>@json($whatsappAdmins[0] ?? null)</script>

        <div class="wa-admin-modal__backdrop absolute inset-0 bg-slate-950/65 backdrop-blur-sm" data-wa-modal-close></div>

        <section
            class="wa-admin-modal__dialog relative z-10 w-full max-w-[480px] overflow-hidden rounded-[30px] border border-white/25 bg-white shadow-[0_28px_80px_rgba(15,23,42,.32)]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="waAdminModalTitle">
            <div class="wa-admin-modal__head relative overflow-hidden bg-gradient-to-br from-[#031f42] via-[#072b57] to-[#0b5f9f] px-6 py-6 text-white">
                <div class="wa-admin-modal__eyebrow mb-3 inline-flex items-center gap-2 rounded-full border border-[#f7b500]/35 bg-[#f7b500]/15 px-3 py-2 text-xs font-black uppercase tracking-[.08em] text-[#ffe8a1]">
                    <i class="fab fa-whatsapp"></i>
                    Admin WhatsApp
                </div>

                <h2 class="wa-admin-modal__title m-0 pr-12 text-2xl font-black leading-tight" id="waAdminModalTitle">Pilih Admin WhatsApp</h2>

                <button
                    class="wa-admin-modal__close absolute right-5 top-5 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white transition hover:bg-[#f7b500] hover:text-[#072b57]"
                    type="button"
                    aria-label="Tutup pilihan admin WhatsApp"
                    data-wa-modal-close>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="wa-admin-modal__body px-6 py-6">
                <p class="m-0 mb-5 text-sm font-semibold leading-relaxed text-slate-500">
                    Silakan pilih salah satu admin untuk membuka chat WhatsApp secara langsung.
                </p>

                <div class="wa-admin-list grid gap-3">
                    @foreach ($whatsappAdmins as $admin)
                        @if (!empty($admin['url']))
                            <a
                                class="wa-admin-option group flex items-center gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-slate-800 shadow-sm transition hover:-translate-y-1 hover:border-[#25d366]/40 hover:bg-white hover:shadow-[0_18px_38px_rgba(15,23,42,.12)]"
                                href="{{ $admin['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer">
                                <span class="wa-admin-option__icon inline-flex h-12 w-12 flex-none items-center justify-center rounded-2xl bg-[#25d366] text-xl text-white shadow-[0_12px_26px_rgba(37,211,102,.26)]">
                                    <i class="fab fa-whatsapp"></i>
                                </span>

                                <span class="wa-admin-option__content min-w-0 flex-1">
                                    <span class="wa-admin-option__name block text-sm font-black text-[#072b57]">{{ $admin['name'] }}</span>
                                    <span class="wa-admin-option__number block text-xs font-bold text-slate-500">{{ $admin['number'] }}</span>
                                </span>

                                <i class="fas fa-arrow-up-right-from-square wa-admin-option__arrow text-sm text-slate-400 transition group-hover:text-[#25d366]"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endif
