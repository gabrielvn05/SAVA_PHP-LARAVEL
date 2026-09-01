@php
    $user = $user ?? auth()->user();
@endphp
<div class="stack">
    <label class="field">
        <span class="field__label">Cédula de identidad *</span>
        <input
            type="text"
            name="cedula"
            class="field__input"
            inputmode="numeric"
            maxlength="13"
            required
            autocomplete="off"
            value="{{ old('cedula', $user->cedula) }}"
        >
        @error('cedula') <p class="field-hint" style="color: var(--color-danger);">{{ $message }}</p> @enderror
    </label>
    <label class="field">
        <span class="field__label">Carrera *</span>
        <select name="carrera" class="field__input" required>
            <option value="">Seleccionar…</option>
            @foreach($carreras as $carrera)
                <option value="{{ $carrera['value'] }}" @selected(old('carrera', $user->carrera) === $carrera['value'])>
                    {{ $carrera['label'] }}
                </option>
            @endforeach
        </select>
        @error('carrera') <p class="field-hint" style="color: var(--color-danger);">{{ $message }}</p> @enderror
    </label>
    <label class="field">
        <span class="field__label">Número celular *</span>
        <input
            type="tel"
            name="celular"
            class="field__input"
            inputmode="tel"
            maxlength="15"
            required
            autocomplete="tel"
            value="{{ old('celular', $user->celular) }}"
        >
        @error('celular') <p class="field-hint" style="color: var(--color-danger);">{{ $message }}</p> @enderror
    </label>
</div>
