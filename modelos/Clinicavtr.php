<?php

namespace Modelos;

use Nucleo\Autenticavel;
use Nucleo\Model;
use Nucleo\Validador;

class Clinicavtr extends Model
{
    use Autenticavel;

    protected string $tabela = 'clinicavtr';
    protected array $preenchiveis = ['campo', 'nome', 'cpf', 'email', 'senha'];
    protected string $ordemPadrao = 'id DESC';

    /**
     * Regras de validacao do formulario.
     * Devolve um array vazio quando esta tudo certo.
     */
    public function validar(array $dados, int|string|null $ignorarId = null): array
    {
        return (new Validador($dados))
            ->obrigatorio('nome', 'Atendimento')
            ->maximo('nome', 255)
            ->maximo('cpf', 14)
            ->personalizada('cpf', $this->cpfValido($dados['cpf'] ?? null), 'Informe um CPF valido.')
            ->email('email')
            ->personalizada('email', !$this->emailEmUso($dados['email'] ?? null, $ignorarId), 'Este e-mail ja esta cadastrado.')
            ->minimo('senha', 6)
            ->erros();
    }

    public function cpfValido(mixed $cpf): bool
    {
        if ($cpf === null || $cpf === '') {
            return true;
        }

        if (!is_string($cpf)) {
            return false;
        }

        $digitos = str_replace(['.', '-'], '', $cpf);

        if (!preg_match('/^\d{11}$/', $digitos) || preg_match('/^(\d)\1{10}$/', $digitos)) {
            return false;
        }

        for ($posicao = 9; $posicao <= 10; $posicao++) {
            $soma = 0;
            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $digitos[$indice] * (($posicao + 1) - $indice);
            }

            $digito = ($soma * 10) % 11;
            $digito = $digito === 10 ? 0 : $digito;

            if ($digito !== (int) $digitos[$posicao]) {
                return false;
            }
        }

        return true;
    }
}
