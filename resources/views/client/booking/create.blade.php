<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                    Pemesanan Baru
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    Buat Pemesanan Layanan
                </h2>
            </div>
            <a href="{{ route('catalog.public') }}"
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl p-10">
            <div class="flex items-center justify-center gap-4 mb-12">
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white flex items-center justify-center font-bold text-lg shadow-inner">1</div>
                    <span class="ml-3 font-medium text-[#3F2B1B]">Pilih Paket</span>
                </div>
                <div class="flex-1 max-w-[80px] h-px bg-gradient-to-r from-[#D4A017] to-[#EDE0D0]"></div>
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white flex items-center justify-center font-bold text-lg shadow-inner">2</div>
                    <span class="ml-3 font-medium text-[#3F2B1B]">Detail Pemesanan</span>
                </div>
                <div class="flex-1 max-w-[80px] h-px bg-[#EDE0D0]"></div>
                <div class="flex items-center opacity-40">
                    <div class="w-9 h-9 rounded-2xl border-2 border-[#EDE0D0] text-[#8B7359] flex items-center justify-center font-bold text-lg">3</div>
                    <span class="ml-3 text-sm text-[#8B7359]">Selesai</span>
                </div>
            </div>

            @if(!$selectedPackage)
                <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-10 text-center">
                    <i class="fa-solid fa-box-open text-6xl text-[#D4A017] mb-6 opacity-70"></i>
                    <p class="font-semibold text-[#3F2B1B] text-xl mb-2">Belum ada paket yang dipilih</p>
                    <p class="text-[#7A5B3A] mb-8">Silakan pilih paket dari katalog terlebih dahulu</p>
                    <a href="{{ route('catalog.public') }}"
                       class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg">
                        <i class="fa-solid fa-camera"></i>
                        Buka Katalog Paket
                    </a>
                </div>
            @else
                @php
                    $basePrice = (int) $selectedPackage->price;
                    $oldDate = old('booking_date');
                    $oldLocationId = old('studio_location_id');
                    $oldTime = old('booking_time');
                @endphp

                @if ($errors->any())
                    <div class="mb-8 rounded-3xl border border-red-200 bg-red-50 px-6 py-5">
                        <div class="flex items-start gap-3 text-red-700">
                            <i class="fa-solid fa-circle-exclamation mt-1"></i>
                            <div class="space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('bookings.store') }}" class="space-y-10" id="booking-form" data-base-price="{{ $basePrice }}" data-availability-url="{{ route('bookings.availability') }}" data-package-id="{{ $selectedPackage->id }}" data-old-time="{{ $oldTime }}">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">

                    <div class="rounded-3xl border border-[#EDE0D0] bg-white p-8">
                        <p class="uppercase tracking-widest text-xs text-[#8B7359] mb-3">Paket Terpilih</p>
                        <div class="flex flex-col md:flex-row gap-6 items-start">
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
                                <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">{{ $selectedPackage->name }}</h3>
                                <p class="text-[#7A5B3A] mt-2 leading-relaxed">{{ $selectedPackage->description }}</p>
                                <div class="mt-6 inline-block bg-gradient-to-r from-[#D4A017]/10 to-[#E07A5F]/10 px-6 py-3 rounded-2xl">
                                    <p class="text-3xl font-bold text-[#D4A017]">Rp {{ number_format($basePrice) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($addonOptions))
                        <div>
                            <label class="block text-sm font-medium text-[#5C432C] mb-4">Pilih Add-on (Opsional)</label>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($addonOptions as $addonKey => $addon)
                                    @php
                                        $oldSelectedAddons = old('selected_addons', []);
                                        $isChecked = in_array($addonKey, is_array($oldSelectedAddons) ? $oldSelectedAddons : [], true);
                                        $oldQuantities = old('addon_quantities', []);
                                        $quantity = max(1, (int) (is_array($oldQuantities) ? ($oldQuantities[$addonKey] ?? 1) : 1));
                                    @endphp
                                    <div class="addon-card p-6 border border-[#EDE0D0] rounded-3xl bg-white hover:border-[#D4A017] transition-all">
                                        <div class="flex items-start justify-between">
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
                                            <div class="w-28">
                                                <label class="block text-xs text-[#8B7359] mb-1 text-right">Jumlah</label>
                                                <input id="addon-qty-{{ $loop->index }}"
                                                       type="number"
                                                       name="addon_quantities[{{ $addonKey }}]"
                                                       min="1"
                                                       value="{{ $quantity }}"
                                                       class="addon-quantity-input w-full px-4 py-3 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] text-center focus:border-[#D4A017]"
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
                                   class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ $oldDate }}">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Jam Pemesanan yang Tersedia</label>
                            <select name="booking_time"
                                    id="booking-time"
                                    required
                                    disabled
                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all disabled:bg-[#F3ECE3] disabled:text-[#8B7359]">
                                <option value="">Pilih tanggal dan cabang terlebih dahulu</option>
                            </select>
                            <p class="text-xs text-[#8B7359]">Hanya slot yang masih tersedia yang ditampilkan.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Cabang Studio</label>
                        <select name="studio_location_id"
                                id="studio-location-id"
                                required
                                class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            <option value="">Pilih cabang studio</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" @selected($oldLocationId == $loc->id)>
                                    {{ $loc->name }} - {{ $loc->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="availability-alert" class="hidden rounded-3xl border px-6 py-5 text-sm"></div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-[#5C432C]">Jenis Pembayaran</label>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="payment-option flex items-center gap-4 p-6 border border-[#EDE0D0] rounded-3xl cursor-pointer hover:border-[#D4A017] transition-all">
                                <input type="radio" name="payment_type" value="DP" checked class="w-5 h-5 text-[#D4A017]">
                                <div>
                                    <span class="font-semibold text-[#3F2B1B]">Bayar DP</span>
                                    <p class="text-xs text-[#7A5B3A]">Minimal Rp 100.000 (sisanya saat sesi foto)</p>
                                </div>
                            </label>
                            <label class="payment-option flex items-center gap-4 p-6 border border-[#EDE0D0] rounded-3xl cursor-pointer hover:border-[#D4A017] transition-all">
                                <input type="radio" name="payment_type" value="FULL" class="w-5 h-5 text-[#D4A017]">
                                <div>
                                    <span class="font-semibold text-[#3F2B1B]">Bayar Lunas</span>
                                    <p class="text-xs text-[#7A5B3A]">Bayar penuh setelah pemesanan dikonfirmasi admin</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="4"
                                  class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 resize-none"
                                  placeholder="Permintaan khusus, tema foto, atau catatan lain...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-8 space-y-4">
                        <div class="flex justify-between text-[#5C432C]">
                            <span>Harga Paket Dasar</span>
                            <span class="font-medium">Rp <span id="base-price">{{ number_format($basePrice) }}</span></span>
                        </div>
                        <div class="flex justify-between text-[#5C432C]">
                            <span>Total Add-on</span>
                            <span class="font-medium">Rp <span id="addon-total">0</span></span>
                        </div>
                        <div class="flex justify-between text-lg pt-4 border-t border-[#EDE0D0]">
                            <span class="font-semibold text-[#3F2B1B]">Total Keseluruhan</span>
                            <span class="font-bold text-2xl text-[#D4A017]">Rp <span id="grand-total">{{ number_format($basePrice) }}</span></span>
                        </div>
                        <div class="pt-4 border-t border-[#EDE0D0] text-sm text-[#7A5B3A]">
                            Setelah formulir dikirim, pemesanan akan masuk ke admin untuk ditinjau. Pembayaran baru dibuka setelah admin mengonfirmasi pemesanan.
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <a href="{{ route('catalog.public') }}"
                           class="flex-1 text-center py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                            Batal
                        </a>
                        <button type="submit"
                                id="booking-submit"
                                class="flex-1 flex items-center justify-center gap-3 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-paper-plane"></i>
                            Simpan dan Kirim Pemesanan
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
            const format = new Intl.NumberFormat('id-ID');
            const locationInput = document.getElementById('studio-location-id');
            const dateInput = document.getElementById('booking-date');
            const timeInput = document.getElementById('booking-time');
            const availabilityAlert = document.getElementById('availability-alert');
            const submitButton = document.getElementById('booking-submit');
            const oldTime = form.dataset.oldTime || '';

            const updateTotal = () => {
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
                addonTotalEl.textContent = format.format(addonTotal);
                grandTotalEl.textContent = format.format(grandTotal);
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

            const resetSlots = (message = '') => {
                timeInput.innerHTML = '<option value="">Pilih tanggal dan cabang terlebih dahulu</option>';
                timeInput.disabled = true;
                setSubmitState(false);
                showAlert(message, message ? 'warning' : 'info');
            };

            const loadAvailability = async () => {
                const bookingDate = dateInput.value;
                const locationId = locationInput.value;
                const packageId = form.dataset.packageId;

                if (!bookingDate || !locationId || !packageId) {
                    resetSlots('');
                    return;
                }

                timeInput.disabled = true;
                timeInput.innerHTML = '<option value="">Memuat slot tersedia...</option>';
                setSubmitState(false);
                showAlert('Memeriksa ketersediaan jam studio...', 'info');

                try {
                    const url = new URL(form.dataset.availabilityUrl, window.location.origin);
                    url.searchParams.set('package_id', packageId);
                    url.searchParams.set('studio_location_id', locationId);
                    url.searchParams.set('booking_date', bookingDate);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memuat jadwal yang tersedia.');
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
                        showAlert('Semua slot pada tanggal ini sudah terisi. Silakan pilih tanggal lain.', 'warning');
                        return;
                    }

                    timeInput.disabled = false;
                    timeInput.innerHTML = '<option value="">Pilih jam sesi foto</option>';
                    slots.forEach((slot) => {
                        const option = document.createElement('option');
                        option.value = slot.value;
                        option.textContent = slot.label;
                        if (oldTime && oldTime === slot.value) {
                            option.selected = true;
                        }
                        timeInput.appendChild(option);
                    });

                    setSubmitState(Boolean(timeInput.value));
                    showAlert('Slot yang tersedia sudah diperbarui. Pilih jam sesi foto untuk melanjutkan.', 'success');
                } catch (error) {
                    timeInput.innerHTML = '<option value="">Gagal memuat slot</option>';
                    setSubmitState(false);
                    showAlert(error.message || 'Gagal memuat jadwal yang tersedia.', 'danger');
                }
            };

            addonInputs.forEach(input => input.addEventListener('change', updateTotal));
            addonQuantityInputs.forEach(input => input.addEventListener('input', updateTotal));
            locationInput.addEventListener('change', loadAvailability);
            dateInput.addEventListener('change', loadAvailability);
            timeInput.addEventListener('change', () => setSubmitState(Boolean(timeInput.value)));

            updateTotal();

            if (locationInput.value && dateInput.value) {
                loadAvailability();
            } else {
                resetSlots();
            }
        })();
    </script>
</x-app-layout>
