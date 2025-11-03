<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
        .small { font-size: 10px; color: #555; }
    </style>
</head>
<body>

<h1>FAKTURA VAT</h1>

<table>
    <tr>
        <td><strong>Numer faktury:</strong> F/{{ date('Y') }}/{{ $zam->id_zamowienia }}</td>
        <td class="text-end"><strong>Data wystawienia:</strong> {{ \Carbon\Carbon::parse($zam->data_wystawienia)->format('d.m.Y') }}</td>
    </tr>
</table>

<table>
    <tr>
        <td width="50%">
            <strong>Sprzedawca:</strong><br>
            Hurtownia RTV/AGD<br>
            ul. Handlowa 10<br>
            00-001 Warszawa<br>
            NIP: 111-222-33-44
        </td>
        <td width="50%">
            <strong>Nabywca:</strong><br>
            {{ $zam->klient }}<br>
            {{ $zam->ulica }}<br>
            {{ $zam->kod_pocztowy }} {{ $zam->miasto }}<br>
            NIP: {{ $zam->nip }}
        </td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Nazwa produktu</th>
        <th>Ilość</th>
        <th>Cena netto</th>
        <th>VAT</th>
        <th>Wartość netto</th>
        <th>Wartość brutto</th>
    </tr>
    </thead>
    <tbody>
    @foreach($pozycje as $i => $poz)
    <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $poz->nazwa }}</td>
        <td class="text-end">{{ number_format($poz->ilosc,2,',',' ') }}</td>
        <td class="text-end">{{ number_format($poz->cena_netto,2,',',' ') }} zł</td>
        <td class="text-end">{{ number_format($poz->stawka_vat,0) }}%</td>
        <td class="text-end">{{ number_format($poz->wart_netto,2,',',' ') }} zł</td>
        <td class="text-end">{{ number_format($poz->wart_brutto,2,',',' ') }} zł</td>
    </tr>
    @endforeach
    </tbody>
</table>

<table>
    <tr>
        <td width="70%" class="text-end"><strong>Suma netto:</strong></td>
        <td class="text-end">{{ number_format($zam->suma_netto,2,',',' ') }} zł</td>
    </tr>
    <tr>
        <td width="70%" class="text-end"><strong>Suma VAT:</strong></td>
        <td class="text-end">{{ number_format($zam->suma_vat,2,',',' ') }} zł</td>
    </tr>
    <tr>
        <td width="70%" class="text-end"><strong>Suma brutto:</strong></td>
        <td class="text-end"><strong>{{ number_format($zam->suma_brutto,2,',',' ') }} zł</strong></td>
    </tr>
</table>

<div class="small text-end">
    Dokument wygenerowany automatycznie dnia {{ date('d.m.Y H:i') }}
</div>

</body>
</html>
