<?php

namespace Testes\Modelos;

use Modelos\Fichavacinal;
use Nucleo\Database;
use Testes\Suporte\TesteBase;

class FichavacinalTest extends TesteBase
{
    private Fichavacinal $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
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

    public function testeExecutaCrudCompleto(): void
    {
        $dados = [
            'nome' => 'Rex',
            'vacinaaplica' => 'Antirrábica',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Dr. Eclis',
        ];

        $id = $this->modelo->criar($dados);
        $registro = $this->modelo->buscar($id);

        $this->assertVerdadeiro($id > 0);
        $this->assertIgual($dados['nome'], $registro['nome']);
        $this->assertIgual($dados['vacinaaplica'], $registro['vacinaaplica']);
        $this->assertIgual($dados['veterinarioresponsalve'], $registro['veterinarioresponsalve']);
        $this->assertIgual(1, $this->modelo->contar());

        $this->assertVerdadeiro($this->modelo->atualizar($id, ['vacinaaplica' => 'Polivalente']));
        $this->assertIgual('Polivalente', $this->modelo->buscar($id)['vacinaaplica']);

        $this->assertVerdadeiro($this->modelo->excluir($id));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeValidaOsCamposObrigatorios(): void
    {
        $dados = [
            'nome' => '',
            'vacinaaplica' => 'V8',
            'lote' => 'Teste',
            'dataaplicacao' => '2026-01-01',
            'dataretorno' => '2026-01-01',
            'veterinarioresponsalve' => 'Veterinario invalido',
        ];

        $erros = $this->modelo->validar($dados);

        $this->assertNaoVazio($erros);
        $this->assertTemChave('nome', $erros);
        $this->assertTemChave('vacinaaplica', $erros);
        $this->assertTemChave('veterinarioresponsalve', $erros);
    }
}
