<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \LogicException('Os dados e usuários de demonstração não podem ser semeados em produção.');
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@mobycontract.com'],
            [
                'nome' => 'Administrador',
                'password' => 'senha1234',
                'papel' => Papel::Administrador->value,
                'ativo' => true,
                'email_verificado_em' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'operador@mobycontract.com'],
            [
                'nome' => 'Operador',
                'password' => 'senha1234',
                'papel' => Papel::Operador->value,
                'ativo' => true,
                'email_verificado_em' => now(),
            ],
        );

        $this->call(ImovelSeeder::class);
        $this->call(InquilinoSeeder::class);
        $this->call(ModeloSeeder::class);
        $this->call(ContratoSeeder::class);
        $this->call(LeadSeeder::class);
        $this->call(InteressadoSeeder::class);
        $this->call(AgendamentoSeeder::class);
        $this->call(ManutencaoSeeder::class);

        $this->command?->info('Seed concluido com sucesso!');
        $this->command?->info('Login: admin@mobycontract.com / senha: senha1234');
    }
}
