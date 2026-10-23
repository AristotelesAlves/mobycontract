<?php

declare(strict_types=1);

namespace Tests\Feature\Imoveis;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoAditivo;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Usuarios\Models\User;
use App\Modules\Vistorias\Models\Vistoria;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Domain\Enums\Papel;
use App\Shared\Infrastructure\Armazenamento\ArmazenadorLocal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ArquivosTest extends TestCase
{
    use RefreshDatabase;

    private string $diretorio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diretorio = sys_get_temp_dir().'/tcc-arquivos-'.bin2hex(random_bytes(8));
        $this->app->instance(ArmazenadorDeArquivo::class, new ArmazenadorLocal($this->diretorio));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->diretorio)) {
            $itens = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->diretorio, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($itens as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($this->diretorio);
        }
        parent::tearDown();
    }

    public function test_upload_leitura_autorizacao_e_exclusao_de_imagem(): void
    {
        $imovel = Imovel::factory()->create();
        $outro = Imovel::factory()->create();
        $this->getJson("/api/v1/imoveis/{$imovel->id}/fotos")->assertUnauthorized();
        $this->actingAs(User::factory()->create(['papel' => 'Operador']), 'api');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jT1sAAAAASUVORK5CYII=');
        $arquivo = UploadedFile::fake()->createWithContent('foto.png', $png);
        $resposta = $this->post("/api/v1/imoveis/{$imovel->id}/fotos", ['foto' => $arquivo], ['Accept' => 'application/json'])->assertCreated();
        $id = $resposta->json('dados.id');
        $download = $this->get("/api/v1/imoveis/{$imovel->id}/fotos/{$id}")->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame($png, $download->getContent());
        $this->getJson("/api/v1/imoveis/{$outro->id}/fotos/{$id}")->assertNotFound();
        $this->deleteJson("/api/v1/imoveis/{$imovel->id}/fotos/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('imovel_fotos', ['id' => $id]);
        $this->getJson("/api/v1/imoveis/{$imovel->id}/fotos/{$id}")->assertNotFound();
    }

    public function test_rejeita_conteudo_disfarcado_de_imagem(): void
    {
        $imovel = Imovel::factory()->create();
        $this->actingAs(User::factory()->create(), 'api')->post("/api/v1/imoveis/{$imovel->id}/fotos", [
            'foto' => UploadedFile::fake()->createWithContent('foto.png', '<script>alert(1)</script>'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertDatabaseCount('imovel_fotos', 0);
    }

    public function test_download_contrato_retorna_pdf_original(): void
    {
        $contrato = Contrato::factory()->create();
        $pdf = $this->pdfDeTeste();
        $caminho = $this->app->make(ArmazenadorDeArquivo::class)->guardar('contratos', 'documento.pdf', $pdf);
        $contrato->update(['anexo_contrato' => $caminho, 'anexo_contrato_nome' => 'documento.pdf']);
        $this->getJson("/api/v1/contratos/{$contrato->id}/documento")->assertUnauthorized();
        $resposta = $this->actingAs(User::factory()->create(), 'api')->get("/api/v1/contratos/{$contrato->id}/documento")
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame($pdf, $resposta->getContent());
        $this->assertStringContainsString('attachment', $resposta->headers->get('Content-Disposition'));
    }

    public function test_storage_rejeita_path_traversal(): void
    {
        $this->expectException(RegraNegocioException::class);
        $this->app->make(ArmazenadorDeArquivo::class)->guardar('../fora', 'foto.png', 'conteudo');
    }

    public function test_aditivo_persiste_anexo_e_rejeita_outro_contrato(): void
    {
        $aditivo = ContratoAditivo::factory()->create();
        $outro = Contrato::factory()->create();
        $pdf = $this->pdfDeTeste();
        $this->actingAs(User::factory()->create(), 'api')->post(
            "/api/v1/contratos/{$aditivo->contrato_id}/aditivos/{$aditivo->id}/documento-fisico",
            ['anexo' => UploadedFile::fake()->createWithContent('anexo.pdf', $pdf)],
            ['Accept' => 'application/json'],
        )->assertOk();
        $aditivo->refresh();
        $this->assertNotNull($aditivo->anexo);
        $resposta = $this->get("/api/v1/contratos/{$aditivo->contrato_id}/aditivos/{$aditivo->id}/documento")->assertOk();
        $this->assertSame($pdf, $resposta->getContent());
        $this->getJson("/api/v1/contratos/{$outro->id}/aditivos/{$aditivo->id}")->assertNotFound();
        $this->getJson("/api/v1/contratos/{$outro->id}/aditivos/{$aditivo->id}/documento")->assertNotFound();
    }

    public function test_vistoria_rejeita_vinculo_com_outro_contrato(): void
    {
        $contrato = Contrato::factory()->create();
        $outro = Contrato::factory()->create();
        $vistoria = Vistoria::query()->create([
            'contrato_id' => $contrato->id, 'numero' => 'VST 001', 'tipo' => 'Entrada',
            'data' => date('Y-m-d'), 'status' => 'Agendada', 'chave_acesso' => (string) Str::uuid(),
        ]);
        $this->actingAs(User::factory()->create(), 'api')->getJson("/api/v1/contratos/{$outro->id}/vistorias/{$vistoria->id}")->assertNotFound();
        $this->getJson("/api/v1/contratos/{$contrato->id}/vistorias/{$vistoria->id}")->assertOk();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jT1sAAAAASUVORK5CYII=');
        $resposta = $this->post("/api/v1/contratos/{$contrato->id}/vistorias/{$vistoria->id}/fotos", [
            'anexo' => UploadedFile::fake()->createWithContent('vistoria.png', $png),
        ], ['Accept' => 'application/json'])->assertCreated();
        $fotoId = $resposta->json('dados.id');
        $arquivo = $this->get("/api/v1/contratos/{$contrato->id}/vistorias/{$vistoria->id}/fotos/{$fotoId}")->assertOk();
        $this->assertSame($png, $arquivo->getContent());
        $this->getJson("/api/v1/contratos/{$outro->id}/vistorias/{$vistoria->id}/fotos/{$fotoId}")->assertNotFound();
        $this->actingAs(User::factory()->create(['papel' => Papel::Administrador->value]), 'api')
            ->deleteJson("/api/v1/contratos/{$contrato->id}/vistorias/{$vistoria->id}/fotos/{$fotoId}")
            ->assertNoContent();

    }
}
