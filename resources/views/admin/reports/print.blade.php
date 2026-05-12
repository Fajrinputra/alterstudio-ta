<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #2f2118;
            font-family: Arial, Helvetica, sans-serif;
            background: #f7efe5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .sheet {
            max-width: 1120px;
            margin: 24px auto;
            background: #fffdf9;
            border: 1px solid #ead8c6;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(63, 43, 27, .13);
        }
        .letterhead {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 24px;
            align-items: center;
            padding: 26px 32px;
            color: white;
            background: linear-gradient(135deg, #3f2b1b 0%, #8f5f2d 48%, #d4a017 100%);
        }
        .brand { display: flex; align-items: center; gap: 16px; }
        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.35);
            font-size: 28px;
            font-weight: 800;
        }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 27px; letter-spacing: .2px; }
        .subtitle {
            margin-top: 6px;
            font-size: 12px;
            opacity: .88;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
        .doc-code { text-align: right; font-size: 12px; line-height: 1.65; opacity: .94; }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding: 22px 32px 8px;
        }
        .meta-card {
            border: 1px solid #ead8c6;
            border-radius: 14px;
            padding: 12px 14px;
            background: #fff8ee;
            min-height: 74px;
        }
        .label {
            display: block;
            margin-bottom: 7px;
            color: #8b7359;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }
        .value { color: #3f2b1b; font-size: 14px; font-weight: 700; line-height: 1.35; }
        .summary {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            padding: 16px 32px 22px;
        }
        .summary-card {
            border-radius: 14px;
            padding: 14px;
            color: #3f2b1b;
            border: 1px solid #ead8c6;
            background: #ffffff;
        }
        .summary-card strong { display: block; margin-top: 7px; font-size: 18px; }
        .summary-card.gold { background: #fff7d7; border-color: #efd376; }
        .summary-card.green { background: #eafaf2; border-color: #b6ebce; }
        .summary-card.blue { background: #eaf3ff; border-color: #bed8ff; }
        .summary-card.orange { background: #fff0e6; border-color: #f2c1a2; }
        .summary-card.purple { background: #f4efff; border-color: #d5c6ff; }
        .section { padding: 0 32px 24px; }
        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 8px 0 10px;
            padding-top: 8px;
            border-top: 2px solid #f0dfcd;
            color: #3f2b1b;
            font-size: 17px;
            font-weight: 800;
        }
        .section-note { color: #8b7359; font-size: 11px; font-weight: 500; }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
            background: #fff;
            border: 1px solid #e8d4bf;
        }
        thead th {
            padding: 10px 8px;
            color: #fff;
            border: 1px solid rgba(255,255,255,.25);
            background: #7a4e24;
            font-size: 10px;
            letter-spacing: .7px;
            text-transform: uppercase;
            text-align: center;
        }
        .table-gold thead th { background: #b87900; }
        .table-blue thead th { background: #2563a9; }
        .table-orange thead th { background: #b85c20; }
        tbody td {
            padding: 9px 8px;
            border: 1px solid #ecdcca;
            vertical-align: top;
            line-height: 1.35;
        }
        tbody tr:nth-child(even) td { background: #fff9f1; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .name { font-weight: 700; color: #3f2b1b; }
        .money { font-weight: 700; color: #9a6b00; }
        .empty { padding: 18px; text-align: center; color: #8b7359; background: #fff8ee; }
        .footer {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 24px;
            padding: 10px 32px 30px;
            color: #6d5137;
            font-size: 11px;
        }
        .signature {
            min-height: 90px;
            padding-top: 12px;
            text-align: center;
            border-top: 1px solid #ead8c6;
        }
        .screen-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 18px auto 0;
        }
        .screen-actions button,
        .screen-actions a {
            border-radius: 999px;
            padding: 12px 22px;
            font-weight: 700;
            text-decoration: none;
        }
        .screen-actions button {
            border: 0;
            color: white;
            background: #d4a017;
            cursor: pointer;
        }
        .screen-actions a {
            color: #5c432c;
            background: white;
            border: 1px solid #e1d3c5;
        }
        @media print {
            body { background: white; }
            .sheet { margin: 0; max-width: none; border-radius: 0; box-shadow: none; border: 0; }
            .screen-actions { display: none; }
            .section { break-inside: avoid; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <button type="button" onclick="window.print()">Simpan sebagai PDF</button>
        <a href="{{ route('reports.index', request()->except('download')) }}">Kembali ke Laporan</a>
    </div>

    <main class="sheet">
        <header class="letterhead">
            <div class="brand">
                <div class="brand-mark">A</div>
                <div>
                    <h1>{{ $reportTitle }}</h1>
                    <p class="subtitle">Alter Studio - Rekap operasional, pemasukan, dan kinerja kru</p>
                </div>
            </div>
            <div class="doc-code">
                <div>Dokumen Laporan</div>
                <div>{{ $exportedAt->format('d/m/Y H:i') }} WIB</div>
            </div>
        </header>

        <section class="meta-grid">
            <div class="meta-card"><span class="label">Periode</span><span class="value">{{ \Carbon\CarbonImmutable::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\CarbonImmutable::parse($dateTo)->format('d/m/Y') }}</span></div>
            <div class="meta-card"><span class="label">Kategori</span><span class="value">{{ $categoryLabel }}</span></div>
            <div class="meta-card"><span class="label">Diekspor Oleh</span><span class="value">{{ $exportedBy }}</span></div>
            <div class="meta-card"><span class="label">Tanggal Ekspor</span><span class="value">{{ $exportedAt->format('d/m/Y H:i') }} WIB</span></div>
        </section>

        <section class="summary">
            <div class="summary-card gold"><span class="label">Total Pemesanan</span><strong>{{ $totalOrders }}</strong></div>
            <div class="summary-card green"><span class="label">Pendapatan Diterima</span><strong>Rp {{ number_format($revenueTotal, 0, ',', '.') }}</strong></div>
            <div class="summary-card blue"><span class="label">Fotografer Bertugas</span><strong>{{ $assignedPhotographers }}</strong></div>
            <div class="summary-card orange"><span class="label">Editor Bertugas</span><strong>{{ $assignedEditors }}</strong></div>
            <div class="summary-card purple"><span class="label">Klien Aktif</span><strong>{{ $activeClients }}</strong></div>
        </section>

        @if($isOwnerReport)
            <section class="section">
                <div class="section-title">
                    <span>Detail Owner - Ringkasan Final</span>
                    <span class="section-note">Detail pembayaran dan status pemesanan dari filter laporan</span>
                </div>
                <table class="table-blue">
                    <thead>
                        <tr>
                            <th style="width: 40%">Jenis Pembayaran</th>
                            <th style="width: 20%">Transaksi</th>
                            <th style="width: 40%">Nominal Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentBreakdown as $item)
                            <tr>
                                <td class="name">{{ $item['label'] }}</td>
                                <td class="text-center">{{ $item['total'] }}</td>
                                <td class="text-right money">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Belum ada pembayaran berhasil pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="table-orange" style="margin-top: 14px;">
                    <thead>
                        <tr>
                            <th style="width: 40%">Status Pemesanan</th>
                            <th style="width: 20%">Jumlah</th>
                            <th style="width: 40%">Nominal Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statusSummary as $item)
                            <tr>
                                <td class="name">{{ $item['label'] }}</td>
                                <td class="text-center">{{ $item['total'] }}</td>
                                <td class="text-right money">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Belum ada pemesanan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endif

        <section class="section">
            <div class="section-title">
                <span>Pemesanan dalam Periode</span>
                <span class="section-note">Pendapatan dihitung dari pembayaran berhasil</span>
            </div>
            <table class="table-gold">
                <thead>
                    <tr>
                        <th style="width: 7%">No</th>
                        <th style="width: 10%">ID</th>
                        <th style="width: 22%">Paket</th>
                        <th style="width: 20%">Klien</th>
                        <th style="width: 13%">Tanggal</th>
                        <th style="width: 13%">Status</th>
                        <th style="width: 15%">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">#{{ $booking->id }}</td>
                            <td>{{ $booking->package->name ?? '-' }}</td>
                            <td class="name">{{ $booking->client->name ?? '-' }}</td>
                            <td class="text-center">{{ optional($booking->booking_date)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $booking->statusLabel() }}</td>
                            <td class="text-right money">Rp {{ number_format($booking->total_price ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada pemesanan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <div class="section-title">
                <span>Kinerja Fotografer</span>
                <span class="section-note">Berdasarkan project terjadwal pada periode laporan</span>
            </div>
            <table class="table-blue">
                <thead>
                    <tr>
                        <th style="width: 8%">No</th>
                        <th style="width: 32%">Nama Fotografer</th>
                        <th style="width: 16%">Total Project</th>
                        <th style="width: 44%">Paket yang Ditangani</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($photographerPerf as $photographer)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="name">{{ $photographer['name'] }}</td>
                            <td class="text-center">{{ $photographer['total'] }}</td>
                            <td>
                                @foreach($photographer['packages'] as $pkgName => $count)
                                    {{ $pkgName }} ({{ $count }})@if(!$loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada data kinerja fotografer pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <div class="section-title">
                <span>Kinerja Editor</span>
                <span class="section-note">Berdasarkan project terjadwal pada periode laporan</span>
            </div>
            <table class="table-orange">
                <thead>
                    <tr>
                        <th style="width: 8%">No</th>
                        <th style="width: 32%">Nama Editor</th>
                        <th style="width: 16%">Total Project</th>
                        <th style="width: 44%">Paket yang Ditangani</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($editorPerf as $editor)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="name">{{ $editor['name'] }}</td>
                            <td class="text-center">{{ $editor['total'] }}</td>
                            <td>
                                @foreach($editor['packages'] as $pkgName => $count)
                                    {{ $pkgName }} ({{ $count }})@if(!$loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada data kinerja editor pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <footer class="footer">
            <p>Laporan ini dibuat otomatis oleh sistem Alter Studio berdasarkan data pemesanan, pembayaran berhasil, dan penugasan kru pada periode yang dipilih.</p>
            <div class="signature">
                <span class="label">Diekspor oleh</span>
                <strong>{{ $exportedBy }}</strong>
            </div>
        </footer>
    </main>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 400);
        });
    </script>
</body>
</html>
