<x-guest-layout>
    <div class="max-w-2xl mx-auto py-12">
        <h1 class="text-2xl font-bold mb-4">Solicitação recebida</h1>
        <p>Obrigado, {{ $paciente->name }}. Seu agendamento foi criado para <strong>{{ \Carbon\Carbon::parse($agendamento->scheduled_at)->format('d/m/Y H:i') }}</strong>.</p>
        <p class="mt-4">Entraremos em contato pelo telefone {{ $paciente->phone }} para confirmar.</p>
        <p class="mt-6"><a href="/" class="text-indigo-600">Voltar ao site</a></p>
    </div>
</x-guest-layout>
