<?php

declare(strict_types=1);

namespace Tests\Feature\Usuarios;

use App\Modules\Usuarios\Http\Middleware\TokenBindingMiddleware;
use App\Modules\Usuarios\Models\AccessToken;
use App\Modules\Usuarios\Models\RefreshToken;
use App\Modules\Usuarios\Models\User;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class SegurancaSessaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_binding_recupera_sessao_pelo_jti_e_rejeita_mudanca_de_contexto(): void
    {
        $diretorio = sys_get_temp_dir().'/tcc-jwt-'.bin2hex(random_bytes(8));
        mkdir($diretorio, 0700);
        $storageAnterior = $this->app->storagePath();
        $chave = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($chave, $privada);
        $publica = openssl_pkey_get_details($chave)['key'];
        file_put_contents($diretorio.'/oauth-public.key', $publica);
        $this->app->useStoragePath($diretorio);
        $usuario = User::factory()->create();
        $jti = bin2hex(random_bytes(16));
        $refreshId = bin2hex(random_bytes(16));
        $store = $this->app->make(RedisRefreshTokenStore::class);
        $store->store($refreshId, $usuario->id, $jti, '127.0.0.1', 'Agente teste');
        $token = JWT::encode(['jti' => $jti, 'sub' => $usuario->id, 'exp' => time() + 300], $privada, 'RS256');
        $request = Request::create('/api/v1/contratos', 'GET', server: [
            'REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'Agente teste', 'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        try {
            $middleware = $this->app->make(TokenBindingMiddleware::class);
            $this->assertSame(200, $middleware->handle($request, static fn (): ResponseFactory|\Illuminate\Http\Response => response('ok'))->getStatusCode());
            $request->headers->set('User-Agent', 'Agente diferente');
            $this->assertSame(401, $middleware->handle($request, static fn (): ResponseFactory|\Illuminate\Http\Response => response('ok'))->getStatusCode());
            $this->assertTrue($store->isAccessTokenBlacklisted($jti));
        } finally {
            $this->app->useStoragePath($storageAnterior);
            unlink($diretorio.'/oauth-public.key');
            rmdir($diretorio);
        }
    }

    public function test_token_malformado_retorna_401_sem_detalhes_internos(): void
    {
        $this->withToken('token.invalido.valor')->getJson('/api/v1/contratos')
            ->assertUnauthorized()
            ->assertJsonMissingPath('detalhe');
    }

    public function test_relacoes_de_refresh_utilizam_access_token_sem_coluna_user_id_inexistente(): void
    {
        $usuario = User::factory()->create();
        $access = AccessToken::query()->create([
            'id' => bin2hex(random_bytes(20)), 'user_id' => $usuario->id, 'client_id' => 1,
            'name' => 'teste', 'scopes' => [], 'revoked' => false, 'expires_at' => now()->addHour(),
        ]);
        $refresh = RefreshToken::query()->create([
            'id' => bin2hex(random_bytes(20)), 'access_token_id' => $access->id, 'revoked' => false, 'expires_at' => now()->addDay(),
        ]);
        $this->assertSame($refresh->id, $usuario->refreshTokens()->firstOrFail()->id);
        $this->assertSame($usuario->id, $refresh->user->id);
    }

    public function test_reserva_e_libera_sessao_somente_para_o_titular(): void
    {
        $store = $this->app->make(RedisRefreshTokenStore::class);
        $ip = 'teste-'.bin2hex(random_bytes(8));
        $this->assertTrue($store->claimSession($ip, 1));
        $this->assertFalse($store->claimSession($ip, 2));
        $store->releaseSession($ip, 2);
        $this->assertSame(1, $store->getSessionOwner($ip));
        $store->releaseSession($ip, 1);
        $this->assertNull($store->getSessionOwner($ip));
    }
}
