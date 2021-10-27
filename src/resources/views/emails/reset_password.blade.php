<p>{{ __('mail.reset_password.line_intro_1') }}</p>
@if (!empty($fullName))
    <p>{{ $fullName }}{{ __('mail.reset_password.line_intro_2') }}</p>
@endif
<span>{!! __('mail.reset_password.url_info', ['url' => "<a href=$url>パスワードのリセット</a>"]) !!}</span>
<br>
<span><a href="{{ $url }}">{{ $url }}</a></span>
<p>{{ __('mail.reset_password.line_warning') }}</p>
<p>{{ __('mail.reset_password.line_notice') }}</p>


