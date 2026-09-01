<div>
    <div class="mb-4">
        <label class="block">Paciente</label>
        <select name="patient_id" class="w-full border p-2">
            @foreach($patients as $pt)
                <option value="{{ $pt->id }}" {{ (old('patient_id', $prontuario->patient_id ?? '') == $pt->id) ? 'selected' : '' }}>{{ $pt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block">Título</label>
        <input name="title" value="{{ old('title', $prontuario->title ?? '') }}" class="w-full border p-2">
    </div>
    <div class="mb-4">
        <label class="block">Conteúdo</label>
        <textarea name="content" class="w-full border p-2">{{ old('content', $prontuario->content ?? '') }}</textarea>
    </div>
    <div>
        <button class="btn">Salvar</button>
    </div>
</div>
