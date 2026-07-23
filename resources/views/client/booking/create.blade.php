<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                    {{ ($isEdit ?? false) ? 'Ganti Jadwal' : 'Pemesanan Baru' }}
                </p>
                <h2 class="font-display font-bold text-2xl tracking-tight text-[#3F2B1B] sm:text-4xl sm:tracking-tighter">
                    {{ ($isEdit ?? false) ? 'Ganti Jadwal Pemesanan' : 'Buat Pemesanan Layanan' }}
                </h2>
            </div>
            <a href="{{ route('catalog.public') }}"
               class="inline-flex w-full items-center justify-center gap-3 rounded-3xl border border-[#E1D3C5] px-5 py-3 text-[#5C432C] transition-all hover:bg-white hover:shadow-md sm:w-auto sm:px-6">
                Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-5 sm:py-8 lg:py-10 px-0 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl p-4 sm:p-8 lg:p-10">
            <div class="mb-8 grid gap-3 sm:grid-cols-3 sm:mb-12 sm:gap-4">
                <div class="flex items-center gap-3 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-lg font-bold text-white shadow-inner">1</div>
                    <span class="font-medium text-[#3F2B1B]">{{ ($isEdit ?? false) ? 'Paket Tetap' : 'Pilih Paket' }}</span>
                </div>
                <div class="flex items-center gap-3 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-lg font-bold text-white shadow-inner">2</div>
                    <span class="font-medium text-[#3F2B1B]">Detail Pemesanan</span>
                </div>
                <div class="flex items-center gap-3 rounded-3xl border border-[#EDE0D0] bg-white/70 px-4 py-3 opacity-60">
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl border-2 border-[#EDE0D0] text-lg font-bold text-[#8B7359]">3</div>
                    <span class="text-sm text-[#8B7359]">Selesai</span>
                </div>
            </div>

            @if(!$selectedPackage)
                    <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-6 text-center sm:p-10">
                    <i class="fa-solid fa-box-open mb-4 text-4xl text-[#D4A017] opacity-70 sm:mb-6 sm:text-6xl"></i>
                    <p class="font-semibold text-[#3F2B1B] text-xl mb-2">Belum ada paket yang dipilih</p>
                    <p class="text-[#7A5B3A] mb-8">Silakan pilih paket dari katalog terlebih dahulu</p>
                    <a href="{{ route('catalog.public') }}"
                       class="inline-flex items-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-6 py-3 text-sm font-semibold text-white shadow-lg sm:px-8 sm:py-4 sm:text-base">
                        <i class="fa-solid fa-camera"></i>
                        Buka Katalog Paket
                    </a>
                </div>
            @else
                @php
                    $isEditMode = (bool) ($isEdit ?? false);
                    $basePrice = (int) $selectedPackage->price;
                    $oldDate = old('booking_date', isset($booking) ? $booking->booking_date?->toDateString() : null);
                    $oldLocationCode = old('studio_location_code', $booking->studio_location_code ?? null);
                    $oldTime = old('booking_time', $booking->booking_time ?? null);
                    $oldNotes = old('notes', $booking->notes ?? '');
                    $oldPaymentType = old('payment_type', $booking->payment_type ?? 'DP');
                    $existingAddonTotal = $isEditMode && isset($booking) ? (int) $booking->addon_total : 0;
                    $existingTotalPrice = $isEditMode && isset($booking) ? (int) $booking->total_price : $basePrice;
                    $currentAddons = collect($booking->selected_addons ?? [])
                        ->mapWithKeys(fn ($addon) => [md5(($addon['label'] ?? '').'|'.(int) ($addon['price'] ?? 0)) => $addon])
                        ->all();
                    $formAction = ($isEdit ?? false) && isset($booking) ? route('bookings.update', $booking) : route('bookings.store');
                @endphp

                <form method="POST" action="{{ $formAction }}" class="space-y-10" id="booking-form" data-base-price="{{ $basePrice }}" data-availability-url="{{ route('bookings.availability') }}" data-package-id="{{ $selectedPackage->id }}" data-old-time="{{ $oldTime }}" data-ignore-booking-id="{{ ($isEdit ?? false) && isset($booking) ? $booking->id : '' }}" data-lock-pricing="{{ $isEditMode ? '1' : '0' }}" data-locked-addon-total="{{ $existingAddonTotal }}" data-locked-grand-total="{{ $existingTotalPrice }}">
                    @csrf
                    @if($isEdit ?? false)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">

                    <div class="rounded-3xl border border-[#EDE0D0] bg-white p-6 sm:p-8">
                        <p class="uppercase tracking-widest text-xs text-[#8B7359] mb-3">Paket Terpilih</p>
                        @if($isEdit ?? false)
                            <p class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                Ganti jadwal hanya dapat mengubah cabang, tanggal, dan jam sesi. Kategori, paket, add-on, jenis pembayaran, biaya, dan catatan awal tetap mengikuti pemesanan sebelumnya.
                            </p>
                        @endif
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:gap-6">
                            @if($selectedPackage->overview_image)
                                <img src="{{ Storage::url($selectedPackage->overview_image) }}"
                                     class="w-full md:w-40 h-40 rounded-2xl object-cover border border-[#EDE0D0] shadow-sm"
                                     alt="{{ $selectedPackage->name }}">
                            @else
                                <div class="w-full md:w-40 h-40 rounded-2xl bg-[#FAF6F0] border border-[#EDE0D0] flex items-center justify-center">
                                    <i class="fa-solid fa-image text-5xl text-[#8B7359]"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] sm:text-3xl">{{ $selectedPackage->name }}</h3>
                                <p class="text-[#7A5B3A] mt-2 leading-relaxed">{{ $selectedPackage->description }}</p>
                                <div class="mt-6 inline-block bg-gradient-to-r from-[#D4A017]/10 to-[#E07A5F]/10 px-6 py-3 rounded-2xl">
                                    <p class="text-2xl font-bold text-[#D4A017] sm:text-3xl">Rp {{ number_format($basePrice) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isEditMode)
                        <div>
                            <label class="block text-sm font-medium text-[#5C432C] mb-4">Add-on Terkunci</label>
                            <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-4 sm:p-6">
                                @if(!empty($currentAddons))
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach($currentAddons as $addon)
                                            <div class="rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3">
                                                <p class="font-semibold text-[#3F2B1B]">{{ $addon['label'] ?? '-' }}</p>
                                                <p class="mt-1 text-sm text-[#7A5B3A]">
                                                    {{ (int) ($addon['quantity'] ?? 1) }} x Rp {{ number_format((int) ($addon['price'] ?? 0)) }}
                                                    @if(!empty($addon['subtotal']))
                                                        = Rp {{ number_format((int) $addon['subtotal']) }}
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-[#7A5B3A]">Tidak ada add-on pada pemesanan ini.</p>
                                @endif
                                <p class="mt-4 text-xs text-[#8B7359]">
                                    Add-on tidak dapat diubah dari menu ganti jadwal agar durasi dan biaya pemesanan tetap konsisten.
                                </p>
                            </div>
                        </div>
                    @elseif(!empty($addonOptions))
                        <div>
                            <label class="block text-sm font-medium text-[#5C432C] mb-4">Pilih Add-on (Opsional)</label>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($addonOptions as $addonKey => $addon)
                                    @php
                                        $oldSelectedAddons = old('selected_addons', array_keys($currentAddons));
                                        $isChecked = in_array($addonKey, is_array($oldSelectedAddons) ? $oldSelectedAddons : [], true);
                                        $oldQuantities = old('addon_quantities', []);
                                        $quantity = max(1, (int) (is_array($oldQuantities) ? ($oldQuantities[$addonKey] ?? ($currentAddons[$addonKey]['quantity'] ?? 1)) : ($currentAddons[$addonKey]['quantity'] ?? 1)));
                                    @endphp
                                    <div class="addon-card rounded-3xl border border-[#EDE0D0] bg-white p-4 transition-all hover:border-[#D4A017] sm:p-6">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <label class="flex items-start gap-4 cursor-pointer flex-1">
                                                <input type="checkbox"
                                                       name="selected_addons[]"
                                                       value="{{ $addonKey }}"
                                                       data-addon-price="{{ $addon['price'] }}"
                                                       data-addon-target="addon-qty-{{ $loop->index }}"
                                                       class="mt-1.5 w-5 h-5 text-[#D4A017] border-[#E1D3C5] rounded focus:ring-[#D4A017] addon-input"
                                                       @checked($isChecked)>
                                                <div>
                                                    <p class="font-semibold text-[#3F2B1B]">{{ $addon['label'] }}</p>
                                                    <p class="text-[#D4A017] text-sm mt-1">
                                                        + Rp {{ number_format($addon['price']) }}
                                                        @if(!empty($addon['unit'])) / {{ $addon['unit'] }} @endif
                                                    </p>
                                                </div>
                                            </label>
                                            <div class="w-full lg:w-28">
                                                <label class="mb-1 block text-xs text-[#8B7359] lg:text-right">Jumlah</label>
                                                <input id="addon-qty-{{ $loop->index }}"
                                                       type="number"
                                                       name="addon_quantities[{{ $addonKey }}]"
                                                       min="1"
                                                       value="{{ $quantity }}"
                                                       class="addon-quantity-input w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] px-4 py-3 text-center focus:border-[#D4A017]"
                                                       @disabled(!$isChecked)>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-[#7A5B3A] mt-4">Isi jumlah sesuai kebutuhan (misalnya: jumlah orang, jam, atau item).</p>
                        </div>
                    @endif

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Tanggal Pemesanan</label>
                            <input type="date"
                                   id="booking-date"
                                   name="booking_date"
                                   required
                                   class="w-full rounded-3xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 sm:px-5 sm:py-4 sm:text-base"
                                   min="{{ date('Y-m-d') }}"
                                   max="{{ $maxBookingDate }}"
                                   data-min-date="{{ date('Y-m-d') }}"
                                   data-max-date="{{ $maxBookingDate }}"
                                   value="{{ $oldDate }}">
                            <p class="text-xs text-[#8B7359]">
                                Tanggal pemesanan hanya dapat dipilih maksimal 1 bulan ke depan.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Jam Pemesanan yang Tersedia</label>
                            <select name="booking_time"
                                    id="booking-time"
                                    required
                                    disabled
                                    class="w-full rounded-3xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 disabled:bg-[#F3ECE3] disabled:text-[#8B7359] sm:px-5 sm:py-4 sm:text-base">
                                <option value="">Pilih tanggal dan cabang terlebih dahulu</option>
                            </select>
                            <p class="text-xs text-[#8B7359]">
                                Slot mengikuti durasi paket {{ (int) ($selectedPackage->duration_minutes ?? 60) }} menit, add-on tambah waktu, dan jeda {{ (int) config('studio.booking_buffer_minutes', 15) }} menit antar sesi.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Cabang Studio</label>
                        <select name="studio_location_code"
                                id="studio-location-id"
                                required
                                class="w-full rounded-3xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 sm:px-5 sm:py-4 sm:text-base">
                            <option value="">Pilih cabang studio</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->location_code }}" @selected($oldLocationCode == $loc->location_code)>
                                    {{ $loc->name }} - {{ $loc->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="availability-alert" class="hidden rounded-3xl border px-6 py-5 text-sm"></div>

                    @if($isEditMode)
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#5C432C]">Jenis Pembayaran Terkunci</label>
                            <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-4 sm:p-6">
                                <p class="font-semibold text-[#3F2B1B]">
                                    {{ $oldPaymentType === 'FULL' ? 'Bayar Lunas' : 'Bayar DP' }}
                                </p>
                                <p class="mt-1 text-sm text-[#7A5B3A]">
                                    Jenis pembayaran tidak dapat diganti saat mengubah jadwal.
                                </p>
                            </div>
                        </div>
                    @else
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-[#5C432C]">Jenis Pembayaran</label>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="payment-option flex items-center gap-4 rounded-3xl border border-[#EDE0D0] p-4 cursor-pointer transition-all hover:border-[#D4A017] sm:p-6">
                                <input type="radio" name="payment_type" value="DP" @checked($oldPaymentType === 'DP') class="w-5 h-5 text-[#D4A017]">
                                <div>
                                    <span class="font-semibold text-[#3F2B1B]">Bayar DP</span>
                                    <p class="text-xs text-[#7A5B3A]">DP 10% dari total harga pemesanan. Estimasi: Rp <span id="dp-estimate">{{ number_format((int) ceil($basePrice * 0.1)) }}</span></p>
                                </div>
                            </label>
                            <label class="payment-option flex items-center gap-4 rounded-3xl border border-[#EDE0D0] p-4 cursor-pointer transition-all hover:border-[#D4A017] sm:p-6">
                                <input type="radio" name="payment_type" value="FULL" @checked($oldPaymentType === 'FULL') class="w-5 h-5 text-[#D4A017]">
                                <div>
                                    <span class="font-semibold text-[#3F2B1B]">Bayar Lunas</span>
                                    <p class="text-xs text-[#7A5B3A]">Bayar penuh setelah pemesanan dikonfirmasi admin atau manajer</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    @endif

                    @if($isEditMode)
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Catatan Awal</label>
                            <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-3 text-sm text-[#7A5B3A] sm:px-5 sm:py-4 sm:text-base">
                                {{ filled($oldNotes) ? $oldNotes : 'Tidak ada catatan tambahan.' }}
                            </div>
                            <p class="text-xs text-[#8B7359]">Catatan tidak diubah melalui proses ganti jadwal.</p>
                        </div>
                    @else
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="4"
                                  class="w-full resize-none rounded-3xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 sm:px-5 sm:py-4 sm:text-base"
                                  placeholder="Permintaan khusus, tema foto, atau catatan lain...">{{ $oldNotes }}</textarea>
                    </div>
                    @endif

                    <div class="space-y-4 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-6 sm:p-8">
                        <div class="flex flex-col gap-1 text-[#5C432C] sm:flex-row sm:items-center sm:justify-between">
                            <span>Harga Paket Dasar</span>
                            <span class="font-medium">Rp <span id="base-price">{{ number_format($basePrice) }}</span></span>
                        </div>
                        <div class="flex flex-col gap-1 text-[#5C432C] sm:flex-row sm:items-center sm:justify-between">
                            <span>Total Add-on</span>
                            <span class="font-medium">Rp <span id="addon-total">{{ number_format($existingAddonTotal) }}</span></span>
                        </div>
                        <div class="flex flex-col gap-2 border-t border-[#EDE0D0] pt-4 text-base sm:flex-row sm:items-center sm:justify-between sm:text-lg">
                            <span class="font-semibold text-[#3F2B1B]">Total Keseluruhan</span>
                            <span class="text-xl font-bold text-[#D4A017] sm:text-2xl">Rp <span id="grand-total">{{ number_format($existingTotalPrice) }}</span></span>
                        </div>
                        <div class="pt-4 border-t border-[#EDE0D0] text-sm text-[#7A5B3A]">
                            {{ ($isEdit ?? false) ? 'Setelah jadwal diperbarui, paket, add-on, jenis pembayaran, dan total biaya tetap sama. Pemesanan tetap menunggu konfirmasi admin atau manajer.' : 'Setelah formulir dikirim, pemesanan akan ditinjau admin atau manajer. Pembayaran baru dibuka setelah pemesanan dikonfirmasi.' }}
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <a href="{{ route('catalog.public') }}"
                           class="flex-1 rounded-3xl border border-[#E1D3C5] py-3 text-center text-sm text-[#5C432C] transition-all hover:bg-white sm:py-4 sm:text-base">
                            Batal
                        </a>
                        <button type="submit"
                                id="booking-submit"
                                class="flex flex-1 items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] py-3 text-sm font-semibold text-white shadow-lg shadow-[#D4A017]/30 transition-all hover:-translate-y-0.5 hover:shadow-xl sm:py-4 sm:text-base">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span class="text-center">{{ ($isEdit ?? false) ? 'Simpan Perubahan Jadwal' : 'Simpan dan Kirim Pemesanan' }}</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('booking-form');
            if (!form) return;

            const basePrice = Number(form.dataset.basePrice || 0);
            const addonInputs = form.querySelectorAll('.addon-input');
            const addonQuantityInputs = form.querySelectorAll('.addon-quantity-input');
            const addonTotalEl = document.getElementById('addon-total');
            const grandTotalEl = document.getElementById('grand-total');
            const dpEstimateEl = document.getElementById('dp-estimate');
            const format = new Intl.NumberFormat('id-ID');
            const locationInput = document.getElementById('studio-location-id');
            const dateInput = document.getElementById('booking-date');
            const timeInput = document.getElementById('booking-time');
            const availabilityAlert = document.getElementById('availability-alert');
            const submitButton = document.getElementById('booking-submit');
            const oldTime = form.dataset.oldTime || '';
            const minBookingDate = dateInput.dataset.minDate || '';
            const maxBookingDate = dateInput.dataset.maxDate || '';
            const lockPricing = form.dataset.lockPricing === '1';
            const lockedAddonTotal = Number(form.dataset.lockedAddonTotal || 0);
            const lockedGrandTotal = Number(form.dataset.lockedGrandTotal || basePrice);

            const updateTotal = () => {
                if (lockPricing) {
                    addonTotalEl.textContent = format.format(lockedAddonTotal);
                    grandTotalEl.textContent = format.format(lockedGrandTotal);
                    if (dpEstimateEl) {
                        dpEstimateEl.textContent = format.format(Math.ceil(lockedGrandTotal * 0.1));
                    }
                    return;
                }

                let addonTotal = 0;
                addonInputs.forEach((input) => {
                    const quantityInput = document.getElementById(input.dataset.addonTarget);
                    if (quantityInput) {
                        quantityInput.disabled = !input.checked;
                        if (!input.checked) quantityInput.value = '1';
                    }
                    if (input.checked && quantityInput) {
                        const qty = Math.max(1, Number(quantityInput.value || 1));
                        addonTotal += Number(input.dataset.addonPrice || 0) * qty;
                    }
                });
                const grandTotal = basePrice + addonTotal;
                const downPayment = Math.ceil(grandTotal * 0.1);
                addonTotalEl.textContent = format.format(addonTotal);
                grandTotalEl.textContent = format.format(grandTotal);
                if (dpEstimateEl) {
                    dpEstimateEl.textContent = format.format(downPayment);
                }
            };

            const showAlert = (message, variant = 'info') => {
                if (!message) {
                    availabilityAlert.className = 'hidden rounded-3xl border px-6 py-5 text-sm';
                    availabilityAlert.textContent = '';
                    return;
                }

                const styles = {
                    info: 'border-[#EDE0D0] bg-[#FAF6F0] text-[#5C432C]',
                    warning: 'border-amber-200 bg-amber-50 text-amber-700',
                    danger: 'border-red-200 bg-red-50 text-red-700',
                    success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                };

                availabilityAlert.className = `rounded-3xl border px-6 py-5 text-sm ${styles[variant] || styles.info}`;
                availabilityAlert.textContent = message;
            };

            const setSubmitState = (enabled) => {
                submitButton.disabled = !enabled;
                submitButton.classList.toggle('opacity-60', !enabled);
                submitButton.classList.toggle('cursor-not-allowed', !enabled);
            };

            const resetSlots = (message = '', variant = 'warning') => {
                timeInput.innerHTML = '<option value="">Pilih tanggal dan cabang terlebih dahulu</option>';
                timeInput.disabled = true;
                setSubmitState(false);
                showAlert(message, message ? variant : 'info');
            };

            const formatDateLabel = (value) => {
                if (!value || !value.includes('-')) return value;

                const [year, month, day] = value.split('-').map(Number);
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                }).format(new Date(year, month - 1, day));
            };

            const validateBookingDate = () => {
                const bookingDate = dateInput.value;
                dateInput.setCustomValidity('');

                if (!bookingDate) {
                    return true;
                }

                if (minBookingDate && bookingDate < minBookingDate) {
                    const message = `Tanggal pemesanan tidak boleh sebelum hari ini (${formatDateLabel(minBookingDate)}).`;
                    dateInput.setCustomValidity(message);
                    resetSlots(message, 'danger');
                    return false;
                }

                if (maxBookingDate && bookingDate > maxBookingDate) {
                    const message = `Tanggal pemesanan hanya dapat dipilih maksimal 1 bulan ke depan, sampai ${formatDateLabel(maxBookingDate)}.`;
                    dateInput.setCustomValidity(message);
                    resetSlots(message, 'danger');
                    return false;
                }

                return true;
            };

            const appendSelectedAddons = (url) => {
                addonInputs.forEach((input) => {
                    if (!input.checked) return;

                    const quantityInput = document.getElementById(input.dataset.addonTarget);
                    const quantity = Math.max(1, Number(quantityInput?.value || 1));
                    url.searchParams.append('selected_addons[]', input.value);
                    url.searchParams.append(`addon_quantities[${input.value}]`, String(quantity));
                });
            };

            const loadAvailability = async () => {
                const bookingDate = dateInput.value;
                const locationId = locationInput.value;
                const packageId = form.dataset.packageId;
                const selectedTime = timeInput.value || oldTime;

                if (!bookingDate || !locationId || !packageId) {
                    resetSlots('');
                    return;
                }

                if (!validateBookingDate()) {
                    return;
                }

                timeInput.disabled = true;
                timeInput.innerHTML = '<option value="">Memuat slot tersedia...</option>';
                setSubmitState(false);
                showAlert('Memeriksa ketersediaan jam studio...', 'info');

                try {
                    const url = new URL(form.dataset.availabilityUrl, window.location.origin);
                    url.searchParams.set('package_id', packageId);
                    url.searchParams.set('studio_location_code', locationId);
                    url.searchParams.set('booking_date', bookingDate);
                    if (form.dataset.ignoreBookingId) {
                        url.searchParams.set('ignore_booking_id', form.dataset.ignoreBookingId);
                    }
                    appendSelectedAddons(url);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                        throw new Error(firstError || data.message || 'Gagal memuat jadwal yang tersedia.');
                    }

                    if (data.is_closed) {
                        timeInput.innerHTML = '<option value="">Studio tutup pada tanggal ini</option>';
                        setSubmitState(false);
                        showAlert(data.reason || 'Studio tutup pada tanggal yang dipilih.', 'danger');
                        return;
                    }

                    const slots = Array.isArray(data.available_times) ? data.available_times : [];
                    if (!slots.length) {
                        timeInput.innerHTML = '<option value="">Tidak ada slot tersedia</option>';
                        setSubmitState(false);
                        const message = data.is_today
                            ? `Tidak ada slot tersisa untuk hari ini. Jam yang sudah lewat sampai ${data.current_time || 'saat ini'} tidak dapat dipilih.`
                            : 'Semua slot pada tanggal ini sudah terisi. Silakan pilih tanggal lain.';
                        showAlert(message, 'warning');
                        return;
                    }

                    timeInput.disabled = false;
                    timeInput.innerHTML = '<option value="">Pilih jam sesi foto</option>';
                    slots.forEach((slot) => {
                        const option = document.createElement('option');
                        option.value = slot.value;
                        option.textContent = slot.label;
                        if (selectedTime && selectedTime === slot.value) {
                            option.selected = true;
                        }
                        timeInput.appendChild(option);
                    });

                    setSubmitState(Boolean(timeInput.value));
                    const extraDuration = Number(data.extra_duration_minutes || 0);
                    const extraText = extraDuration > 0 ? ` termasuk tambahan waktu ${extraDuration} menit` : '';
                    const slotRule = `Durasi sesi ${data.duration_minutes || '-'} menit${extraText}, dengan jeda ${data.buffer_minutes ?? 15} menit antar sesi.`;
                    const successMessage = data.is_today
                        ? `Slot hari ini sudah diperbarui. Jam yang sudah lewat sampai ${data.current_time || 'saat ini'} tidak ditampilkan. ${slotRule}`
                        : `Slot yang tersedia sudah diperbarui. ${slotRule}`;
                    showAlert(successMessage, data.is_today ? 'warning' : 'success');
                } catch (error) {
                    timeInput.innerHTML = '<option value="">Gagal memuat slot</option>';
                    setSubmitState(false);
                    showAlert(error.message || 'Gagal memuat jadwal yang tersedia.', 'danger');
                }
            };

            addonInputs.forEach(input => input.addEventListener('change', () => {
                updateTotal();
                loadAvailability();
            }));
            addonQuantityInputs.forEach(input => input.addEventListener('input', () => {
                updateTotal();
                loadAvailability();
            }));
            locationInput.addEventListener('change', loadAvailability);
            dateInput.addEventListener('input', validateBookingDate);
            dateInput.addEventListener('change', () => {
                if (validateBookingDate()) {
                    loadAvailability();
                }
            });
            timeInput.addEventListener('change', () => setSubmitState(Boolean(timeInput.value)));
            form.addEventListener('submit', (event) => {
                if (!validateBookingDate()) {
                    event.preventDefault();
                    dateInput.reportValidity();
                }
            });

            updateTotal();

            if (locationInput.value && dateInput.value) {
                loadAvailability();
            } else {
                resetSlots();
            }
        })();
    </script>
</x-app-layout>
