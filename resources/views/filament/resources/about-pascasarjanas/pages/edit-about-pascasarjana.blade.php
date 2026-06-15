<x-filament-panels::page>
    <style>
        .custom-filament-form{width:min(100%,980px);display:grid;gap:24px}.custom-form-card{border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.035);border-radius:16px;padding:24px}.custom-form-grid{display:grid;gap:22px}.custom-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.custom-field label,.custom-checkbox span{display:block;margin-bottom:8px;color:#f4f4f5;font-size:14px;font-weight:700}.custom-field input[type=text],.custom-field input[type=file],.custom-field textarea{display:block;width:100%;min-height:42px;border:1px solid rgba(255,255,255,.14);background:#111114;color:#f4f4f5;border-radius:10px;padding:10px 12px;outline:none}.custom-field textarea{min-height:120px;resize:vertical}.custom-field input:focus,.custom-field textarea:focus{border-color:#f59e0b;box-shadow:0 0 0 1px #f59e0b}.custom-field input[type=file]::file-selector-button{border:0;border-radius:8px;background:#f59e0b;color:#111827;font-weight:800;padding:8px 12px;margin-right:14px;cursor:pointer}.custom-help{margin-top:8px;color:#a1a1aa;font-size:14px;line-height:1.5}.custom-error{border:1px solid rgba(239,68,68,.65);background:rgba(127,29,29,.25);color:#fecaca;border-radius:14px;padding:14px 18px;font-size:14px}.custom-error ul{margin:8px 0 0;padding-left:20px}.custom-point{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:18px;background:rgba(17,17,20,.46);display:grid;gap:16px}.custom-point-head{display:flex;align-items:center;justify-content:space-between;gap:12px;color:#f4f4f5;font-weight:900}.custom-preview{display:block;width:100%;max-width:220px;max-height:160px;object-fit:contain;border:1px solid rgba(255,255,255,.12);background:#fff;border-radius:12px;padding:8px}.custom-director-preview{max-width:340px;max-height:300px}.custom-actions{display:flex;flex-wrap:wrap;gap:12px}.custom-btn-primary,.custom-btn-secondary,.custom-btn-danger,.custom-btn-soft{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:9px 16px;border-radius:9px;font-size:14px;font-weight:800;text-decoration:none;cursor:pointer}.custom-btn-primary{border:0;background:#f59e0b;color:#111827}.custom-btn-secondary{border:1px solid rgba(255,255,255,.14);background:transparent;color:#f4f4f5}.custom-btn-soft{border:1px solid rgba(245,158,11,.35);background:rgba(245,158,11,.12);color:#fbbf24}.custom-btn-danger{border:1px solid rgba(239,68,68,.45);background:rgba(127,29,29,.22);color:#fecaca}.custom-section-title{margin:0 0 18px;color:#f4f4f5;font-size:18px;font-weight:900}.custom-section-desc{margin:-8px 0 18px;color:#a1a1aa;font-size:14px;line-height:1.5}@media(max-width:768px){.custom-two{grid-template-columns:1fr}.custom-form-card{padding:18px}}
    </style>

    @php
        $record = $this->record;
        $points = old('points', $record->points ?: []);
        if (empty($points)) {
            $points = [['title' => '', 'description' => '', 'icon' => null]];
        }
        $messageValue = old('direktur_message', trim(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", (string) $record->direktur_message))));
    @endphp

    <form action="{{ route('admin.about-pascasarjanas.update', $record) }}" method="POST" enctype="multipart/form-data" class="custom-filament-form">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="custom-error">
                <strong>The submitted data is invalid.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="custom-form-card">
            <h2 class="custom-section-title">Teks Utama Halaman</h2>
            <div class="custom-form-grid">
                <div class="custom-two">
                    <div class="custom-field">
                        <label for="subheading">Sub Judul</label>
                        <input type="text" id="subheading" name="subheading" value="{{ old('subheading', $record->subheading) }}" required>
                    </div>
                    <div class="custom-field">
                        <label for="heading">Judul Utama</label>
                        <input type="text" id="heading" name="heading" value="{{ old('heading', $record->heading) }}" required>
                    </div>
                </div>
                <div class="custom-field">
                    <label for="description">Deskripsi Panjang</label>
                    <textarea id="description" name="description" rows="6" required>{{ old('description', $record->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="custom-form-card">
            <h2 class="custom-section-title">Poin-Poin Fitur & Keunggulan</h2>
            <p class="custom-section-desc">Upload ikon diproses langsung seperti Organization Structures, tanpa proses temporary upload Livewire.</p>
            <div id="pointsWrapper" class="custom-form-grid">
                @foreach ($points as $index => $point)
                    @php
                        $existingIcon = \App\Models\AboutPascasarjana::normalizeImagePath($point['existing_icon'] ?? $point['icon'] ?? null);
                        $existingIconUrl = \App\Models\AboutPascasarjana::publicImageUrl($existingIcon);
                    @endphp
                    <div class="custom-point" data-point>
                        <div class="custom-point-head">
                            <span>Poin #{{ $loop->iteration }}</span>
                            <button type="button" class="custom-btn-danger" data-remove-point>Hapus</button>
                        </div>
                        @if($existingIcon && $existingIconUrl)
                            <div class="custom-field">
                                <label>Ikon Saat Ini</label>
                                <img src="{{ $existingIconUrl }}?v={{ optional($record->updated_at)->timestamp }}" alt="Ikon" class="custom-preview">
                                <input type="hidden" name="points[{{ $index }}][existing_icon]" value="{{ $existingIcon }}">
                            </div>
                        @endif
                        <div class="custom-field">
                            <label>Ganti / Upload Ikon</label>
                            <input type="file" name="points[{{ $index }}][icon]" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                            <p class="custom-help">Kosongkan jika tidak ingin mengganti ikon. Maksimal 5 MB.</p>
                        </div>
                        <div class="custom-two">
                            <div class="custom-field">
                                <label>Judul Poin</label>
                                <input type="text" name="points[{{ $index }}][title]" value="{{ $point['title'] ?? '' }}">
                            </div>
                            <div class="custom-field">
                                <label>Deskripsi Singkat</label>
                                <textarea name="points[{{ $index }}][description]" rows="3">{{ $point['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="custom-actions" style="margin-top:16px">
                <button type="button" class="custom-btn-soft" id="addPointButton">Tambah Poin Baru</button>
            </div>
        </div>

        <div class="custom-form-card">
            <h2 class="custom-section-title">Sambutan Direktur Pascasarjana</h2>
            <div class="custom-form-grid">
                <div class="custom-two">
                    <div class="custom-field">
                        <label for="direktur_heading">Label Sambutan</label>
                        <input type="text" id="direktur_heading" name="direktur_heading" value="{{ old('direktur_heading', $record->direktur_heading) }}">
                    </div>
                    <div class="custom-field">
                        <label for="direktur_greeting">Kalimat Sapaan</label>
                        <input type="text" id="direktur_greeting" name="direktur_greeting" value="{{ old('direktur_greeting', $record->direktur_greeting) }}">
                    </div>
                </div>

                @php
                    $directorImage = \App\Models\AboutPascasarjana::normalizeImagePath($record->direktur_image);
                    $directorImageUrl = \App\Models\AboutPascasarjana::publicImageUrl($directorImage);
                @endphp
                @if($directorImage && $directorImageUrl)
                    <div class="custom-field">
                        <label>Foto Direktur Saat Ini</label>
                        <img src="{{ $directorImageUrl }}?v={{ optional($record->updated_at)->timestamp }}" alt="Foto Direktur" class="custom-preview custom-director-preview">
                        <input type="hidden" name="existing_direktur_image" value="{{ $directorImage }}">
                    </div>
                @endif

                <div class="custom-field">
                    <label for="direktur_image">Ganti / Upload Foto Direktur</label>
                    <input type="file" id="direktur_image" name="direktur_image" accept="image/jpeg,image/png,image/webp">
                    <p class="custom-help">Kosongkan jika tidak ingin mengganti foto. Maksimal 5 MB.</p>
                </div>
                <div class="custom-two">
                    <div class="custom-field">
                        <label for="direktur_name">Nama Lengkap</label>
                        <input type="text" id="direktur_name" name="direktur_name" value="{{ old('direktur_name', $record->direktur_name) }}">
                    </div>
                    <div class="custom-field">
                        <label for="direktur_title">Jabatan</label>
                        <input type="text" id="direktur_title" name="direktur_title" value="{{ old('direktur_title', $record->direktur_title) }}">
                    </div>
                </div>
                <div class="custom-field">
                    <label for="direktur_message">Isi Pesan / Sambutan</label>
                    <textarea id="direktur_message" name="direktur_message" rows="7">{{ $messageValue }}</textarea>
                    <p class="custom-help">Pisahkan paragraf dengan baris kosong.</p>
                </div>
            </div>
        </div>

        <div class="custom-actions">
            <button type="submit" class="custom-btn-primary">Save Changes</button>
            <a href="{{ \App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource::getUrl('index') }}" class="custom-btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
        (function () {
            const wrapper = document.getElementById('pointsWrapper');
            const button = document.getElementById('addPointButton');
            let index = wrapper ? wrapper.querySelectorAll('[data-point]').length : 0;

            function renumber() {
                wrapper.querySelectorAll('[data-point]').forEach((point, pointIndex) => {
                    const label = point.querySelector('.custom-point-head span');
                    if (label) label.textContent = 'Poin #' + (pointIndex + 1);
                });
            }

            button?.addEventListener('click', function () {
                const current = index++;
                const node = document.createElement('div');
                node.className = 'custom-point';
                node.setAttribute('data-point', '');
                node.innerHTML = '<div class="custom-point-head"><span>Poin</span><button type="button" class="custom-btn-danger" data-remove-point>Hapus</button></div><div class="custom-field"><label>Upload Ikon</label><input type="file" name="points[' + current + '][icon]" accept="image/jpeg,image/png,image/webp,image/svg+xml"><p class="custom-help">Gunakan JPG, PNG, WEBP, atau SVG. Maksimal 5 MB.</p></div><div class="custom-two"><div class="custom-field"><label>Judul Poin</label><input type="text" name="points[' + current + '][title]"></div><div class="custom-field"><label>Deskripsi Singkat</label><textarea name="points[' + current + '][description]" rows="3"></textarea></div></div>';
                wrapper.appendChild(node);
                renumber();
            });

            wrapper?.addEventListener('click', function (event) {
                if (!event.target.matches('[data-remove-point]')) return;
                event.preventDefault();
                event.target.closest('[data-point]')?.remove();
                renumber();
            });
        })();
    </script>
</x-filament-panels::page>
