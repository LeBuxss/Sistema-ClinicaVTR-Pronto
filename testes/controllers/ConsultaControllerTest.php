<?php

namespace Testes\Controllers;

use Modelos\Consulta;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class ConsultaControllerTest extends TesteBase
{
    private Consulta $modelo;
    private array $idsRelacoes = [];
    private array $idsRelacoesAtualizadas = [];

    public function preparar(): void
    {
        $this->limparSessao();
        Sessao::definir('autenticacao_id', 1);

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

    public function testeExecutaRotasDoCrud(): void
    {
        $lista = $this->requisitar('consulta');
        $this->assertIgual(200, $lista->status);
        $this->assertContem('Nome', $lista->html);
        $this->assertNaoContem('consulta/relatorio', $lista->html);

        $formulario = $this->requisitar('consulta/criar');
        $this->assertIgual(200, $formulario->status);
        $this->assertContem('Salvar', $formulario->html);
        $this->assertContem('Finalizar', $formulario->html);
        $this->assertContem('horario_disponivel', $formulario->html);
        $this->assertContem('Finalizar', $formulario->html);
        $this->assertContem('data_consulta', $formulario->html);
        $this->assertContem('min="' . date('Y-m-d') . '"', $formulario->html);
        $this->assertContem('data-data-agendamento', $formulario->html);
        $this->assertContem('data-horario-agendamento', $formulario->html);
        $this->assertContem('veterinario_disponivel', $formulario->html);
        $this->assertContem('name="raca"', $formulario->html);
        $this->assertContem('name="telefone_tutor"', $formulario->html);
        $this->assertContem('name="queixa_principal"', $formulario->html);
        $this->assertContem('name="observacoes_clinicas"', $formulario->html);
        $this->assertContem('Dr. Eclis', $formulario->html);
        $this->assertContem('Dra. Bruna', $formulario->html);
        $this->assertContem('Dra. Creuza', $formulario->html);
        $this->assertContem('Cleiton', $formulario->html);
        $this->assertContem('José', $formulario->html);
        $this->assertContem('Amanda', $formulario->html);
        $this->assertContem('Filipe', $formulario->html);
        $this->assertContem('Claudia', $formulario->html);

        $salvar = $this->postar('consulta/salvar', [
            'nome' => 'Teste',
            'especie' => 'Teste',
            'raca' => 'Sem raça definida',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Cleiton',
            'telefone_tutor' => '(11) 99999-0000',
            'queixa_principal' => 'Retorno de rotina',
            'observacoes_clinicas' => 'Sem alterações aparentes',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);
        $this->assertVerdadeiro($salvar->redirecionouPara('consulta/ver/1'));

        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);

        $id = (int) $registro['id'];
        $this->assertIgual('Teste', $registro['nome']);
        $this->assertIgual('Sem raça definida', $registro['raca']);
        $this->assertIgual('(11) 99999-0000', $registro['telefone_tutor']);
        $this->assertIgual('Retorno de rotina', $registro['queixa_principal']);
        $this->assertIgual('Sem alterações aparentes', $registro['observacoes_clinicas']);
        $this->assertIgual(date('Y-m-d', strtotime('+1 day')), $registro['data_consulta']);
        $this->assertIgual('Cleiton', $registro['tutor']);
        $this->assertIgual('09:30:00', $registro['horario_disponivel']);
        $this->assertIgual('Dr. Eclis', $registro['veterinario_disponivel']);
        $this->assertIgual(0, $registro['finalizada']);
        $this->assertIgual(0, $registro['finalizada']);

        $ver = $this->requisitar('consulta/ver/' . $id);
        $this->assertIgual(200, $ver->status);
        $this->assertContem('consulta/editar/' . $id, $ver->html);

        $editar = $this->requisitar('consulta/editar/' . $id);
        $this->assertIgual(200, $editar->status);
        $this->assertContem('Editar Consulta', $editar->html);

        $atualizar = $this->postar('consulta/atualizar/' . $id, [
            'nome' => 'Atualizado',
            'especie' => 'Atualizado',
            'raca' => 'SRD',
            'datanascimente' => '2026-02-02',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'sexo' => 'Atualizado',
            'peso' => 20.5,
            'tutor' => 'José',
            'telefone_tutor' => '(11) 98888-0000',
            'queixa_principal' => 'Coceira',
            'observacoes_clinicas' => 'Solicitada avaliação dermatológica',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);
        $this->assertVerdadeiro($atualizar->redirecionouPara('consulta/ver/' . $id));
        $this->assertIgual('Atualizado', $this->modelo->buscar($id)['nome']);
        $this->assertIgual('SRD', $this->modelo->buscar($id)['raca']);
        $this->assertIgual('Coceira', $this->modelo->buscar($id)['queixa_principal']);
        $this->assertIgual('(11) 98888-0000', $this->modelo->buscar($id)['telefone_tutor']);
        $this->assertIgual('Solicitada avaliação dermatológica', $this->modelo->buscar($id)['observacoes_clinicas']);
        $this->assertIgual('José', $this->modelo->buscar($id)['tutor']);
        $this->assertIgual('09:30:00', $this->modelo->buscar($id)['horario_disponivel']);
        $this->assertIgual('Dr. Eclis', $this->modelo->buscar($id)['veterinario_disponivel']);

        $excluir = $this->postar('consulta/excluir/' . $id);
        $this->assertVerdadeiro($excluir->redirecionouPara('consulta'));
        $this->assertNulo($this->modelo->buscar($id));
    }

    public function testeRecusaDadosInvalidos(): void
    {
        $resposta = $this->postar('consulta/salvar', [
            'nome' => '',
            'especie' => 'Teste',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Teste',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('consulta/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaFormularioSemToken(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
            'especie' => 'Teste',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Teste',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $semToken = $this->postarSemToken('consulta/excluir/' . $id);

        $this->assertVerdadeiro($semToken->foiRedirecionado());
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeExclusaoNaoAceitaGet(): void
    {
        $id = $this->modelo->criar([
            'nome' => 'Teste',
            'especie' => 'Teste',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Teste',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $porGet = $this->requisitar('consulta/excluir/' . $id);

        $this->assertIgual(404, $porGet->status);
        $this->assertNaoNulo($this->modelo->buscar($id));
    }

    public function testeGeraRelatorioEmPdf(): void
    {
        $this->modelo->criar([
            'nome' => 'Teste',
            'especie' => 'Teste',
            'datanascimente' => '2026-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Teste',
        ]);

        $relatorio = $this->requisitar('consulta/relatorio', 'GET', [
            'nome' => 'Teste',
        ]);

        $this->assertIgual(200, $relatorio->status);
        $this->assertContem('%PDF-1.4', $relatorio->html);
        $this->assertContem('Relatorio de consulta', $relatorio->html);
    }

    public function testeRecusaAgendamentoEmDataPassada(): void
    {
        $resposta = $this->postar('consulta/salvar', [
            'nome' => 'Teste',
            'especie' => 'Teste',
            'datanascimente' => '2020-01-01',
            'data_consulta' => date('Y-m-d', strtotime('-1 day')),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Cleiton',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('consulta/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeFinalizaConsulta(): void
    {
        $resposta = $this->postar('consulta/salvar', [
            'nome' => 'Luna',
            'especie' => 'Canina',
            'datanascimente' => '2020-01-01',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'sexo' => 'Femea',
            'peso' => 8.5,
            'tutor' => 'Amanda',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dra. Bruna',
            'finalizar' => '1',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('home'));
        $registro = $this->modelo->todos()[0] ?? null;
        $this->assertNaoNulo($registro);
        $this->assertIgual(1, $registro['finalizada']);
    }

    public function testeRecusaConflitoEPreservaFormulario(): void
    {
        $this->modelo->criar([
            'nome' => 'Consulta existente',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $resposta = $this->postar('consulta/salvar', [
            'nome' => 'Animal conflito',
            'especie' => 'Felino',
            'datanascimente' => '2020-05-14',
            'data_consulta' => date('Y-m-d', strtotime('+1 day')),
            'sexo' => 'Femea',
            'peso' => '4.25',
            'tutor' => 'Amanda',
            'horario_disponivel' => '09:30',
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('consulta/criar'));
        $this->assertIgual(1, $this->modelo->contar());

        $formulario = $this->requisitar('consulta/criar');
        $this->assertContem('Este veterinário já possui outro atendimento nessa data e horário.', $formulario->html);
        $this->assertContem('value="Animal conflito"', $formulario->html);
        $this->assertContem('value="Felino"', $formulario->html);
        $this->assertContem('value="2020-05-14"', $formulario->html);
        $this->assertContem('value="' . date('Y-m-d', strtotime('+1 day')) . '"', $formulario->html);
        $this->assertContem('value="Femea"', $formulario->html);
        $this->assertContem('value="4.25"', $formulario->html);
        $this->assertContem('value="Amanda" selected', $formulario->html);
        $this->assertContem('value="09:30"', $formulario->html);
        $this->assertContem('value="Dr. Eclis" selected', $formulario->html);
    }

    public function testeRecusaHorarioPassadoHoje(): void
    {
        $agora = new \DateTimeImmutable('now');
        if ($agora->format('H:i:s') === '00:00:00') {
            return;
        }

        $horarioPassado = $agora->format('H:i') === '00:00'
            ? '00:00'
            : $agora->modify('-1 minute')->format('H:i');

        $resposta = $this->postar('consulta/salvar', [
            'nome' => 'Teste horario passado',
            'especie' => 'Teste',
            'datanascimente' => '2020-01-01',
            'data_consulta' => date('Y-m-d'),
            'sexo' => 'Teste',
            'peso' => 10.5,
            'tutor' => 'Cleiton',
            'horario_disponivel' => $horarioPassado,
            'veterinario_disponivel' => 'Dr. Eclis',
        ]);

        $this->assertVerdadeiro($resposta->redirecionouPara('consulta/criar'));
        $this->assertIgual(0, $this->modelo->contar());
    }
}
