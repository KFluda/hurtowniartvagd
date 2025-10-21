<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Produkty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui; padding: 16px;">
<h1>Lista produktów</h1>

<form method="get" style="margin: 12px 0;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Szukaj po nazwie / SKU / EAN">
    <button type="submit">Szukaj</button>
</form>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
    <tr>
        <th>SKU</th>
        <th>Nazwa</th>
        <th>EAN</th>
        <th>VAT</th>
        <th>Aktywny</th>
    </tr>
    </thead>
    <tbody>
    @forelse($produkty as $p)
    <tr>
        <td>{{ $p->kod_sku }}</td>
        <td>{{ $p->nazwa }}</td>
        <td>{{ $p->ean }}</td>
        <td>{{ $p->stawka_vat }}%</td>
        <td>{{ $p->aktywny ? 'tak' : 'nie' }}</td>
    </tr>
    @empty
    <tr><td colspan="5">Brak danych w tabeli „produkty”.</td></tr>
    @endforelse
    </tbody>
</table>

<div style="margin-top:12px;">{{ $produkty->links() }}</div>
</body>
</html>
