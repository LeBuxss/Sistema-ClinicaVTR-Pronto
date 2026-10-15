<?php

namespace Testes\Controllers;

use Modelos\Cadastro;
use Modelos\Clinicavtr;
use Modelos\Consulta;
use Modelos\Fichavacinal;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class HomeControllerTest extends TesteBase
{
    public function preparar(): void
    {
        $this->limparSessao();
        Sessao::definir('autenticacao_id', 1);

        $this->recriarTabelas([
            'clinicavtr' => "CREATE TABLE `clinicavtr` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nome` VARCHAR(255) NULL,
                `cpf` VARCHAR(14) NULL,
                `email` VARCHAR(255) NULL,
                `senha` VARCHAR(255) NULL
            )",
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
    }

    public function testePesquisaEmTodosOsModulosSemExporDadosSensiveis(): void
    {
        (new Clinicavtr())->criar([
            'nome' => 'Busca Global',
            'cpf' => '529.982.247-25',
            'email' => 'busca@example.com',
            'senha' => 'hash-nao-exibir',
        ]);
        (new Cadastro())->criar([
            'nome' => 'Busca Global',
            'especie' => 'Canina',
            'procedimento' => 'Busca Global',
        ]);
        (new Consulta())->criar([
            'nome' => 'Busca Global',
            'especie' => 'Felina',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'horario_disponivel' => '09:00',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);
        (new Fichavacinal())->criar([
            'vacinaaplica' => 'Busca Global',
            'lote' => 'Lote 1',
        ]);

        $resultado = $this->requisitar('home/pesquisar', 'GET', ['q' => 'Busca Global']);

        $this->assertIgual(200, $resultado->status);
        $this->assertContem('Cadastro', $resultado->html);
        $this->assertContem('Atendimento', $resultado->html);
        $this->assertContem('Consulta', $resultado->html);
        $this->assertContem('Ficha Animal', $resultado->html);
        $this->assertContem('clinicavtr/ver/', $resultado->html);
        $this->assertContem('cadastro/ver/', $resultado->html);
        $this->assertContem('consulta/ver/', $resultado->html);
        $this->assertContem('fichavacinal/ver/', $resultado->html);
        $this->assertNaoContem('529.982.247-25', $resultado->html);
        $this->assertNaoContem('hash-nao-exibir', $resultado->html);
    }

    public function testeExigeDoisCaracteresParaPesquisar(): void
    {
        $resultado = $this->requisitar('home/pesquisar', 'GET', ['q' => 'a']);

        $this->assertIgual(200, $resultado->status);
        $this->assertContem('Digite ao menos 2 caracteres.', $resultado->html);
    }

    public function testeIntegraHistoricoPorNomeEUmRelatorio(): void
    {
        $conta = new Clinicavtr();
        $conta->criarComSenha([
            'nome' => 'Administradora',
            'cpf' => '529.982.247-25',
            'email' => 'admin@example.com',
        ], 'senha-segura');

        $this->postar('auth-clinicavtr/login', [
            'email' => 'admin@example.com',
            'senha' => 'senha-segura',
        ]);

        (new Cadastro())->criar([
            'nome' => 'Luna',
            'email' => 'luna@example.com',
            'especie' => 'Canina',
            'tutor' => 'Amanda',
            'veterinario' => 'Dra. Bruna',
            'procedimento' => 'Aplicacao de vacina',
            'finalizada' => 1,
        ]);
        (new Cadastro())->criar([
            'nome' => 'Max',
            'email' => 'max@example.com',
            'especie' => 'Felina',
            'tutor' => 'Cleiton',
            'veterinario' => 'Dr. Eclis',
            'procedimento' => 'PROCEDIMENTO-OUTRO',
            'finalizada' => 1,
        ]);
        (new Consulta())->criar([
            'nome' => 'Luna',
            'especie' => 'Canina',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dra. Bruna',
            'finalizada' => 1,
        ]);

        $fichas = new Fichavacinal();
        $fichas->criar([
            'nome' => 'luna',
            'vacinaaplica' => 'Antirrábica',
            'lote' => 'LOTE-ANIMAL',
            'dataaplicacao' => '2026-04-01',
            'dataretorno' => '2026-05-01',
            'veterinarioresponsalve' => 'Dr. Eclis',
            'finalizada' => 1,
        ]);
        $fichas->criar([
            'nome' => 'Luna',
            'vacinaaplica' => 'Polivalente',
            'lote' => 'LOTE-RASCUNHO',
            'finalizada' => 0,
        ]);

        $inicio = $this->requisitar('home');
        $this->assertIgual(200, $inicio->status);
        $this->assertContem('Histórico de atendimentos finalizados', $inicio->html);
        $this->assertContem('Ações rápidas', $inicio->html);
        $this->assertContem('Agendar consulta', $inicio->html);
        $this->assertContem('Registrar vacina', $inicio->html);
        $this->assertContem('<table', $inicio->html);
        $this->assertContem('rowspan="3"', $inicio->html);
        $this->assertContem('<th scope="col">Nome</th>', $inicio->html);
        $this->assertContem('Atendimento', $inicio->html);
        $this->assertContem('Consulta', $inicio->html);
        $this->assertContem('Ficha Animal', $inicio->html);
        $this->assertContem('Aplicacao de vacina', $inicio->html);
        $this->assertContem('LOTE-ANIMAL', $inicio->html);
        $this->assertContem('home/relatorio?nome=Luna', $inicio->html);
        $this->assertContem('home/relatorio?nome=Max', $inicio->html);
        $this->assertNaoContem('LOTE-RASCUNHO', $inicio->html);
        $this->assertNaoContem('fichavacinal/relatorio', $inicio->html);

        $relatorio = $this->requisitar('home/relatorio', 'GET', ['nome' => 'Luna']);
        $this->assertIgual(200, $relatorio->status);
        $this->assertContem('%PDF-1.4', $relatorio->html);
        $this->assertContem('Historico Integrado da ClinicaVTR', $relatorio->html);
        $this->assertContem('Luna', $relatorio->html);
        $this->assertContem('Atendimento', $relatorio->html);
        $this->assertContem('Consulta', $relatorio->html);
        $this->assertContem('Ficha Animal', $relatorio->html);
        $this->assertContem('LOTE-ANIMAL', $relatorio->html);
        $this->assertNaoContem('LOTE-RASCUNHO', $relatorio->html);
        $this->assertNaoContem('Max', $relatorio->html);
        $this->assertNaoContem('PROCEDIMENTO-OUTRO', $relatorio->html);
    }
}