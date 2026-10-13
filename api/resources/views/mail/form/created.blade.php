@component('mail::message')

Olá 👋

Seu formulário "{{$form->title}}" foi criado com sucesso no Forms4U!

Para compartilhar o formulário, use este link:

@component('mail::panel')
{{$form->share_url}}
@endcomponent

Para incorporar o formulário no seu site, copie e cole o código abaixo:
@component('mail::panel')
{{ '<iframe style="border:none;width:100%;" id="'.$form->slug.'" src="'.$form->share_url.'"></iframe>
    <script type="text/javascript" onload="initEmbed(\''.$form->slug.'\')" src="'.front_url("widgets/iframe.min.js").'"></script>' }}
@endcomponent

@endcomponent
