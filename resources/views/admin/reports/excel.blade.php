<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #2f241c;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 18px;
        }

        th,
        td {
            border: 1px solid #d9c7b3;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #3f2b1b;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #3f2b1b;
        }

        .subtitle {
            color: #8b7359;
        }

        .section {
            background: #d4a017;
            color: #ffffff;
            font-weight: bold;
            font-size: 15px;
        }

        .label {
            background: #faf6f0;
            font-weight: bold;
            width: 220px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" class="title">{{ $reportTitle }}</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Alter Studio</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td colspan="6">{{ \Carbon\CarbonImmutable::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\CarbonImmutable::parse($dateTo)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td colspan="6">{{ $categoryLabel }}</td>
        </tr>
        <tr>
            <td class="label">Diekspor Oleh</td>
            <td colspan="6">{{ $exportedBy }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Ekspor</td>
            <td colspan="6">{{ $exportedAt->format('d/m/Y H:i') }} WIB</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="5" class="section">Ringkasan Laporan</td>
        </tr>
        <tr>
            <th>Total Pemesanan</th>
            <th>Pendapatan Diterima</th>
            <th>Fotografer Bertugas</th>
            <th>Editor Bertugas</th>
            <th>Klien Aktif</th>
        </tr>
        <tr>
            <td class="center">{{ $totalOrders }}</td>
            <td class="right">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</td>
            <td class="center">{{ $assignedPhotographers }}</td>
            <td class="center">{{ $assignedEditors }}</td>
            <td class="center">{{ $activeClients }}</td>
        </tr>
    </table>

    @if($isOwnerReport)
        <table>
            <tr>
                <td colspan="3" class="section">Detail Owner - Ringkasan Pembayaran</td>
            </tr>
            <tr>
                <th>Jenis Pembayaran</th>
                <th>Jumlah Transaksi</th>
                <th>Nominal Diterima</th>
            </tr>
            @forelse($paymentBreakdown as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="center">{{ $item['total'] }}</td>
                    <td class="right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Belum ada pembayaran berhasil pada periode ini.</td>
                </tr>
            @endforelse
        </table>

        <table>
            <tr>
                <td colspan="3" class="section">Detail Owner - Status Pemesanan</td>
            </tr>
            <tr>
                <th>Status</th>
                <th>Jumlah Pemesanan</th>
                <th>Nominal Diterima</th>
            </tr>
            @forelse($statusSummary as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="center">{{ $item['total'] }}</td>
                    <td class="right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Belum ada pemesanan pada periode ini.</td>
                </tr>
            @endforelse
        </table>
    @endif

    <table>
        <tr>
            <td colspan="7" class="section">Pemesanan dalam Periode</td>
        </tr>
        <tr>
            <th>No</th>
            <th>ID Pemesanan</th>
            <th>Paket</th>
            <th>Klien</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Nilai Pemesanan</th>
        </tr>
        @forelse($bookings as $index => $booking)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">#{{ $booking->id }}</td>
                <td>{{ $booking->package->name ?? '-' }}</td>
                <td>{{ $booking->client->name ?? '-' }}</td>
                <td class="center">{{ optional($booking->booking_date)->format('d/m/Y') }}</td>
                <td class="center">{{ $booking->statusLabel() }}</td>
                <td class="right">Rp {{ number_format($booking->total_price ?? 0, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="center">Belum ada pemesanan pada periode ini.</td>
            </tr>
        @endforelse
    </table>

    <table>
        <tr>
            <td colspan="4" class="section">Kinerja Fotografer</td>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama Fotografer</th>
            <th>Total Project</th>
            <th>Paket yang Ditangani</th>
        </tr>
        @forelse($photographerPerf as $index => $photographer)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $photographer['name'] }}</td>
                <td class="center">{{ $photographer['total'] }}</td>
                <td>{{ collect($photographer['packages'])->map(fn ($count, $name) => $name.' ('.$count.')')->values()->implode(', ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="center">Belum ada data fotografer pada periode ini.</td>
            </tr>
        @endforelse
    </table>

    <table>
        <tr>
            <td colspan="4" class="section">Kinerja Editor</td>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama Editor</th>
            <th>Total Project</th>
            <th>Paket yang Ditangani</th>
        </tr>
        @forelse($editorPerf as $index => $editor)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $editor['name'] }}</td>
                <td class="center">{{ $editor['total'] }}</td>
                <td>{{ collect($editor['packages'])->map(fn ($count, $name) => $name.' ('.$count.')')->values()->implode(', ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="center">Belum ada data editor pada periode ini.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
