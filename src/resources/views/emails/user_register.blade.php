<p>{{ __('mail.user_registered.line_intro_1') }}</p>
@if (!empty($fullName))
    <p>{{ $fullName }}{{ __('mail.user_registered.line_intro_2') }}</p>
@endif
<span>{{ __('mail.user_registered.line_intro_3') }}</span>
<br>
<span>{{ __('mail.user_registered.line_intro_4') }}</span>
<hr style="border-top: 1px dashed #000000;">
{{ __('mail.user_registered.info_user_name') }}{{ $userName }}
<br>
{{ __('mail.user_registered.info_id') }}{{ $employeeId }}
<br>
{{ __('mail.user_registered.info_password') }}{{ $passwordDefault }}
<br>
<hr style="border-top: 1px dashed #000000;">
{{ __('mail.user_registered.line_footer_1') }}
<br>
<span><a href="{{ $urlLogin }}">{{ $urlLogin }}</a></span>
<br>
{{ __('mail.user_registered.line_footer_2') }}
<p>{{ __('mail.user_registered.line_notice') }}</p>


