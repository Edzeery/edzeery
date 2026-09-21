{{-- One-time login credentials for a newly added team member (Phase 36.8). --}}
{{-- Plain inline-styled HTML — no Markdown mail components exist in this app. --}}
{{-- The password is only ever carried by this synchronous send. --}}
@php($rtl = app()->getLocale() === 'ar')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('emails.store_membership_credentials.subject', ['store' => $storeName]) }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Inter','IBM Plex Sans Arabic',-apple-system,'Segoe UI',sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" style="max-width:560px;background-color:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;">
                    <tr>
                        <td style="background-color:#eef2ff;padding:24px 32px;border-bottom:1px solid #e2e8f0;">
                            <h1 style="margin:0;font-size:18px;font-weight:700;color:#1e293b;">{{ $storeName }}</h1>
                            <p style="margin:4px 0 0;font-size:13px;color:#64748b;">{{ __('emails.store_membership_credentials.subject', ['store' => $storeName]) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="margin:0 0 12px;font-size:14px;color:#1e293b;"><strong>{{ __('emails.store_membership_credentials.greeting', ['name' => $memberName]) }}</strong></p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#334155;">{{ __('emails.store_membership_credentials.intro', ['inviter' => $inviterName, 'store' => $storeName]) }}</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;">
                                        <span style="font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">{{ __('emails.store_membership_credentials.email_label') }}</span>
                                        <div style="font-size:15px;font-weight:600;color:#0f172a;margin-top:4px;" dir="ltr">{{ $memberEmail }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <span style="font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">{{ __('emails.store_membership_credentials.password_label') }}</span>
                                        <div style="font-size:15px;font-weight:700;color:#0f172a;font-family:'JetBrains Mono',Consolas,monospace;background-color:#eef2ff;border:1px solid #c7d2fe;border-radius:6px;padding:8px 12px;margin-top:6px;direction:ltr;text-align:left;" dir="ltr">{{ $password }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $loginUrl }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 28px;border-radius:8px;">
                                            {{ __('emails.store_membership_credentials.login_button') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#64748b;">{{ __('emails.store_membership_credentials.change_prompt') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;border-top:1px solid #e2e8f0;background-color:#f8fafc;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;">{{ $storeName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>