<?php

namespace App\Services;

use App\Enums\AppRole;
use App\Support\Carreras;

class AccountRequestValidator
{
    /** @return array{ok: true, data: array<string, string>}|array{ok: false, aviso: string} */
    public function validate(array $raw): array
    {
        $email = strtolower(trim($raw['email'] ?? ''));
        $nombres = trim($raw['nombres'] ?? '');
        $apellidos = trim($raw['apellidos'] ?? '');
        $cedula = preg_replace('/\D/', '', $raw['cedula'] ?? '') ?? '';
        $celular = preg_replace('/[^\d+]/', '', ltrim($raw['celular'] ?? '', '+')) ?? '';
        $carrera = trim($raw['carrera'] ?? '');
        $rol = trim($raw['rol_solicitado'] ?? '') ?: AppRole::Administrativo->value;

        if ($email === '' || $nombres === '' || $apellidos === '' || $cedula === '' || $celular === '' || $carrera === '') {
            return ['ok' => false, 'aviso' => 'datos_incompletos'];
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'aviso' => 'correo_invalido'];
        }

        if (! Carreras::isValid($carrera)) {
            return ['ok' => false, 'aviso' => 'carrera_invalida'];
        }

        if (! in_array($rol, [
            AppRole::Docente->value,
            AppRole::Administrativo->value,
            AppRole::Mantenimiento->value,
            AppRole::Secretaria->value,
            AppRole::Decano->value,
        ], true)) {
            return ['ok' => false, 'aviso' => 'rol_invalido'];
        }

        if (strlen($cedula) < 10 || strlen($cedula) > 13) {
            return ['ok' => false, 'aviso' => 'cedula_invalida'];
        }

        if (strlen($celular) < 9 || strlen($celular) > 15) {
            return ['ok' => false, 'aviso' => 'celular_invalido'];
        }

        return [
            'ok' => true,
            'data' => [
                'email' => $email,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'cedula' => $cedula,
                'celular' => $celular,
                'carrera' => $carrera,
                'jornada' => '',
                'rol_solicitado' => $rol,
            ],
        ];
    }
}
