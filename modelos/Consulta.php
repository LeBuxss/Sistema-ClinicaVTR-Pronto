<?php

namespace Modelos;

use Nucleo\Model;
use Nucleo\Validador;

class Consulta extends Model
{
    public const TUTORES_DISPONIVEIS = ['Cleiton', 'José', 'Amanda', 'Filipe', 'Claudia'];

    protected string $tabela = 'consulta';
    protected array $preenchiveis = ['nome', 'especie', 'raca', 'datanascimente', 'data_consulta', 'sexo', 'peso', 'tutor', 'telefone_tutor', 'queixa_principal', 'observacoes_clinicas', 'horario_disponivel', 'veterinario_disponivel', 'finalizada'];
    protected string $ordemPadrao = 'id DESC';

    /**
     * Regras de validacao do formulario.
     * Devolve um array vazio quando esta tudo certo.
     */
    public function validar(array $dados, int|string|null $ignorarId = null): array
    {
        return (new Validador($dados))
            ->obrigatorio('nome')
            ->maximo('nome', 255)
            ->maximo('especie', 255)
            ->maximo('raca', 120)
            ->obrigatorio('data_consulta', 'Data da consulta')
            ->personalizada(
                'data_consulta',
                $this->dataConsultaValida($dados['data_consulta'] ?? null),
                'A data da consulta nao pode ser anterior a hoje.'
            )
            ->maximo('sexo', 255)
            ->numerico('peso')
            ->dentroDe('tutor', self::TUTORES_DISPONIVEIS, 'Tutor')
            ->maximo('telefone_tutor', 30)
            ->maximo('queixa_principal', 500)
            ->maximo('observacoes_clinicas', 10000)
            ->obrigatorio('horario_disponivel', 'Horario disponivel')
            ->personalizada(
                'horario_disponivel',
                $this->dataHoraAgendamentoValida(
                    $dados['data_consulta'] ?? null,
                    $dados['horario_disponivel'] ?? null
                ),
                'O horario da consulta precisa ser futuro quando a data selecionada for hoje.'
            )
            ->obrigatorio('veterinario_disponivel', 'Veterinario disponivel')
            ->dentroDe('veterinario_disponivel', ['Dr. Eclis', 'Dra. Bruna', 'Dra. Creuza'])
            ->personalizada(
                'horario_disponivel',
                !$this->temConflitoAgendamento($dados, $ignorarId),
                'Este veterinário já possui outro atendimento nessa data e horário. Escolha outro horário ou veterinário.'
            )
            ->erros();
    }

    private function temConflitoAgendamento(array $dados, int|string|null $ignorarId): bool
    {
        $data = $dados['data_consulta'] ?? null;
        $horario = $dados['horario_disponivel'] ?? null;
        $veterinario = $dados['veterinario_disponivel'] ?? null;

        if (
            !$this->dataConsultaValida($data)
            || !is_string($horario)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horario)
            || !is_string($veterinario)
            || !in_array($veterinario, ['Dr. Eclis', 'Dra. Bruna', 'Dra. Creuza'], true)
        ) {
            return false;
        }

        $sql = 'SELECT id FROM ' . $this->tabelaProtegida()
            . ' WHERE data_consulta = ? AND horario_disponivel = ? AND veterinario_disponivel = ?';
        $parametros = [$data, $horario . ':00', $veterinario];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> ?';
            $parametros[] = $ignorarId;
        }

        return $this->consultar($sql . ' LIMIT 1', $parametros) !== [];
    }

    private function dataConsultaValida(mixed $valor): bool
    {
        if (!is_string($valor) || $valor === '') {
            return false;
        }

        $data = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        $erros = \DateTimeImmutable::getLastErrors();

        return $data !== false
            && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
            && $data->format('Y-m-d') === $valor
            && $data >= new \DateTimeImmutable('today');
    }

    private function dataHoraAgendamentoValida(mixed $data, mixed $horario): bool
    {
        if (
            !$this->dataConsultaValida($data)
            || !is_string($horario)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horario)
        ) {
            return false;
        }

        $agendamento = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $data . ' ' . $horario);
        $erros = \DateTimeImmutable::getLastErrors();

        return $agendamento !== false
            && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
            && $agendamento >= new \DateTimeImmutable('now');
    }
}
