<?php

namespace Testes\Modelos;

use Modelos\Consulta;
use Nucleo\Database;
use Testes\Suporte\TesteBase;

class ConsultaTest extends TesteBase
{
    private Consulta $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        // Cada teste monta as proprias tabelas: a ordem em que as
        // classes rodam nao interfere no resultado.
        $this->recriarTabelas([
            'consulta' => "CREATE TABLE `consulta` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(255) NULL,
                `especie` VARCHAR(255) NULL,
                `raca` VARCHAR(120) NULL,
                `datanascimente` DATE NULL,
                `data_consulta` DATE NULL,
                `sexo` VARCHAR(255) NULL,
                `peso` DECIMAL(12,2) NULL,
                `tutor` VARCHAR(255) NULL,
                `telefone_tutor` VARCHAR(30) NULL,
                `queixa_principal` VARCHAR(500) NULL,
                `observacoes_clinicas` MEDIUMTEXT NULL,
                `horario_disponivel` TIME NULL,
                `veterinario_disponivel` VARCHAR(255) NULL,
                `finalizada` TINYINT(1) NOT NULL DEFAULT 0
            )",
        ]);


        $this->modelo = new Consulta();
    }

    public function testeExecutaCrudCompleto(): void
    {
        $dados = [
            'nome' => 'Teste',
            'especie' => 'Teste',
            'raca' => 'SRD',
            'telefone_tutor' => '(11) 99999-0000',
            'queixa_principal' => 'Retorno',
            'observacoes_clinicas' => 'Sem alterações',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Tutor invalido',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
            'finalizada' => 1,
        ];

        $id = $this->modelo->criar($dados);
        $registro = $this->modelo->buscar($id);

        $this->assertVerdadeiro($id > 0);
        $this->assertIgual($dados['nome'], $registro['nome']);
        $this->assertIgual($dados['raca'], $registro['raca']);
        $this->assertIgual($dados['telefone_tutor'], $registro['telefone_tutor']);
        $this->assertIgual($dados['queixa_principal'], $registro['queixa_principal']);
        $this->assertIgual($dados['observacoes_clinicas'], $registro['observacoes_clinicas']);
        $this->assertIgual(1, $registro['finalizada']);
        $this->assertIgual('09:30:00', $registro['horario_disponivel']);
        $this->assertIgual('Dr. Eclis', $registro['veterinario_disponivel']);
        $this->assertIgual(1, $this->modelo->contar());

        $this->assertVerdadeiro($this->modelo->atualizar($id, ['nome' => 'Atualizado']));
        $this->assertIgual('Atualizado', $this->modelo->buscar($id)['nome']);

        $this->assertVerdadeiro($this->modelo->excluir($id));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeValidaOsCamposObrigatorios(): void
    {
        $dados = [
            'nome' => '',
            'especie' => 'Teste',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d', strtotime('-1 day')),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Teste',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Medico invalido',
        ];

        $erros = $this->modelo->validar($dados);

        $this->assertNaoVazio($erros);
        $this->assertTemChave('nome', $erros);
        $this->assertTemChave('data_consulta', $erros);
        $this->assertTemChave('tutor', $erros);
        $this->assertTemChave('veterinario_disponivel', $erros);
    }

}
