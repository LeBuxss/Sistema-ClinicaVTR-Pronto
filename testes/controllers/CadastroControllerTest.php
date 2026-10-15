<?php

namespace Testes\Controllers;

use Modelos\Cadastro;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class CadastroControllerTest extends TesteBase
{
    private Cadastro $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        $this->limparSessao();
        Sessao::definir('autenticacao_id', 1);

        // Cada teste monta as proprias tabelas: a ordem em que as
        // classes rodam nao interfere no resultado.
        $this->recriarTabelas([
            'cadastro' => "CREATE TABLE `cadastro` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(255) NULL,
                `email` VARCHAR(255) NULL,
                `especie` VARCHAR(255) NULL,
                `tutor` VARCHAR(255) NULL,
                `veterinario` VARCHAR(255) NULL,
                `procedimento` VARCHAR(255) NULL,
                `finalizada` TINYINT(1) NOT NULL DEFAULT 0
            )",
        ]);


        $this->modelo = new Cadastro();
    }

    public function testeExecutaRotasDoCrud(): void
    {
        $lista = $this->requisitar('cadastro');
        $this->assertIgual(200, $lista->status);
        $this->assertContem('Atendimento', $lista->html);
        $this->assertNaoContem('cadastro/relatorio', $lista->html);
        $this->assertContem('Nome', $lista->html);

        $formulario = $this->requisitar('cadastro/criar');
        $this->assertIgual(200, $formulario->status);
        $this->assertContem('Salvar', $formulario->html);
        $this->assertContem('Finalizar', $formulario->html);
        $this->assertContem('Cleiton', $formulario->html);
        $this->assertContem('José', $formulario->html);
        $this->assertContem('Amanda', $formulario->html);
        $this->assertContem('Filipe', $formulario->html);
        $this->assertContem('Claudia', $formulario->html);

        $salvar = $this->postar('cadastro/salvar', [
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);
        $this->assertVerdadeiro($salvar->redirecionouPara('cadastro/ver/1'));

        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);

        $id = (int) $registro['id'];
        $this->assertIgual('Teste', $registro['nome']);
        $this->assertIgual('Cleiton', $registro['tutor']);
        $this->assertIgual(0, $registro['finalizada']);

        $ver = $this->requisitar('cadastro/ver/' . $id);
        $this->assertIgual(200, $ver->status);
        $this->assertContem('cadastro/editar/' . $id, $ver->html);

        $editar = $this->requisitar('cadastro/editar/' . $id);
        $this->assertIgual(200, $editar->status);
        $this->assertContem('Editar Atendimento', $editar->html);

        $atualizar = $this->postar('cadastro/atualizar/' . $id, [
            'nome' => 'Atualizado',
            'email' => 'maria@example.com',
            'especie' => 'Atualizado',
            'tutor' => 'José',
            'veterinario' => 'Dra. Bruna',
            'procedimento' => 'Tosa',
        ]);
        $this->assertVerdadeiro($atualizar->redirecionouPara('cadastro/ver/' . $id));
        $this->assertIgual('Atualizado', $this->modelo->buscar($id)['nome']);
        $this->assertIgual('José', $this->modelo->buscar($id)['tutor']);

        $excluir = $this->postar('cadastro/excluir/' . $id);
        $this->assertVerdadeiro($excluir->redirecionouPara('cadastro'));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeFinalizaAtendimento(): void
    {
        $resposta = $this->postar('cadastro/salvar', [
            'nome' => 'Luna',
            'email' => 'luna@example.com',
            'especie' => 'Canina',
            'tutor' => 'Amanda',
            'veterinario' => 'Dra. Bruna',
            'procedimento' => 'Consulta Completa',
            'finalizar' => '1',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('home'));
        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);
        $this->assertIgual(1, $registro['finalizada']);
    }

    public function testeRecusaDadosInvalidos(): void
    {
        $resposta = $this->postar('cadastro/salvar', [
            'nome' => '',
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('cadastro/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaFormularioSemToken(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);

        $semToken = $this->postarSemToken('cadastro/excluir/' . $id);

        $this->assertVerdadeiro($semToken->foiRedirecionado());
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeExclusaoNaoAceitaGet(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);

        $porGet = $this->requisitar('cadastro/excluir/' . $id);

        $this->assertIgual(404, $porGet->status);
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeGeraRelatorioEmPdf(): void
    {
        $this->modelo->criar([
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);

        $relatorio = $this->requisitar('cadastro/relatorio', 'GET', [
            'nome' => 'Teste',
        ]);

        $this->assertIgual(200, $relatorio->status);
        $this->assertContem('%PDF-1.4', $relatorio->html);
        $this->assertContem('Relatorio de Atendimento', $relatorio->html);
    }
}
