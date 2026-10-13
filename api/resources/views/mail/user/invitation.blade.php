@component('mail::message')

# Convite para {{ $workspaceName }} no Forms4U

Olá,

Você foi convidado para participar do espaço de trabalho "{{ $workspaceName }}" no Forms4U, a plataforma de formulários da Cidadania4U. Com o Forms4U, você cria, compartilha e gerencia formulários com facilidade.

Para participar, clique no botão abaixo.

@component('mail::button', ['url' => $inviteLink])
Aceitar convite
@endcomponent

Até logo,<br>
Equipe Forms4U

@endcomponent
