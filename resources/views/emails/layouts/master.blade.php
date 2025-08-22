<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#f5f6fa">
    <tr>
        <td align="center" style="padding: 20px 10px;">

            <table align="center" width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff"
                style="max-width:600px; width:100%; margin:0 auto; border-spacing:0;">

                @include('emails.layouts.header')

                <!-- Content -->
                <tr>
                    <td align="left"
                        style="padding:30px 25px 20px 25px; color:#333333; font-size:15px; line-height:1.6;">
                        @yield('content')
                    </td>
                </tr>

                @include('emails.layouts.footer')

            </table>
        </td>
    </tr>
</table>
