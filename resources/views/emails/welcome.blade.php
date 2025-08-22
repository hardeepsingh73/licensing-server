@extends('emails.layouts.master')

@section('content')
    <p style="margin:0 0 18px 0; font-size:24px; font-weight:bold; color:#333;">
        Welcome, {{ $user->name }}!
    </p>

    <p style="margin:0 0 12px 0;">
        Your account has been created successfully.<br>
        We're excited to have you with us!
    </p>

    <p style="margin:0 0 20px 0; font-weight:500;">
        Here are your details:
    </p>

    <table width="100%" border="0" cellspacing="0" cellpadding="8"
        style="font-size:14px; color:#333; border:1px solid #ddd; border-collapse:collapse; margin-bottom:20px;">
        <tr>
            <td style="width:30%; background:#f9f9f9; font-weight:bold; border:1px solid #ddd;">
                Name:
            </td>
            <td style="border:1px solid #ddd;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="width:30%; background:#f9f9f9; font-weight:bold; border:1px solid #ddd;">
                Email:
            </td>
            <td style="border:1px solid #ddd;">{{ $user->email }}</td>
        </tr>
    </table>

    <p style="margin:0 0 20px 0;">
        You can now log in to your account and start exploring.
    </p>

    <div style="text-align:center; margin:30px 0;">
        <a href="{{ url('/login') }}"
            style="background-color:#0d3b66; color:#fff; text-decoration:none; padding:12px 24px; font-size:15px; font-weight:bold; border-radius:4px; display:inline-block;">
            Go to Dashboard
        </a>
    </div>
@endsection
