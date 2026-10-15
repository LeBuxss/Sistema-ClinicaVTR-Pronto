<?php

namespace Modelos;

use Nucleo\Model;
use Nucleo\Validador;

class Cadastro extends Model
{
    public const TUTORES_DISPONIVEIS = ['Cleiton', 'José', 'Amanda', 'Filipe', 'Claudia'];
    public const VETERINARIOS_DISPONIVEIS = ['Dr. Eclis', 'Dra. Bruna', 'Dra. Creuza'];

    public const SERVICOS_DISPONIVEIS = [
        'Banho' => 100.00,
        'Tosa' => 150.00,
        'Vacina' => 130.00,
        'Consulta Completa' => 380.00,
    ];

    protected string $tabela = 'cadastro';
    protected array $preenchiveis = ['nome', 'email', 'especie', 'tutor', 'veterinario', 'procedimento', 'finalizada'];
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
            ->email('email')
            ->maximo('email', 255)
            ->maximo('especie', 255)
            ->dentroDe('tutor', self::TUTORES_DISPONIVEIS, 'Tutor')
            ->dentroDe('veterinario', self::VETERINARIOS_DISPONIVEIS, 'Veterinario')
            ->dentroDe('procedimento', array_keys(self::SERVICOS_DISPONIVEIS), 'Serviço')
            ->erros();
    }
}
