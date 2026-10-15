<?php

namespace Modelos;

use Nucleo\Model;
use Nucleo\Validador;

class Fichavacinal extends Model
{
    public const TIPOS_VACINA = ['Antirrábica', 'Polivalente'];
    public const VETERINARIOS_DISPONIVEIS = ['Dr. Eclis', 'Dra. Bruna', 'Dra. Creuza'];

    protected string $tabela = 'fichavacinal';
    protected array $preenchiveis = ['nome', 'vacinaaplica', 'lote', 'dataaplicacao', 'dataretorno', 'veterinarioresponsalve', 'finalizada'];
    protected string $ordemPadrao = 'id DESC';

    /**
     * Regras de validacao do formulario.
     * Devolve um array vazio quando esta tudo certo.
     */
    public function validar(array $dados, int|string|null $ignorarId = null): array
    {
        return (new Validador($dados))
            ->obrigatorio('nome', 'Nome')
            ->maximo('nome', 255)
            ->obrigatorio('vacinaaplica', 'Tipo de Vacina')
            ->dentroDe('vacinaaplica', self::TIPOS_VACINA, 'Tipo de Vacina')
            ->maximo('lote', 255)
            ->dentroDe('veterinarioresponsalve', self::VETERINARIOS_DISPONIVEIS, 'Veterinario')
            ->erros();
    }
}
