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
            
            <!-- Progress Step -->
            <div class="flex items-center justify-center gap-4 mb-12">
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white flex items-center justify-center font-bold text-lg shadow-inner">1</div>
                    <span class="ml-3 font-medium text-[#3F2B1B]">Pilih Paket</span>
                </div>
                <div class="flex-1 max-w-[80px] h-px bg-gradient-to-r from-[#D4A017] to-[#EDE0D0]"></div>
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white flex items-center justify-center font-bold text-lg shadow-inner">2</div>
                    <span class="ml-3 font-medium text-[#3F2B1B]">Detail & Pembayaran</span>
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
                @endphp

                <form method="POST" action="{{ route('bookings.store') }}" class="space-y-10" id="booking-form" data-base-price="{{ $basePrice }}">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">

                    <!-- Selected Package Card -->
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

                    <!-- Add-ons -->
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

                    <!-- Booking Details -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Tanggal Booking</label>
                            <input type="date" 
                                   name="booking_date" 
                                   required 
                                   class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C]">Jam Booking (11:00 - 22:00)</label>
                            <input type="time" 
                                   name="booking_time" 
                                   required 
                                   min="11:00" 
                                   max="22:00" 
                                   value="11:00"
                                   class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            <p class="text-xs text-[#8B7359]">Studio beroperasi pukul 11:00 - 22:00</p>
                        </div>
                    </div>

                    <!-- Studio Location -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Cabang Studio</label>
                        <select name="studio_location_id" 
                                required 
                                class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            <option value="">Pilih cabang studio</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" @selected(old('studio_location_id') == $loc->id)>
                                    {{ $loc->name }} — {{ $loc->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Type -->
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
                                    <p class="text-xs text-[#7A5B3A]">Bayar penuh sekarang</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C]">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 resize-none"
                                  placeholder="Permintaan khusus, tema foto, atau catatan lain..."></textarea>
                    </div>

                    <!-- Price Summary -->
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
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <a href="{{ route('catalog.public') }}" 
                           class="flex-1 text-center py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                            Batal
                        </a>
                        <button type="submit" 
                                class="flex-1 flex items-center justify-center gap-3 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-paper-plane"></i>
                            Lanjut ke Pembayaran
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- JavaScript untuk dynamic total -->
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

            addonInputs.forEach(input => input.addEventListener('change', updateTotal));
            addonQuantityInputs.forEach(input => input.addEventListener('input', updateTotal));
            updateTotal();
        })();
    </script>
</x-app-layout>