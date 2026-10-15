<?php

namespace Testes\Controllers;

use Modelos\Clinicavtr;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class ClinicavtrControllerTest extends TesteBase
{
    private Clinicavtr $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        $this->limparSessao();
        Sessao::definir('autenticacao_id', 1);

        // Cada teste monta as proprias tabelas: a ordem em que as
        // classes rodam nao interfere no resultado.
        $this->recriarTabelas([
            'clinicavtr' => "CREATE TABLE `clinicavtr` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `campo` VARCHAR(255) NULL,
                `nome` VARCHAR(255) NULL,
                `cpf` VARCHAR(14) NULL,
                `email` VARCHAR(255) NULL,
                `senha` VARCHAR(255) NULL
            )",
        ]);


        $this->modelo = new Clinicavtr();
    }

    public function testeExecutaRotasDoCrud(): void
    {
        $lista = $this->requisitar('clinicavtr');
        $this->assertIgual(200, $lista->status);
        $this->assertContem('Nome', $lista->html);
        $this->assertContem('CPF', $lista->html);
        $this->assertContem('Cadastro', $lista->html);
        $this->assertContem('clinicavtr/relatorio', $lista->html);

        $formulario = $this->requisitar('clinicavtr/criar');
        $this->assertIgual(200, $formulario->status);
        $this->assertContem('Salvar', $formulario->html);

        $salvar = $this->postar('clinicavtr/salvar', [
            'nome' => 'Teste',
            'cpf' => '529.982.247-25',
        ]);
        $this->assertVerdadeiro($salvar->redirecionouPara('clinicavtr/ver/1'));

        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);

        $id = (int) $registro['id'];
        $this->assertIgual('Teste', $registro['nome']);
        $this->assertIgual('529.982.247-25', $registro['cpf']);

        $ver = $this->requisitar('clinicavtr/ver/' . $id);
        $this->assertIgual(200, $ver->status);
        $this->assertContem('clinicavtr/editar/' . $id, $ver->html);

        $editar = $this->requisitar('clinicavtr/editar/' . $id);
        $this->assertIgual(200, $editar->status);
        $this->assertContem('Editar Cadastro', $editar->html);

        $atualizar = $this->postar('clinicavtr/atualizar/' . $id, [
            'nome' => 'Atualizado',
            'cpf' => '935.411.347-80',
        ]);
        $this->assertVerdadeiro($atualizar->redirecionouPara('clinicavtr/ver/' . $id));
        $this->assertIgual('Atualizado', $this->modelo->buscar($id)['nome']);
        $this->assertIgual('935.411.347-80', $this->modelo->buscar($id)['cpf']);

        $excluir = $this->postar('clinicavtr/excluir/' . $id);
        $this->assertVerdadeiro($excluir->redirecionouPara('clinicavtr'));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeRecusaDadosInvalidos(): void
    {
        $resposta = $this->postar('clinicavtr/salvar', [
            'nome' => '',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('clinicavtr/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaFormularioSemToken(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
        ]);

        $semToken = $this->postarSemToken('clinicavtr/excluir/' . $id);

        $this->assertVerdadeiro($semToken->foiRedirecionado());
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeExclusaoNaoAceitaGet(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
        ]);

        $porGet = $this->requisitar('clinicavtr/excluir/' . $id);

        $this->assertIgual(404, $porGet->status);
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeGeraRelatorioEmPdf(): void
    {
        $this->modelo->criar([
            'nome' => 'Teste',
        ]);

        $relatorio = $this->requisitar('clinicavtr/relatorio', 'GET', [
            'nome' => 'Teste',
        ]);

        $this->assertIgual(200, $relatorio->status);
        $this->assertContem('%PDF-1.4', $relatorio->html);
        $this->assertContem('Relatorio de Cadastro', $relatorio->html);
    }
}
