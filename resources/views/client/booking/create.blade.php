<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#b58042]"></i>
                    Pemesanan Baru
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Pemesanan Layanan</h2>
            </div>
            <a href="{{ route('catalog.public') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-center gap-2 mb-8">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-[#b58042] text-white flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-[#3f2b1b]">Pilih Paket</span>
                </div>
                <div class="w-12 h-px bg-[#e3d5c4]"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-[#b58042] text-white flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-[#3f2b1b]">Pembayaran</span>
                </div>
                <div class="w-12 h-px bg-[#e3d5c4]"></div>
                <div class="flex items-center opacity-50">
                    <div class="w-8 h-8 rounded-full border-2 border-[#e3d5c4] text-[#8b7359] flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm text-[#8b7359]">Selesai</span>
                </div>
            </div>

            @if(!$selectedPackage)
                <div class="rounded-xl border border-[#e3d5c4] bg-[#fcf7f1] p-5 text-[#6f5134]">
                    <p class="font-semibold text-[#3f2b1b] mb-1">Pilih paket terlebih dahulu</p>
                    <p class="text-sm">Silakan kembali ke katalog lalu tekan tombol <strong>Pesan</strong> pada paket yang diinginkan.</p>
                    <a href="{{ route('catalog.public') }}" class="inline-flex mt-3 items-center gap-2 px-4 py-2 rounded-lg bg-[#b58042] text-white text-sm">
                        Buka Katalog
                    </a>
                </div>
            @else
                @php
                    $basePrice = (int) $selectedPackage->price;
                @endphp

                <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6" id="booking-form" data-base-price="{{ $basePrice }}">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">

                    <div class="rounded-xl border border-[#e3d5c4] bg-white p-5">
                        <p class="text-sm text-[#6f5134] mb-2">Detail Paket Terpilih</p>
                        <div class="flex items-start gap-4">
                            @if($selectedPackage->overview_image)
                                <img src="{{ Storage::url($selectedPackage->overview_image) }}" class="w-24 h-24 rounded-lg object-cover border border-[#e3d5c4]" alt="{{ $selectedPackage->name }}">
                            @else
                                <div class="w-24 h-24 rounded-lg bg-[#f0e4d6] border border-[#e3d5c4] flex items-center justify-center">
                                    <i class="fa-solid fa-image text-[#8b7359]"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="font-display text-2xl text-[#3f2b1b]">{{ $selectedPackage->name }}</p>
                                <p class="text-[#6f5134] text-sm">{{ $selectedPackage->description }}</p>
                                <p class="mt-2 text-xl font-bold text-[#b58042]">Rp {{ number_format($basePrice) }}</p>
                            </div>
                        </div>
                    </div>

                    @if(!empty($addonOptions))
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#6f5134]">Add-on</label>
                            <div class="grid md:grid-cols-2 gap-3">
                                @foreach($addonOptions as $addonKey => $addon)
                                    <label class="flex items-center justify-between gap-3 p-4 border border-[#e3d5c4] rounded-xl bg-white cursor-pointer hover:border-[#b58042] transition">
                                        <div>
                                            <p class="font-semibold text-[#3f2b1b]">{{ $addon['label'] }}</p>
                                            <p class="text-sm text-[#b58042]">+ Rp {{ number_format($addon['price']) }}</p>
                                        </div>
                                        <input type="checkbox" name="selected_addons[]" value="{{ $addonKey }}" data-addon-price="{{ $addon['price'] }}" class="w-4 h-4 text-[#b58042] border-[#d7c5b2] rounded focus:ring-[#b58042] addon-input">
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134]">Tanggal Booking</label>
                            <input type="date" name="booking_date" required class="w-full px-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134]">Jam (11:00 - 22:00)</label>
                            <input type="time" name="booking_time" required min="11:00" max="22:00" value="11:00" class="w-full px-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            <p class="text-xs text-[#7a5b3a]">Jam operasional studio: 11:00 - 22:00.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#6f5134]">Cabang Studio</label>
                        <select name="studio_location_id" required class="w-full px-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            <option value="">Pilih cabang studio</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }} - {{ $loc->address }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#6f5134]">Jenis Pembayaran</label>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-4 border border-[#e3d5c4] rounded-xl cursor-pointer hover:border-[#b58042] transition-all">
                                <input type="radio" name="payment_type" value="DP" checked class="w-4 h-4 text-[#b58042] border-[#d7c5b2] focus:ring-[#b58042]">
                                <div>
                                    <span class="font-medium text-[#3f2b1b]">DP</span>
                                    <p class="text-xs text-[#7a5b3a]">Pembayaran awal minimal Rp 100.000</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-[#e3d5c4] rounded-xl cursor-pointer hover:border-[#b58042] transition-all">
                                <input type="radio" name="payment_type" value="FULL" class="w-4 h-4 text-[#b58042] border-[#d7c5b2] focus:ring-[#b58042]">
                                <div>
                                    <span class="font-medium text-[#3f2b1b]">Lunas</span>
                                    <p class="text-xs text-[#7a5b3a]">Bayar penuh sekarang</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#6f5134]">Catatan</label>
                        <textarea name="notes" rows="4" class="w-full px-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042] resize-none" placeholder="Catatan atau permintaan khusus (opsional)"></textarea>
                    </div>

                    <div class="rounded-xl border border-[#e3d5c4] bg-[#fcf7f1] p-5 space-y-2">
                        <div class="flex items-center justify-between text-sm text-[#6f5134]">
                            <span>Harga Paket</span>
                            <span>Rp <span id="base-price">{{ number_format($basePrice) }}</span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-[#6f5134]">
                            <span>Total Add-on</span>
                            <span>Rp <span id="addon-total">0</span></span>
                        </div>
                        <div class="flex items-center justify-between text-lg pt-2 border-t border-[#e3d5c4]">
                            <span class="font-semibold text-[#3f2b1b]">Total Pemesanan</span>
                            <span class="font-bold text-[#b58042]">Rp <span id="grand-total">{{ number_format($basePrice) }}</span></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3d5c4]">
                        <a href="{{ route('catalog.public') }}" class="px-6 py-3 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white transition-all">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-paper-plane"></i>
                            Lanjut ke Pembayaran
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
            const addonTotalEl = document.getElementById('addon-total');
            const grandTotalEl = document.getElementById('grand-total');
            const format = new Intl.NumberFormat('id-ID');

            const updateTotal = () => {
                let addonTotal = 0;
                addonInputs.forEach((input) => {
                    if (input.checked) {
                        addonTotal += Number(input.dataset.addonPrice || 0);
                    }
                });

                const grandTotal = basePrice + addonTotal;
                addonTotalEl.textContent = format.format(addonTotal);
                grandTotalEl.textContent = format.format(grandTotal);
            };

            addonInputs.forEach((input) => input.addEventListener('change', updateTotal));
            updateTotal();
        })();
    </script>
</x-app-layout>
