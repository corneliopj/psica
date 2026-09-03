<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel {{ ucfirst($usuario->perfil) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($dashboard['perfil'] === 'admin')
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">Usuários</div>
                        <div class="mt-2 text-2xl font-bold">{{ $dashboard['metricas']['usuarios'] }}</div>
                    </div>
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">Pacientes</div>
                        <div class="mt-2 text-2xl font-bold">{{ $dashboard['metricas']['pacientes'] }}</div>
                    </div>
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">Profissionais</div>
                        <div class="mt-2 text-2xl font-bold">{{ $dashboard['metricas']['profissionais'] }}</div>
                    </div>
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">Sessões aguardando confirmação</div>
                        <div class="mt-2 text-2xl font-bold">{{ $dashboard['metricas']['solicitacoes_pendentes'] }}</div>
                    </div>
                </div>

                <div class="mt-8 bg-white shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold">Gerenciar usuários</h3>
                            <p class="text-sm text-gray-500">O administrador gerencia contas e perfis do sistema.</p>
                        </div>
                        <a href="{{ route('usuarios.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Abrir gestão</a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-gray-500">
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">E-mail</th>
                                    <th class="py-2 pr-4">Perfil</th>
                                    <th class="py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboard['usuarios'] as $item)
                                    <tr class="border-b">
                                        <td class="py-3 pr-4">{{ $item->nome }}</td>
                                        <td class="py-3 pr-4">{{ $item->email }}</td>
                                        <td class="py-3 pr-4">{{ ucfirst($item->perfil) }}</td>
                                        <td class="py-3">{{ ucfirst($item->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($dashboard['perfil'] === 'profissional')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="col-span-2 bg-white shadow sm:rounded-lg p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">Agenda do doutor</h3>
                                <p class="text-sm text-gray-500">Horários livres ficam verdes. Solicitações pendentes aparecem em amarelo e sessões confirmadas em azul.</p>
                            </div>
                            <button id="addSlotBtn" class="px-3 py-2 bg-green-600 text-white rounded">Adicionar horário livre</button>
                        </div>
                        <div id="calendar" data-perfil="profissional"></div>

                        <div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                            <div class="bg-white rounded-lg w-full max-w-md p-6 relative z-50">
                                <h3 class="text-lg font-semibold mb-2">Solicitar sessão</h3>
                                <div id="booking_error" class="text-red-600 mb-2"></div>
                                <form id="booking_form">
                                    <div class="mb-3">
                                        <label class="block text-sm">Nome</label>
                                        <input id="booking_name" name="name" class="w-full border rounded p-2" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm">Telefone</label>
                                        <input id="booking_phone" name="phone" class="w-full border rounded p-2" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm">Horário</label>
                                        <input id="booking_scheduled_at" name="scheduled_at" type="datetime-local" class="w-full border rounded p-2" required>
                                        <div id="booking_scheduled_display" class="mt-2 text-sm text-gray-700 hidden"></div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" id="booking_cancel" class="px-3 py-2 border rounded">Cancelar</button>
                                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Confirmar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="slotModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                            <div class="bg-white rounded-lg w-full max-w-md p-6 relative z-50">
                                <h3 class="text-lg font-semibold mb-2">Criar horário livre</h3>
                                <div id="slot_error" class="text-red-600 mb-2"></div>
                                <form id="slot_form">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-sm">Data</label>
                                            <input id="slot_date" type="date" class="w-full border rounded p-2" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm">Início</label>
                                            <input id="slot_start_time" type="time" class="w-full border rounded p-2" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm">Fim</label>
                                            <input id="slot_end_time" type="time" class="w-full border rounded p-2" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm">Repetir semanalmente até</label>
                                        <input id="slot_repeat_until" type="date" class="w-full border rounded p-2">
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" id="slot_cancel" class="px-3 py-2 border rounded">Cancelar</button>
                                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded">Criar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-2">Solicitações para confirmar</h3>
                            <p class="text-sm text-gray-500 mb-4">Clique na sessão amarela do calendário para confirmar.</p>
                            <div class="text-3xl font-bold text-amber-500">{{ $dashboard['solicitacoes_pendentes'] }}</div>
                            <div class="mt-6 space-y-3">
                                @forelse($dashboard['solicitacoes_lista'] as $agendamento)
                                    <div class="border rounded p-3">
                                        <div class="font-medium">{{ $agendamento->paciente?->name ?? 'Paciente' }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($agendamento->scheduled_at)->format('d/m/Y H:i') }}</div>
                                        <div class="text-sm text-amber-600">Solicitado</div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">Nenhuma solicitação pendente.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-2">Notificações pendentes</h3>
                            <div class="text-3xl font-bold text-indigo-600">{{ $dashboard['notificacoes_pendentes'] }}</div>
                            <div class="mt-6 space-y-3">
                                @forelse($dashboard['notificacoes'] as $notificacao)
                                    <div class="border rounded p-3">
                                        <div class="font-medium">{{ $notificacao->assunto ?? 'Notificação' }}</div>
                                        <div class="text-sm text-gray-500">{{ $notificacao->mensagem }}</div>
                                        <div class="text-xs text-gray-400 mt-1">{{ $notificacao->created_at?->format('d/m/Y H:i') }}</div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">Nenhuma notificação pendente.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold">Solicitar nova sessão</h3>
                            <p class="mt-2 mb-6 text-sm text-gray-500">Escolha um horário livre direto do calendário para solicitar sua sessão.</p>
                            @include('solicitar._form')
                        </div>

                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Histórico de sessões</h3>
                            <div class="space-y-3">
                                @forelse($dashboard['historico'] as $agendamento)
                                    <div class="border rounded p-3">
                                        <div class="font-medium">{{ \Carbon\Carbon::parse($agendamento->scheduled_at)->format('d/m/Y H:i') }}</div>
                                        <div class="text-sm text-gray-500">Status: {{ ucfirst($agendamento->status) }}</div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">Nenhuma sessão registrada.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Recibos</h3>
                            <div class="space-y-3">
                                @forelse($dashboard['faturas'] as $fatura)
                                    <div class="border rounded p-3">
                                        <div class="font-medium">Recibo {{ $fatura->numero_recibo ?? 'pendente' }}</div>
                                        <div class="text-sm text-gray-500">Valor: R$ {{ number_format((float) $fatura->valor, 2, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">Nenhum recibo disponível.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-white shadow sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Anotações particulares</h3>
                            <div class="space-y-3">
                                @forelse($dashboard['prontuarios'] as $prontuario)
                                    <div class="border rounded p-3">
                                        <div class="font-medium">{{ $prontuario->title ?? 'Prontuário #' . $prontuario->id }}</div>
                                        <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit((string) ($prontuario->anotacoes ?? $prontuario->content), 120) }}</div>
                                    </div>
                                @empty
                                    <div class="text-gray-500">Nenhuma anotação disponível.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
