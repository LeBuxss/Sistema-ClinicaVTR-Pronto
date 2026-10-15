<?php

namespace Modelos;

use Nucleo\Autenticavel;
use Nucleo\Model;

class Usuario extends Model
{
    use Autenticavel;

    protected string $tabela = 'usuarios';
    protected array $preenchiveis = ['nome', 'email', 'senha'];
    protected string $ordemPadrao = 'id DESC';
}
