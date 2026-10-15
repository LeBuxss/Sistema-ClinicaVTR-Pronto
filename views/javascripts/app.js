/* =====================================================================
   JavaScript padrao do framework
   Carregado pelo template em views/template/layout.php
   ===================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    var carrosselBemEstar = document.querySelector('[data-bem-estar-marquee]');
    var botaoPausaCarrossel = document.querySelector('[data-carrossel-pausa]');

    if (carrosselBemEstar && botaoPausaCarrossel) {
        botaoPausaCarrossel.addEventListener('click', function () {
            var estaPausado = botaoPausaCarrossel.dataset.pausado === 'true';

            if (estaPausado) {
                carrosselBemEstar.classList.remove('is-paused');
                carrosselBemEstar.classList.add('is-playing');
                botaoPausaCarrossel.innerHTML = '<span aria-hidden="true">&#10074;&#10074;</span> Pausar';
                botaoPausaCarrossel.setAttribute('aria-label', 'Pausar apresentação');
                botaoPausaCarrossel.dataset.pausado = 'false';
                return;
            }

            carrosselBemEstar.classList.add('is-paused');
            carrosselBemEstar.classList.remove('is-playing');
            botaoPausaCarrossel.innerHTML = '<span aria-hidden="true">&#9654;</span> Continuar';
            botaoPausaCarrossel.setAttribute('aria-label', 'Retomar apresentação');
            botaoPausaCarrossel.dataset.pausado = 'true';
        });
    }

    /* -----------------------------------------------------------------
       1. Confirmacao antes de acoes destrutivas
       Basta colocar data-confirmar="mensagem" no formulario ou link:

           <form method="POST" data-confirmar="Excluir mesmo?">
       ----------------------------------------------------------------- */
    document.querySelectorAll('[data-confirmar]').forEach(function (elemento) {
        elemento.addEventListener('submit', function (evento) {
            if (!window.confirm(elemento.dataset.confirmar)) {
                evento.preventDefault();
            }
        });

        elemento.addEventListener('click', function (evento) {
            if (elemento.tagName === 'A' && !window.confirm(elemento.dataset.confirmar)) {
                evento.preventDefault();
            }
        });
    });

    /* -----------------------------------------------------------------
       2. Some com as mensagens de sucesso depois de alguns segundos
       ----------------------------------------------------------------- */
    document.querySelectorAll('.alerta--sucesso').forEach(function (alerta) {
        window.setTimeout(function () {
            alerta.style.transition = 'opacity .4s';
            alerta.style.opacity = '0';
            window.setTimeout(function () { alerta.remove(); }, 400);
        }, 4000);
    });

    /* -----------------------------------------------------------------
       3. Evita envio duplicado ao clicar duas vezes em "Salvar"
       ----------------------------------------------------------------- */
    document.querySelectorAll('form.formulario').forEach(function (formulario) {
        formulario.addEventListener('submit', function () {
            var botao = formulario.querySelector('button[type="submit"]');

            if (botao) {
                window.setTimeout(function () {
                    botao.disabled = true;
                    botao.textContent = 'Aguarde...';
                }, 0);
            }
        });
    });

    /* -----------------------------------------------------------------
       4. Coloca o cursor no primeiro campo do formulario
       ----------------------------------------------------------------- */
    var primeiroCampo = document.querySelector('.formulario input, .formulario select');

    if (primeiroCampo) {
        primeiroCampo.focus();
    }

    /* -----------------------------------------------------------------
       5. Orientacao exibida quando a data de retorno vacinal e preenchida
       ----------------------------------------------------------------- */
    var dataRetornoVacinal = document.querySelector('[data-retorno-vacinal]');
    var mensagemRetornoVacinal = document.querySelector('[data-retorno-vacinal-mensagem]');

    if (dataRetornoVacinal && mensagemRetornoVacinal) {
        var atualizarMensagemRetorno = function () {
            mensagemRetornoVacinal.classList.toggle('d-none', !dataRetornoVacinal.value);
        };

        dataRetornoVacinal.addEventListener('input', atualizarMensagemRetorno);
        dataRetornoVacinal.addEventListener('change', atualizarMensagemRetorno);
        atualizarMensagemRetorno();
    }

    var dataAgendamento = document.querySelector('[data-data-agendamento]');
    var horarioAgendamento = document.querySelector('[data-horario-agendamento]');

    if (dataAgendamento && horarioAgendamento) {
        var atualizarHorarioMinimo = function () {
            var agora = new Date();
            var hoje = [
                agora.getFullYear(),
                String(agora.getMonth() + 1).padStart(2, '0'),
                String(agora.getDate()).padStart(2, '0')
            ].join('-');

            if (dataAgendamento.value !== hoje) {
                horarioAgendamento.removeAttribute('min');
                return;
            }

            var primeiroHorario = new Date(agora.getTime());
            primeiroHorario.setSeconds(0, 0);

            if (agora.getSeconds() > 0 || agora.getMilliseconds() > 0) {
                primeiroHorario.setMinutes(primeiroHorario.getMinutes() + 1);
            }

            horarioAgendamento.min = [
                String(primeiroHorario.getHours()).padStart(2, '0'),
                String(primeiroHorario.getMinutes()).padStart(2, '0')
            ].join(':');
        };

        dataAgendamento.addEventListener('input', atualizarHorarioMinimo);
        dataAgendamento.addEventListener('change', atualizarHorarioMinimo);
        horarioAgendamento.addEventListener('focus', atualizarHorarioMinimo);
        atualizarHorarioMinimo();
    }
});
