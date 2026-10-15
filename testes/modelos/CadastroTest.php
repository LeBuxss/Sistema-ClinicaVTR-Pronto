<?php

namespace Testes\Modelos;

use Modelos\Cadastro;
use Nucleo\Database;
use Testes\Suporte\TesteBase;

class CadastroTest extends TesteBase
{
    private Cadastro $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
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

    public function testeExecutaCrudCompleto(): void
    {
        $dados = [
            'nome' => 'Teste',
            'finalizada' => 1,
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ];

        $id = $this->modelo->criar($dados);
        $registro = $this->modelo->buscar($id);

        $this->assertVerdadeiro($id > 0);
        $this->assertIgual($dados['nome'], $registro['nome']);
        $this->assertIgual(1, $registro['finalizada']);
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
            'email' => 'ana@example.com',
            'especie' => 'Teste',
            'tutor' => 'Tutor invalido',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ];

        $erros = $this->modelo->validar($dados);

        $this->assertNaoVazio($erros);
        $this->assertTemChave('nome', $erros);
        $this->assertTemChave('tutor', $erros);
    }

    public function testeValidaVeterinarioDisponivel(): void
    {
        $errosValidos = $this->modelo->validar([
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Cachorro',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'Banho',
        ]);

        $this->assertVazio($errosValidos);

        $errosInvalidos = $this->modelo->validar([
            'nome' => 'Teste',
            'email' => 'ana@example.com',
            'especie' => 'Cachorro',
            'tutor' => 'Cleiton',
            'veterinario' => 'Veterinario invalido',
            'procedimento' => 'Banho',
        ]);

        $this->assertTemChave('veterinario', $errosInvalidos);
    }
}
