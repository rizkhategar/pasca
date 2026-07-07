@if (isset($whatsappAdmins) && count($whatsappAdmins))
    <div
        class="wa-admin-modal fixed inset-0 z-[100000] hidden items-end justify-center px-4 py-4 sm:items-center sm:py-8 [&.is-open]:flex"
        id="waAdminModal"
        aria-hidden="true">
        <script type="application/json" data-primary-admin-json>@json($whatsappAdmins[0] ?? null)</script>

        <div class="wa-admin-modal__backdrop absolute inset-0 bg-slate-950/70 backdrop-blur-sm" data-wa-modal-close></div>

        <section
            class="wa-admin-modal__dialog relative z-10 flex max-h-[88vh] w-full max-w-[520px] flex-col overflow-hidden rounded-[28px] border border-white/25 bg-white shadow-[0_28px_80px_rgba(15,23,42,.32)] sm:rounded-[34px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="waAdminModalTitle"
            aria-describedby="waAdminModalDesc"
            tabindex="-1">
            <div class="wa-admin-modal__head relative overflow-hidden bg-gradient-to-br from-[#062e62] via-[#0b5f9f] to-[#2389cf] px-5 py-5 text-white sm:px-6 sm:py-6">
                <div class="pointer-events-none absolute -right-12 -top-14 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-[#25d366]/20 blur-2xl"></div>

                <div class="relative z-10 flex items-start gap-4 pr-12">
                    <span class="inline-flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-[#25d366] text-3xl text-white shadow-[0_16px_34px_rgba(37,211,102,.30)]">
                        <i class="fab fa-whatsapp"></i>
                    </span>

                    <div>
                        <div class="wa-admin-modal__eyebrow mb-2 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[11px] font-black uppercase tracking-[.08em] text-white/90">
                            <i class="fas fa-headset"></i>
                            Layanan PMB
                        </div>

                        <h2 class="wa-admin-modal__title m-0 text-[22px] font-black leading-tight sm:text-2xl" id="waAdminModalTitle">
                            Pilih Admin WhatsApp
                        </h2>
                    </div>
                </div>

                <button
                    class="wa-admin-modal__close absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white transition hover:bg-[#f7b500] hover:text-[#072b57] focus:outline-none focus:ring-4 focus:ring-white/25"
                    type="button"
                    aria-label="Tutup pilihan admin WhatsApp"
                    data-wa-modal-close>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="wa-admin-modal__body overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
                <p class="m-0 mb-5 text-sm font-semibold leading-relaxed text-slate-500" id="waAdminModalDesc">
                    Silakan pilih admin yang ingin dihubungi. Chat akan terbuka melalui WhatsApp di tab baru.
                </p>

                <div class="wa-admin-list grid gap-3">
                    @foreach ($whatsappAdmins as $admin)
                        @if (!empty($admin['url']))
                            <a
                                class="wa-admin-option group flex items-center gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-slate-800 shadow-sm transition hover:-translate-y-1 hover:border-[#25d366]/45 hover:bg-white hover:shadow-[0_18px_38px_rgba(15,23,42,.12)] focus:outline-none focus:ring-4 focus:ring-[#25d366]/20"
                                href="{{ $admin['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer">
                                <span class="wa-admin-option__icon inline-flex h-12 w-12 flex-none items-center justify-center rounded-2xl bg-[#25d366] text-xl text-white shadow-[0_12px_26px_rgba(37,211,102,.26)]">
                                    <i class="fab fa-whatsapp"></i>
                                </span>

                                <span class="wa-admin-option__content min-w-0 flex-1">
                                    <span class="wa-admin-option__name block truncate text-sm font-black text-[#072b57]">{{ $admin['name'] }}</span>
                                    <span class="wa-admin-option__number block truncate text-xs font-bold text-slate-500">{{ $admin['number'] }}</span>
                                </span>

                                <span class="wa-admin-option__action inline-flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white text-sm text-slate-400 shadow-sm transition group-hover:bg-[#25d366] group-hover:text-white">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endif
