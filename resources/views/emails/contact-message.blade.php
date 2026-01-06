<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pesan Kontak Baru</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px 0;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <table width="100%"
                    style="max-width:600px; background:#ffffff; padding:20px; border-radius:8px; margin: 0 auto;">
                    <tr>
                        <td>
                            <h2 style="margin:0; font-size: 24px;">Pesan Kontak Baru</h2>
                            <p style="color: #555555;">Anda menerima pesan baru dari website.</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p><strong>Nama:</strong> {{ $contact->name }}</p>
                            <p><strong>Email:</strong> <a href="mailto:{{ $contact->email }}"
                                    style="color: #3b82f6; text-decoration: none;">{{ $contact->email }}</a></p>
                            @if($contact->phone)
                                <p><strong>Telepon:</strong> {{ $contact->phone }}</p>
                            @endif
                            <br>
                            <div style="text-align: left; padding: 10px 0;">
                                <a href="mailto:{{ $contact->email }}?subject={{ rawurlencode('Re: Pesan dari ' . $contact->name) }}"
                                    style="display: inline-block; background-color: #122737; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                    Balas ke Pengirim
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f9fafb; padding:20px; border-radius:6px; border: 1px solid #e5e7eb;">
                            <strong style="display:block; margin-bottom:10px;">Pesan:</strong>
                            <div style="line-height: 1.6; color: #333;">
                                {!! nl2br(e($contact->message)) !!}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top:20px; font-size:12px; color:#777; text-align: center;">
                            Diterima pada {{ $contact->created_at->format('d M Y, H:i') }}
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>