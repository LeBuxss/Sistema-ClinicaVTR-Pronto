<?php

namespace Testes\Controllers;

use Modelos\Fichavacinal;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class FichavacinalControllerTest extends TesteBase
{
    private Fichavacinal $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        $this->limparSessao();
        Sessao::definir('autenticacao_id', 1);

        // Cada teste monta as proprias tabelas: a ordem em que as
        // classes rodam nao interfere no resultado.
        $this->recriarTabelas([
            'fichavacinal' => "CREATE TABLE `fichavacinal` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(255) NULL,
                `vacinaaplica` VARCHAR(255) NULL,
                `lote` VARCHAR(255) NULL,
                `dataaplicacao` DATE NULL,
                `dataretorno` DATE NULL,
                `veterinarioresponsalve` VARCHAR(255) NULL,
                `finalizada` TINYINT(1) NOT NULL DEFAULT 0
            )",
        ]);


        $this->modelo = new Fichavacinal();
    }

    public function testeExecutaRotasDoCrud(): void
    {
        $lista = $this->requisitar('fichavacinal');
        $this->assertIgual(200, $lista->status);
        $this->assertContem('Ficha Animal', $lista->html);
        $this->assertContem('Nome', $lista->html);
        $this->assertNaoContem('fichavacinal/relatorio', $lista->html);
        $this->assertContem('Tipo de Vacina', $lista->html);
        $this->assertContem('Data Aplicada', $lista->html);
        $this->assertContem('Retorno', $lista->html);
        $this->assertContem('Veterinario', $lista->html);
        $this->assertNaoContem('fichaanimal', $lista->html);
        $this->assertNaoContem('nomevacina', $lista->html);

        $formulario = $this->requisitar('fichavacinal/criar');
        $this->assertIgual(200, $formulario->status);
        $this->assertContem('Salvar', $formulario->html);
        $this->assertContem('Finalizar', $formulario->html);
        $this->assertContem('Nome', $formulario->html);
        $this->assertNaoContem('Nome do animal', $formulario->html);
        $this->assertContem('name="nome"', $formulario->html);
        $this->assertContem('Tipo de Vacina', $formulario->html);
        $this->assertContem('Antirrábica', $formulario->html);
        $this->assertContem('Polivalente', $formulario->html);
        $this->assertContem('Data Aplicada', $formulario->html);
        $this->assertContem('Retorno', $formulario->html);
        $this->assertContem('Retorno vacinal daqui 30 dias.', $formulario->html);
        $this->assertContem('Veterinario', $formulario->html);
        $this->assertContem('Dr. Eclis', $formulario->html);
        $this->assertContem('Dra. Bruna', $formulario->html);
        $this->assertContem('Dra. Creuza', $formulario->html);
        $this->assertNaoContem('fichaanimal', $formulario->html);
        $this->assertNaoContem('nomevacina', $formulario->html);

        $salvar = $this->postar('fichavacinal/salvar', [
            'nome' => 'Luna',
            'vacinaaplica' => 'Antirrábica',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Dr. Eclis',
        ]);
        $this->assertVerdadeiro($salvar->redirecionouPara('fichavacinal/ver/1'));

        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);

        $id = (int) $registro['id'];
        $this->assertIgual('Antirrábica', $registro['vacinaaplica']);
        $this->assertIgual('Luna', $registro['nome']);
        $this->assertIgual('Dr. Eclis', $registro['veterinarioresponsalve']);
        $this->assertIgual(0, $registro['finalizada']);

        $ver = $this->requisitar('fichavacinal/ver/' . $id);
        $this->assertIgual(200, $ver->status);
        $this->assertContem('fichavacinal/editar/' . $id, $ver->html);

        $editar = $this->requisitar('fichavacinal/editar/' . $id);
        $this->assertIgual(200, $editar->status);
        $this->assertContem('Editar Ficha Animal', $editar->html);

        $atualizar = $this->postar('fichavacinal/atualizar/' . $id, [
            'nome' => 'Luna',
            'vacinaaplica' => 'Polivalente',
            'lote' => 'Atualizado',
            'dataaplicacao' => '2026-02-02',
            'dataretorno' => '2026-02-02',
            'veterinarioresponsalve' => 'Dra. Bruna',
        ]);
        $this->assertVerdadeiro($atualizar->redirecionouPara('fichavacinal/ver/' . $id));
        $this->assertIgual('Polivalente', $this->modelo->buscar($id)['vacinaaplica']);
        $this->assertIgual('Dra. Bruna', $this->modelo->buscar($id)['veterinarioresponsalve']);

        $excluir = $this->postar('fichavacinal/excluir/' . $id);
        $this->assertVerdadeiro($excluir->redirecionouPara('fichavacinal'));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeRecusaDadosInvalidos(): void
    {
        $resposta = $this->postar('fichavacinal/salvar', [
            'nome' => '',
            'vacinaaplica' => '',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Teste',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('fichavacinal/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaFormularioSemToken(): void
    {
        $id = $this->modelo->criar([
            'vacinaaplica' => 'Teste',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Teste',
        ]);

        $semToken = $this->postarSemToken('fichavacinal/excluir/' . $id);

        $this->assertVerdadeiro($semToken->foiRedirecionado());
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeExclusaoNaoAceitaGet(): void
    {
        $id = $this->modelo->criar([
            'vacinaaplica' => 'Teste',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Teste',
        ]);

        $porGet = $this->requisitar('fichavacinal/excluir/' . $id);

        $this->assertIgual(404, $porGet->status);
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeGeraRelatorioEmPdf(): void
    {
        $this->modelo->criar([
            'nome' => 'Luna',
            'vacinaaplica' => 'Antirrábica',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Teste',
        ]);

        $relatorio = $this->requisitar('fichavacinal/relatorio', 'GET', [
            'vacinaaplica' => 'Antirrábica',
        ]);

        $this->assertIgual(200, $relatorio->status);
        $this->assertContem('%PDF-1.4', $relatorio->html);
        $this->assertContem('Relatorio de Ficha Animal', $relatorio->html);
        $this->assertContem('Tipo de Vacina', $relatorio->html);
    }

    public function testeFinalizaFichaEAdicionaAoInicio(): void
    {
        $resposta = $this->postar('fichavacinal/salvar', [
            'nome' => 'Luna',
            'vacinaaplica' => 'Polivalente',
            'lote' => 'Lote final',
            'dataaplicacao' => '2026-04-01',
            'dataretorno' => '2026-05-01',
            'veterinarioresponsalve' => 'Dra. Bruna',
            'finalizar' => '1',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('home'));
        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);
        $this->assertIgual(1, $registro['finalizada']);
        $this->assertIgual('Polivalente', $registro['vacinaaplica']);
    }
}
