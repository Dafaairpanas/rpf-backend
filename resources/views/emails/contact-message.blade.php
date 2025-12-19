<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pesan Kontak Baru</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center">

<table width="600" style="background:#ffffff; padding:20px; border-radius:8px;">
    <tr>
        <td>
            <h2 style="margin:0;">Pesan Kontak Baru</h2>
            <p>Anda menerima pesan baru dari website.</p>
        </td>
    </tr>

    <tr>
        <td>
            <p><strong>Nama:</strong> {{ $contact->name }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            @if($contact->phone)
                <p><strong>Telepon:</strong> {{ $contact->phone }}</p>
            @endif
        </td>
    </tr>

    <tr>
        <td style="background:#f9fafb; padding:15px; border-radius:6px;">
            <strong>Pesan:</strong><br><br>
            {!! nl2br(e($contact->message)) !!}
        </td>
    </tr>

    <tr>
        <td style="padding-top:15px; font-size:12px; color:#777;">
            Diterima pada {{ $contact->created_at->format('d M Y, H:i') }}
        </td>
    </tr>
</table>

</td>
</tr>
</table>
</body>
</html>
