<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Jobs;

use App\Modules\Usuarios\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AnonimizarAuditLogsAntigos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        private readonly int $diasRetencao = 180,
    ) {}

    public function handle(): void
    {
        $dataCorte = Carbon::now()->subDays($this->diasRetencao);

        AuditLog::query()
            ->where('created_at', '<', $dataCorte)
            ->whereNotNull('user_id')
            ->chunkById(500, function ($logs): void {
                foreach ($logs as $log) {
                    $this->anonimizarLog($log);
                }
            });

        Log::info('Audit logs anonimizados', [
            'data_corte' => $dataCorte->toIso8601String(),
            'dias_retencao' => $this->diasRetencao,
        ]);
    }

    private function anonimizarLog(AuditLog $log): void
    {
        DB::transaction(function () use ($log): void {
            $log->update([
                'user_id' => null,
                'ip' => $this->anonimizarIp($log->ip),
                'user_agent' => 'ANONIMIZADO',
                'metadata' => array_merge($log->metadata ?? [], [
                    'anonimizado_em' => now()->toIso8601String(),
                    'anonimizado_por' => 'lgpd_job',
                ]),
            ]);
        });
    }

    private function anonimizarIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $partes = explode('.', $ip);

            return $partes[0].'.'.$partes[1].'.xxx.xxx';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $partes = explode(':', $ip);

            return implode(':', array_slice($partes, 0, 4)).':xxxx:xxxx:xxxx:xxxx';
        }

        return 'ANONIMIZADO';
    }
}
