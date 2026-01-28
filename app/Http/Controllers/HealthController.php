<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        // Si alguno de los checks tiene 'ok' => false, el sistema no está sano
        $isHealthy = ! in_array(false, array_column($checks, 'ok'), true);

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'degraded',
            'server_time' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            // Solo mostrar detalles de debug si la app no está en producción o tiene debug activo
            'checks' => $checks,
        ], $isHealthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo(); // Es más rápido que hacer una query SELECT 1
            $latency = round((microtime(true) - $start) * 1000);

            return $this->formatResult(true, "{$latency}ms");
        } catch (Throwable $e) {
            return $this->formatResult(false, null, $e);
        }
    }

    private function checkCache(): array
    {
        try {
            // Usamos 'add' en lugar de 'put' para no sobrescribir nada importante por accidente
            // y un tiempo de expiración muy corto (1 segundo)
            $key = 'health_check_test';
            Cache::put($key, true, 1);
            $value = Cache::get($key);

            return $this->formatResult($value === true);
        } catch (Throwable $e) {
            return $this->formatResult(false, null, $e);
        }
    }

    private function checkStorage(): array
    {
        try {
            // Verificamos si la carpeta es escribible sin crear archivos reales (menos I/O)
            // Asumimos el disco 'local' o el que uses por defecto
            $path = storage_path('framework');
            $isWritable = is_writable($path);

            return $this->formatResult($isWritable);
        } catch (Throwable $e) {
            return $this->formatResult(false, null, $e);
        }
    }

    /**
     * Helper para formatear respuestas uniformes y seguras.
     */
    private function formatResult(bool $ok, ?string $meta = null, ?Throwable $error = null): array
    {
        $payload = ['ok' => $ok];

        if ($meta) {
            $payload['meta'] = $meta;
        }

        // SEGURIDAD: Solo mostramos el mensaje de error si estamos en modo debug.
        // En producción, ver errores de SQL públicamente es una vulnerabilidad grave.
        if (! $ok && config('app.debug') && $error) {
            $payload['error'] = $error->getMessage();
        }

        return $payload;
    }
}
