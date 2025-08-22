@extends('emails.layouts.master')

@section('content')
    <p style="margin:0 0 18px 0; font-size:22px; font-weight:bold; color:#333;">
        Hello, {{ $user->name }}!
    </p>

    <p style="margin:0 0 12px 0;">
        We received a request to reset your password.
    </p>

    <div style="text-align:center; margin:30px 0;">
        <a href="{{ route('password.reset', ['token' => $token, 'email' => $user->email]) }}"
            style="background-color:#0d3b66; color:#fff; text-decoration:none; padding:12px 24px; font-size:15px; font-weight:bold; border-radius:4px; display:inline-block;">
            Reset Password
        </a>
    </div>

    <p style="margin:0 0 20px 0;">
        If you didn’t request this, you can safely ignore this email.
    </p>
@endsection
