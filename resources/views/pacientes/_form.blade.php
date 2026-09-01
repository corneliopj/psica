<div>
    <div class="mb-4">
        <label class="block">Nome</label>
        <input type="text" name="name" value="{{ old('name', $paciente->name ?? '') }}" class="w-full border p-2">
        @error('name')<div class="text-red-600">{{ $message }}</div>@enderror
    </div>
    <div class="mb-4">
        <label class="block">Email</label>
        <input type="email" name="email" value="{{ old('email', $paciente->email ?? '') }}" class="w-full border p-2">
        @error('email')<div class="text-red-600">{{ $message }}</div>@enderror
    </div>
    <div class="mb-4">
        <label class="block">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $paciente->phone ?? '') }}" class="w-full border p-2">
    </div>
    <div class="mb-4">
        <label class="block">Data de Nascimento</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', isset($paciente) ? $paciente->birth_date : '') }}" class="w-full border p-2">
    </div>
    <div class="mb-4">
        <label class="block">Notas</label>
        <textarea name="notes" class="w-full border p-2">{{ old('notes', $paciente->notes ?? '') }}</textarea>
    </div>
    <div>
        <button class="btn">Salvar</button>
    </div>
</div>
