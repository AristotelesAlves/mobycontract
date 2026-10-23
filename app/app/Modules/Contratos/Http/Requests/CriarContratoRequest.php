<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\IndiceReajuste;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Http\Requests\RegrasDeDocumento;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

/**
 * Criação de contrato (R-4.5 e R-4.4). As validações que dependem do banco
 * (imóvel, modelo, locatário, garantia) são aplicadas no service, conforme
 * o padrão R-9.9: aqui ficam somente regras independentes de consulta.
 */
final class CriarContratoRequest extends RequisicaoBase
{
    use RegrasDeDocumento;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'diaVencimentoParcela' => $this->input('diaVencimentoParcela', 5),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'imovelId' => ['required', 'integer', 'exists:imoveis,id'],
            'modeloId' => ['required', 'integer', 'exists:modelos,id'],
            'prazoVigencia' => ['required', Rule::enum(PrazoVigencia::class)],
            'dataInicio' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'dataFim' => [
                'nullable',
                'required_if:prazoVigencia,Determinado',
                'prohibited_if:prazoVigencia,Indeterminado',
                'date',
                'date_format:Y-m-d',
                'after:dataInicio',
            ],
            'indiceReajuste' => ['required', Rule::enum(IndiceReajuste::class)],
            'modoInquilino' => ['required', Rule::in(['existente', 'novo'])],
            'inquilinoId' => ['nullable', 'required_if:modoInquilino,existente', 'integer', 'exists:inquilinos,id'],
            'inquilino' => ['nullable', 'required_if:modoInquilino,novo', 'array'],
            'inquilino.nome' => ['required_with:inquilino', 'string', 'max:150'],
            'inquilino.cpf' => ['required_with:inquilino', 'string', $this->regraDeDocumento(), Rule::unique('inquilinos', 'cpf')],
            'inquilino.email' => ['required_with:inquilino', 'email', 'max:180', Rule::unique('inquilinos', 'email')],
            'inquilino.telefone' => ['required_with:inquilino', 'string', 'max:30'],
            'inquilino.rg' => ['nullable', 'string', 'max:30'],
            'inquilino.dataNascimento' => ['required_with:inquilino', 'date_format:Y-m-d', 'before_or_equal:now -18 years'],
            'inquilino.estadoCivil' => ['required_with:inquilino', Rule::enum(EstadoCivil::class)],
            'inquilino.renda' => ['required_with:inquilino', 'numeric', 'min:0'],
            'inquilino.endereco' => ['nullable', 'string', 'max:255'],
            'inquilino.profissao' => ['nullable', 'string', 'max:120'],
            'comprovanteGarantia' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'valorCaucao' => ['nullable', 'numeric', 'min:0'],
            'fiadores' => ['nullable', 'array'],
            'fiadores.*.nome' => ['required', 'string', 'max:150'],
            'fiadores.*.cpf' => ['required', 'string', $this->regraDeDocumento()],
            'fiadores.*.telefone' => ['required', 'string', 'max:30'],
            'nomeLocador' => ['required', 'string', 'max:150'],
            'descontoMensal' => ['required', 'numeric', 'min:0'],
            'multaPorAtraso' => ['required', 'numeric', 'min:0', 'max:100'],
            'multaRescisao' => ['nullable', 'numeric', 'min:1', 'max:12'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'diaVencimentoParcela' => ['nullable', 'integer', 'between:1,28'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imovelId.required' => 'Selecione um imóvel.',
            'imovelId.exists' => 'O imóvel selecionado não existe.',
            'modeloId.required' => 'Selecione o modelo do contrato.',
            'modeloId.exists' => 'O modelo selecionado não existe.',
            'prazoVigencia.required' => 'Selecione o prazo de vigência.',
            'prazoVigencia.enum' => 'Selecione o prazo de vigência.',
            'dataInicio.required' => 'Informe a data de início.',
            'dataInicio.date' => 'Informe uma data de início válida.',
            'dataInicio.date_format' => 'A data de início deve estar no formato AAAA-MM-DD.',
            'dataInicio.after_or_equal' => 'A data de início não pode ser no passado.',
            'dataFim.required_if' => 'Informe o fim da vigência.',
            'dataFim.prohibited_if' => 'Prazo indeterminado não aceita data de término.',
            'dataFim.date' => 'Informe uma data de término válida.',
            'dataFim.date_format' => 'A data de término deve estar no formato AAAA-MM-DD.',
            'dataFim.after' => 'A data de término deve ser posterior à data de início.',
            'indiceReajuste.required' => 'Selecione o índice de reajuste.',
            'indiceReajuste.enum' => 'Selecione o índice de reajuste.',
            'modoInquilino.required' => 'Selecione o modo de locatário.',
            'modoInquilino.in' => 'O modo de locatário deve ser existente ou novo.',
            'inquilinoId.required_if' => 'Selecione um locatário.',
            'inquilinoId.exists' => 'O locatário selecionado não existe.',
            'inquilino.required_if' => 'Informe os dados do novo locatário.',
            'inquilino.array' => 'Informe os dados do novo locatário.',
            'inquilino.nome.required' => 'Preencha o nome do locatário.',
            'inquilino.nome.max' => 'O nome do locatário deve ter no máximo 150 caracteres.',
            'inquilino.cpf.required' => 'Preencha o documento do locatário.',
            'inquilino.cpf.unique' => 'Já existe um inquilino cadastrado com este documento (CPF).',
            'inquilino.email.required' => 'Preencha o e-mail do locatário.',
            'inquilino.email.email' => 'E-mail inválido.',
            'inquilino.email.unique' => 'Já existe um inquilino cadastrado com este e-mail.',
            'inquilino.telefone.required' => 'Preencha o telefone do locatário.',
            'inquilino.telefone.max' => 'O telefone deve ter no máximo 30 caracteres.',
            'inquilino.rg.max' => 'RG inválido.',
            'inquilino.dataNascimento.required' => 'A data de nascimento é obrigatória.',
            'inquilino.dataNascimento.date_format' => 'Data de nascimento deve estar no formato AAAA-MM-DD.',
            'inquilino.dataNascimento.before_or_equal' => 'O locatário deve ser maior de 18 anos.',
            'inquilino.estadoCivil.required' => 'Selecione o estado civil.',
            'inquilino.estadoCivil.enum' => 'Selecione o estado civil.',
            'inquilino.renda.required' => 'Informe uma renda válida.',
            'inquilino.renda.numeric' => 'Informe uma renda válida.',
            'inquilino.renda.min' => 'A renda não pode ser negativa.',
            'inquilino.endereco.required' => 'O endereço é obrigatório.',
            'valorCaucao.numeric' => 'Informe um valor de caução válido.',
            'valorCaucao.min' => 'A caução não pode ser negativa.',
            'fiadores.array' => 'Informe os fiadores.',
            'fiadores.*.nome.required' => 'Informe o nome do fiador.',
            'fiadores.*.nome.max' => 'O nome do fiador deve ter no máximo 150 caracteres.',
            'fiadores.*.cpf.required' => 'Informe o CPF do fiador.',
            'fiadores.*.telefone.required' => 'Informe o telefone do fiador.',
            'fiadores.*.telefone.max' => 'O telefone do fiador deve ter no máximo 30 caracteres.',
            'nomeLocador.required' => 'Informe o nome do proprietário.',
            'nomeLocador.max' => 'O nome do proprietário deve ter no máximo 150 caracteres.',
            'descontoMensal.required' => 'Informe o desconto mensal.',
            'descontoMensal.numeric' => 'Informe um desconto mensal válido.',
            'descontoMensal.min' => 'O desconto não pode ser negativo.',
            'multaPorAtraso.required' => 'Informe a multa por atraso.',
            'multaPorAtraso.numeric' => 'Informe uma multa por atraso válida.',
            'multaPorAtraso.min' => 'A multa por atraso não pode ser negativa.',
            'multaPorAtraso.max' => 'A multa por atraso deve estar entre 0 e 100.',
            'multaRescisao.numeric' => 'Informe uma multa de rescisão válida.',
            'multaRescisao.min' => 'A multa de rescisão deve estar entre 1 e 12 aluguéis.',
            'multaRescisao.max' => 'A multa de rescisão deve estar entre 1 e 12 aluguéis.',
            'observacoes.max' => 'As observações devem ter no máximo 2000 caracteres.',
            'diaVencimentoParcela.integer' => 'O dia de vencimento da parcela deve ser um número.',
            'diaVencimentoParcela.between' => 'O dia de vencimento da parcela deve estar entre 1 e 28.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'imovelId' => 'imóvel',
            'modeloId' => 'modelo',
            'prazoVigencia' => 'prazo de vigência',
            'dataInicio' => 'data de início',
            'dataFim' => 'data de término',
            'indiceReajuste' => 'índice de reajuste',
            'modoInquilino' => 'modo de locatário',
            'inquilinoId' => 'locatário',
            'inquilino' => 'dados do novo locatário',
            'nomeLocador' => 'nome do locador',
            'descontoMensal' => 'desconto mensal',
            'multaPorAtraso' => 'multa por atraso',
            'multaRescisao' => 'multa por rescisão',
            'diaVencimentoParcela' => 'dia de vencimento da parcela',
        ];
    }
}
