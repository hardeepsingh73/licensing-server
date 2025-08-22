@extends('emails.layouts.master')

@section('content')
    <p style="margin:0 0 18px 0; font-size:22px; font-weight:bold; color:#333;">
        Hello, {{ $user->name }}!
    </p>

    <p style="margin:0 0 12px 0;">
        Thank you for registering an account with us!
    </p>

    <p style="margin:0 0 12px 0;">
        Please click the button below to verify your email address:
    </p>

    <div style="text-align:center; margin:30px 0;">
        <a href="{{ $verificationUrl }}"
            style="background-color:#0d3b66; color:#fff; text-decoration:none; padding:12px 24px; font-size:15px; font-weight:bold; border-radius:4px; display:inline-block;">
            Verify Email
        </a>
    </div>

    <p style="margin:0 0 20px 0;">
        If you didn’t create an account, you can safely ignore this email.
    </p>
@endsection
