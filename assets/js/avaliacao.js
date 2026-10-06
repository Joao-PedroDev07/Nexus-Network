$(function() {
    let notaSelecionada = 0;

    $('.rating[data-pres-id] .star').on('click', function(e) {
        e.preventDefault();
        const presId = $(this).parent().data('pres-id');
        
        $('#modalPresId').val(presId);
        $('#notaSelecionada').val(0);
        notaSelecionada = 0;
        $('#modalStars .star').removeClass('selected hover');
        $('#comentario').val('');
        $('#avaliacaoModal').addClass('active');
        $('body').css('overflow', 'hidden');
    });

    $('#fecharModal, #cancelarModal').on('click', function() {
        fecharModal();
    });

    $('#avaliacaoModal').on('click', function(e) {
        if (e.target === this) {
            fecharModal();
        }
    });

    function fecharModal() {
        $('#avaliacaoModal').removeClass('active');
        $('body').css('overflow', 'auto');
        notaSelecionada = 0;
    }

    $('#modalStars .star').on('mouseenter', function() {
        let valor = $(this).data('star');
        $('#modalStars .star').each(function() {
            $(this).toggleClass('hover', $(this).data('star') <= valor);
        });
    }).on('mouseleave', function() {
        $('#modalStars .star').removeClass('hover');
    });

    $('#modalStars .star').on('click', function() {
        notaSelecionada = $(this).data('star');
        $('#notaSelecionada').val(notaSelecionada);
        
        $('#modalStars .star').each(function() {
            $(this).toggleClass('selected', $(this).data('star') <= notaSelecionada);
        });
    });

    $('#formAvaliacao').on('submit', function(e) {
        const nota = $('#notaSelecionada').val();
        const prestadorId = $('#modalPresId').val();
        
        if (nota == 0 || nota == '') {
            alert('Por favor, selecione uma nota de 1 a 5 estrelas.');
            e.preventDefault();
            return false;
        }

        if (!prestadorId) {
            alert('Erro: ID do prestador não identificado.');
            e.preventDefault();
            return false;
        }

        // Formulário será enviado normalmente
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#avaliacaoModal').hasClass('active')) {
            fecharModal();
        }
    });
});