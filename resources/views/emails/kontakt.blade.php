<h2>Nowa wiadomość z formularza kontaktowego</h2>

<p><strong>Imię i nazwisko:</strong> {{ $dane['name'] }}</p>
<p><strong>Email:</strong> {{ $dane['email'] }}</p>
<p><strong>Firma:</strong> {{ $dane['company'] ?? '-' }}</p>
<p><strong>Temat:</strong> {{ $dane['subject'] }}</p>

<p><strong>Wiadomość:</strong><br>
{{ $dane['message'] }}</p>
