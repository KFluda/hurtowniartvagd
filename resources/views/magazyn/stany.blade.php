<!doctype html>
<html lang="pl">
<head><meta charset="utf-8"><title>Stany magazynowe</title></head>
<body style="font-family: system-ui; padding:16px;">
<h1>Stany magazynowe</h1>
<table border="1" cellpadding="6" cellspacing="0">
    <thead><tr><th>ID magazynu</th><th>ID produktu</th><th>Stan</th><th>Zarezerwowane</th></tr></thead>
    <tbody>
    @forelse($stany as $s)
    <tr>
        <td>{{ $s->id_magazynu }}</td>
        <td>{{ $s->id_produktu }}</td>
        <td>{{ number_format($s->stan,2,',',' ') }}</td>
        <td>{{ number_format($s->zarezerwowane,2,',',' ') }}</td>
    </tr>
    @empty
    <tr><td colspan="4">Brak danych.</td></tr>
    @endforelse
    </tbody>
</table>
<div style="margin-top:12px;">{{ $stany->links() }}</div>
</body>
</html>
