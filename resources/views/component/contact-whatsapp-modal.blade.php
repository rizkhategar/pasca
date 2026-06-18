@if (isset($whatsappAdmins) && count($whatsappAdmins))
    <style>
        .whatsapp-contact-card {
            cursor: pointer;
        }

        .whatsapp-contact-card .contact-icon {
            background: linear-gradient(135deg, #25d366, #128c46) !important;
            box-shadow: 0 12px 28px rgba(37, 211, 102, .28) !important;
        }

        .whatsapp-contact-card .contact-info a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #128c46 !important;
            font-weight: 800;
        }

        .whatsapp-contact-card .contact-info a i {
            font-size: 12px;
        }

        .wa-admin-modal {
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .22s ease, visibility .22s ease;
            z-index: 10050;
        }

        .wa-admin-modal.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .wa-admin-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 31, 63, .64);
            backdrop-filter: blur(6px);
        }

        .wa-admin-modal__dialog {
            position: relative;
            width: min(100%, 440px);
            overflow: hidden;
            border-radius: 26px;
            background: #ffffff;
            box-shadow: 0 28px 70px rgba(2, 31, 63, .34);
            transform: translateY(14px) scale(.97);
            transition: transform .22s ease;
        }

        .wa-admin-modal.is-open .wa-admin-modal__dialog {
            transform: translateY(0) scale(1);
        }

        .wa-admin-modal__head {
            position: relative;
            padding: 26px 62px 22px 26px;
            color: #ffffff;
            background:
                radial-gradient(circle at 84% 15%, rgba(255, 255, 255, .18), transparent 27%),
                linear-gradient(135deg, #128c46, #25d366);
        }

        .wa-admin-modal__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 9px;
            color: rgba(255, 255, 255, .88);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .wa-admin-modal__title {
            margin: 0;
            font-size: 24px;
            line-height: 1.15;
            font-weight: 900;
        }

        .wa-admin-modal__close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 38px;
            height: 38px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 12px;
            background: rgba(255, 255, 255, .14);
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
        }

        .wa-admin-modal__body {
            padding: 22px 26px 26px;
        }

        .wa-admin-modal__body > p {
            margin: 0 0 16px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 600;
        }

        .wa-admin-list {
            display: grid;
            gap: 12px;
        }

        .wa-admin-option {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) 22px;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid rgba(18, 140, 70, .14);
            border-radius: 16px;
            background: #f8fffa;
            color: #0f172a;
            text-decoration: none;
            transition: transform .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .wa-admin-option:hover {
            transform: translateY(-2px);
            border-color: rgba(37, 211, 102, .58);
            background: #f0fff5;
            box-shadow: 0 12px 26px rgba(18, 140, 70, .12);
        }

        .wa-admin-option__icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            color: #ffffff;
            background: linear-gradient(135deg, #25d366, #128c46);
            font-size: 21px;
        }

        .wa-admin-option__content {
            min-width: 0;
        }

        .wa-admin-option__name,
        .wa-admin-option__number {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wa-admin-option__name {
            margin-bottom: 3px;
            color: #0f3763;
            font-size: 15px;
            font-weight: 900;
        }

        .wa-admin-option__number {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .wa-admin-option__arrow {
            color: #128c46;
            font-size: 14px;
        }

        body.wa-modal-open {
            overflow: hidden;
        }

        @media (max-width: 540px) {
            .wa-admin-modal {
                padding: 14px;
            }

            .wa-admin-modal__head {
                padding: 22px 56px 20px 20px;
            }

            .wa-admin-modal__title {
                font-size: 21px;
            }

            .wa-admin-modal__body {
                padding: 18px 20px 20px;
            }
        }
    </style>

    <div class="wa-admin-modal" id="waAdminModal" aria-hidden="true">
        <div class="wa-admin-modal__backdrop" data-wa-modal-close></div>

        <section class="wa-admin-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="waAdminModalTitle">
            <div class="wa-admin-modal__head">
                <div class="wa-admin-modal__eyebrow">
                    <i class="fab fa-whatsapp"></i>
                    Contact Admin
                </div>
                <h2 class="wa-admin-modal__title" id="waAdminModalTitle">Choose WhatsApp Admin</h2>
                <button class="wa-admin-modal__close" type="button" aria-label="Close WhatsApp admin options" data-wa-modal-close>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="wa-admin-modal__body">
                <p>Select an admin below to open a direct chat in WhatsApp.</p>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('waAdminModal');
            if (!modal) return;

            const openModal = function () {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('wa-modal-open');
                modal.querySelector('.wa-admin-modal__close')?.focus();
            };

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('wa-modal-open');
            };

            document.querySelectorAll('[data-wa-modal-close]').forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            document.querySelectorAll('.contact-card').forEach(function (card) {
                if (!card.querySelector('.fa-whatsapp')) return;

                card.classList.add('whatsapp-contact-card');
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-label', 'Choose WhatsApp Admin');

                const title = card.querySelector('.contact-info h2');
                const number = card.querySelector('.contact-info a, .contact-info p');

                if (title) title.textContent = 'WhatsApp Admin';
                if (number) {
                    number.innerHTML = 'Choose an admin <i class="fas fa-chevron-right"></i>';
                    number.setAttribute('href', '#waAdminModal');
                }

                card.addEventListener('click', function (event) {
                    event.preventDefault();
                    openModal();
                });

                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openModal();
                    }
                });
            });

            const floatingButton = document.querySelector('.wa-floating');
            if (floatingButton) {
                floatingButton.setAttribute('href', '#waAdminModal');
                floatingButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    openModal();
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });
        });
    </script>
@endif
