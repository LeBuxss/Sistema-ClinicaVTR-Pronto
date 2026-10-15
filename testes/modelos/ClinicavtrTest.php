<?php

namespace Testes\Modelos;

use Modelos\Clinicavtr;
use Nucleo\Database;
use Testes\Suporte\TesteBase;

class ClinicavtrTest extends TesteBase
{
    private Clinicavtr $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        // Cada teste monta as proprias tabelas: a ordem em que as
        // classes rodam nao interfere no resultado.
        $this->recriarTabelas([
            'clinicavtr' => "CREATE TABLE `clinicavtr` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `campo` VARCHAR(255) NULL,
                `nome` VARCHAR(255) NULL,
                `cpf` VARCHAR(14) NULL
            )",
        ]);


        $this->modelo = new Clinicavtr();
    }

    public function testeExecutaCrudCompleto(): void
    {
        $dados = [
            'nome' => 'Teste',
            'cpf' => '529.982.247-25',
        ];

        $id = $this->modelo->criar($dados);
        $registro = $this->modelo->buscar($id);

        $this->assertVerdadeiro($id > 0);
        $this->assertIgual($dados['nome'], $registro['nome']);
        $this->assertIgual($dados['cpf'], $registro['cpf']);
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
            'cpf' => '111.111.111-11',
        ];

        $erros = $this->modelo->validar($dados);

        $this->assertNaoVazio($erros);
        $this->assertTemChave('nome', $erros);
        $this->assertTemChave('cpf', $erros);
    }
}
