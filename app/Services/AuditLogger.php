<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class AuditLogger
{
    /**
     * Registra una acción en el log con formato detallado.
     */
    public static function log(string $tipo, string $nivel, string $descripcion, ?Throwable $exception = null): void
    {
        try {
            $user = Auth::check()
                ? Auth::user()->name.' ('.Auth::user()->email.')'
                : 'Invitado';

            $ip = Request::ip();
            $url = Request::fullUrl();
            $fecha = now()->format('Y-m-d H:i:s');

            // Construir mensaje multilínea
            $mensaje = "\n[".$fecha.']';
            $mensaje .= "\nTIPO: ".strtoupper($tipo);
            $mensaje .= "\nNIVEL: ".$nivel;
            $mensaje .= "\nDESCRIPCIÓN: ".$descripcion;
            $mensaje .= "\nUSUARIO: ".$user;
            $mensaje .= "\nIP ORIGEN: ".$ip;
            $mensaje .= "\nUBICACIÓN: ".$url;

            if ($exception) {
                // Si es un error crítico, registrar detalles técnicos
                $mensaje .= "\nERROR MSG: ".$exception->getMessage();
                $mensaje .= "\nARCHIVO: ".$exception->getFile().':'.$exception->getLine();
            }

            $mensaje .= "\n--------------------------------------------------";

            // Usamos el canal 'daily' para rotación diaria de logs
            Log::channel('daily')->info($mensaje);
        } catch (\Exception $e) {
            // Fallback por si falla el logging mismo
            Log::error('Error crítico al intentar registrar log de auditoría: '.$e->getMessage());
        }
    }

    // Helpers para acciones comunes
    public static function consulta(string $descripcion): void
    {
        self::log('CONSULTA', 'info', $descripcion);
    }

    public static function insercion(string $descripcion, mixed $datos = null): void
    {
        $desc = $descripcion;
        if ($datos) {
            $desc .= ' | Datos: '.json_encode($datos);
        }
        self::log('INSERCIÓN', 'success', $desc);
    }

    public static function actualizacion(string $descripcion, mixed $cambios = null): void
    {
        $desc = $descripcion;
        if ($cambios) {
            $desc .= ' | Cambios: '.json_encode($cambios);
        }
        self::log('ACTUALIZACIÓN', 'info', $desc);
    }

    public static function eliminacion(string $descripcion, int|string|null $id = null): void
    {
        $desc = $descripcion;
        if ($id) {
            $desc .= ' | ID Eliminado: '.$id;
        }
        self::log('ELIMINACIÓN', 'warning', $desc);
    }

    public static function error(string $descripcion, ?Throwable $exception = null): void
    {
        self::log('ERROR', 'error', $descripcion, $exception);
    }

    public static function login(string $descripcion): void
    {
        self::log('LOGIN', 'info', $descripcion);
    }
}
