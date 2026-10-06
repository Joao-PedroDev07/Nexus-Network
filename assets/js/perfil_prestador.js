// Máscara do telefone 
function mascaraTelefone(input) {
    let valor = input.value.replace(/\D/g, '');
    
    // Limita a 11 dígitos
    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }
    
    if (valor.length <= 10) {
        // Formato para telefone fixo (10 dígitos)
        valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
    } else {
        // Formato para celular (11 dígitos)
        valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
    }
    
    input.value = valor;
}

// Máscara do CPF
function mascaraCPF(input) {
    let valor = input.value.replace(/\D/g, '');
    
    // Limita a 11 dígitos
    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }
    
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    
    input.value = valor;
}

// Máscara do CNPJ
function mascaraCNPJ(input) {
    let valor = input.value.replace(/\D/g, '');
    
    // Limita a 14 dígitos
    if (valor.length > 14) {
        valor = valor.substring(0, 14);
    }
    
    valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
    valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
    
    input.value = valor;
}

// Função para alternar modo de edição
function toggleEditMode(formType) {
    const form = document.getElementById(`form-${formType}`);
    const actions = document.getElementById(`actions-${formType}`);
    const inputs = form.querySelectorAll('input, select, textarea');
    const editBtn = form.parentElement.querySelector('.btn-secondary');
    
    // Verificar se já está em modo de edição
    const isEditing = form.classList.contains('editing');
    
    if (!isEditing) {
        // Entrar em modo de edição
        form.classList.add('editing');
        actions.style.display = 'flex';
        editBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
        editBtn.onclick = () => cancelEdit(formType);
        
        inputs.forEach(input => {
            // Lista de campos editáveis baseado na estrutura do banco
            const editableFields = [
                'nome', 'telefone', 'genero', 'estado', 'cidade',
                'nome_loja', 'categoria_produtos', 'descricao_negocio',
                'profissao', 'descricao', 'preco_min', 'preco_max'
            ];
            
            if (!editableFields.includes(input.name)) {
                return; // Pular campos não editáveis
            }
            
            input.removeAttribute('readonly');
            input.removeAttribute('disabled');
            input.style.cursor = 'text';
        });
        
        // Carregar estados se necessário
        carregarEstados();
        
        // Aplicar máscaras
        const telefoneInput = form.querySelector('input[name="telefone"]');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function() {
                mascaraTelefone(this);
            });
        }
        
        const cpfInput = form.querySelector('input[name="cpf"]');
        if (cpfInput) {
            cpfInput.addEventListener('input', function() {
                mascaraCPF(this);
            });
        }
        
    } else {
        cancelEdit(formType);
    }
}

// Função para cancelar edição
function cancelEdit(formType) {
    const form = document.getElementById(`form-${formType}`);
    const actions = document.getElementById(`actions-${formType}`);
    const inputs = form.querySelectorAll('input, select, textarea');
    const editBtn = form.parentElement.querySelector('.btn-secondary');
    
    // Sair do modo de edição
    form.classList.remove('editing');
    actions.style.display = 'none';
    editBtn.innerHTML = '<i class="fas fa-edit"></i> Editar';
    editBtn.onclick = () => toggleEditMode(formType);
    
    inputs.forEach(input => {
        const editableFields = [
            'nome', 'telefone', 'genero', 'estado', 'cidade',
            'nome_loja', 'categoria_produtos', 'descricao_negocio',
            'profissao', 'descricao', 'preco_min', 'preco_max'
        ];
        
        if (!editableFields.includes(input.name)) {
            return;
        }
        
        if (input.tagName === 'SELECT') {
            input.setAttribute('disabled', 'disabled');
        } else {
            input.setAttribute('readonly', 'readonly');
        }
        input.style.cursor = 'not-allowed';
    });
    
    // Recarregar a página para restaurar valores originais
    location.reload();
}

// Funções do modal de exclusão
function showDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('show');
    modal.style.display = 'flex';
    
    // Focar no campo de senha
    setTimeout(() => {
        const senhaInput = modal.querySelector('input[name="senha_confirmacao"]');
        if (senhaInput) {
            senhaInput.focus();
        }
    }, 100);
    
    // Fechar modal ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideDeleteModal();
        }
    });
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.style.display = 'none';
        // Limpar campo de senha
        const senhaInput = modal.querySelector('input[name="senha_confirmacao"]');
        if (senhaInput) {
            senhaInput.value = '';
        }
    }, 300);
}

// Carregar estados do IBGE
function carregarEstados() {
    const estadoSelect = document.getElementById('estado');
    
    // Se já tem estados carregados, não carregar novamente
    if (estadoSelect.options.length > 1) {
        return;
    }
    
    fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
    .then(response => response.json())
    .then(estados => {
        // Manter a opção atual
        const valorAtual = estadoSelect.value;
        const textoAtual = estadoSelect.options[0].text;
        
        // Limpar e adicionar opção default
        estadoSelect.innerHTML = '<option value="">Selecione um estado</option>';
        
        // Adicionar todos os estados
        estados.forEach(estado => {
            let option = document.createElement('option');
            option.value = estado.sigla;
            option.text = estado.nome;
            if (estado.sigla === valorAtual) {
                option.selected = true;
            }
            estadoSelect.appendChild(option);
        });
        
        // Se não encontrou o estado atual nos dados do IBGE, manter o original
        if (!estadoSelect.value && valorAtual) {
            let optionAtual = document.createElement('option');
            optionAtual.value = valorAtual;
            optionAtual.text = textoAtual;
            optionAtual.selected = true;
            estadoSelect.insertBefore(optionAtual, estadoSelect.firstChild.nextSibling);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar estados:', error);
        // Manter o valor atual em caso de erro
    });
}

// Buscar cidades quando estado for alterado
function buscarCidades() {
    const estado = document.getElementById('estado').value;
    const cidadeSelect = document.getElementById('cidade');
    
    if (estado === "") {
        cidadeSelect.innerHTML = '<option value="">Selecione um estado primeiro</option>';
        cidadeSelect.disabled = true;
        return;
    }
    
    cidadeSelect.disabled = true;
    cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
    
    fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${estado}/municipios`)
    .then(response => response.json())
    .then(cidades => {
        cidadeSelect.innerHTML = '<option value="">Selecione uma cidade</option>';
        cidades.forEach(cidade => {
            const option = document.createElement('option');
            option.value = cidade.nome;
            option.text = cidade.nome;
            cidadeSelect.appendChild(option);
        });
        cidadeSelect.disabled = false;
    })
    .catch(error => {
        console.error('Erro ao carregar cidades:', error);
        cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
        cidadeSelect.disabled = false;
    });
}

// Validação em tempo real
function setupValidation() {
    const telefoneInput = document.querySelector('input[name="telefone"]');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            mascaraTelefone(this);
            validarTelefone(this);
        });
        
        telefoneInput.addEventListener('blur', function() {
            validarTelefone(this);
        });
    }
    
    const cpfInput = document.querySelector('input[name="cpf"]');
    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            mascaraCPF(this);
        });
        
        cpfInput.addEventListener('blur', function() {
            validarCPF(this);
        });
    }
}

// Validar telefone
function validarTelefone(input) {
    const telefone = input.value.replace(/\D/g, '');
    const wrapper = input.closest('.input-wrapper') || input.closest('.form-group');
    
    if (!wrapper) return true;
    
    // Remover classes anteriores
    wrapper.classList.remove('input-valid', 'input-invalid');
    
    if (telefone.length >= 10 && telefone.length <= 11) {
        // Validar formato
        if (telefone.length === 11 && telefone[2] !== '9') {
            wrapper.classList.add('input-invalid');
            showFieldError(input, 'Celular deve começar com 9');
            return false;
        }
        
        // Verificar se não são todos dígitos iguais
        if (/(\d)\1{9,10}/.test(telefone)) {
            wrapper.classList.add('input-invalid');
            showFieldError(input, 'Telefone inválido');
            return false;
        }
        
        wrapper.classList.add('input-valid');
        hideFieldError(input);
        return true;
    } else if (telefone.length > 0) {
        wrapper.classList.add('input-invalid');
        showFieldError(input, 'Telefone deve ter 10 ou 11 dígitos');
        return false;
    }
    
    hideFieldError(input);
    return true;
}

// Validar CPF
function validarCPF(input) {
    const cpf = input.value.replace(/\D/g, '');
    const wrapper = input.closest('.input-wrapper') || input.closest('.form-group');
    
    if (!wrapper) return true;
    
    wrapper.classList.remove('input-valid', 'input-invalid');
    
    if (cpf.length === 0) {
        hideFieldError(input);
        return true;
    }
    
    if (cpf.length !== 11) {
        wrapper.classList.add('input-invalid');
        showFieldError(input, 'CPF deve ter 11 dígitos');
        return false;
    }
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) {
        wrapper.classList.add('input-invalid');
        showFieldError(input, 'CPF inválido');
        return false;
    }
    
    // Validar dígitos verificadores
    let soma = 0;
    let resto;
    
    for (let i = 1; i <= 9; i++) {
        soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
    }
    
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) {
        wrapper.classList.add('input-invalid');
        showFieldError(input, 'CPF inválido');
        return false;
    }
    
    soma = 0;
    for (let i = 1; i <= 10; i++) {
        soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
    }
    
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) {
        wrapper.classList.add('input-invalid');
        showFieldError(input, 'CPF inválido');
        return false;
    }
    
    wrapper.classList.add('input-valid');
    hideFieldError(input);
    return true;
}

// Mostrar erro em campo específico
function showFieldError(input, message) {
    hideFieldError(input);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '0.25rem';
    
    input.closest('.form-group').appendChild(errorDiv);
}

// Esconder erro em campo específico
function hideFieldError(input) {
    const errorDiv = input.closest('.form-group').querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Confirmação antes de enviar formulário de exclusão
function confirmDelete(event) {
    // Contas do Google não têm campo de senha no modal
    const senhaInput = document.querySelector('input[name="senha_confirmacao"]');
    const senha = senhaInput ? senhaInput.value : 'google';
    
    if (!senha) {
        event.preventDefault();
        alert('Por favor, digite sua senha para confirmar.');
        return false;
    }
    
    if (!confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
        event.preventDefault();
        return false;
    }
    
    return true;
}

// Preview da foto antes do upload
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const avatarImg = document.querySelector('.profile-avatar img');
            const avatarPlaceholder = document.querySelector('.avatar-placeholder');
            
            if (avatarImg) {
                avatarImg.src = e.target.result;
            } else if (avatarPlaceholder) {
                // Criar nova imagem e substituir placeholder
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.alt = 'Foto do Perfil';
                avatarPlaceholder.parentNode.replaceChild(newImg, avatarPlaceholder);
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Função para validar arquivo de foto
function validarFoto(input) {
    const file = input.files[0];
    
    if (!file) return true;
    
    // Validar tipo
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Apenas arquivos de imagem são permitidos (JPG, PNG, GIF, WEBP).');
        input.value = '';
        return false;
    }
    
    // Validar tamanho (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Arquivo muito grande! Máximo 5MB.');
        input.value = '';
        return false;
    }
    
    return true;
}

// Auto-dismiss para alerts
function setupAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-dismiss após 5 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
}

// Keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // ESC para fechar modal
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal.show');
            if (modal) {
                hideDeleteModal();
            }
        }
        
        // Ctrl+E para editar (se não estiver em modo de edição)
        if (e.ctrlKey && e.key === 'e' && !document.querySelector('.editing')) {
            e.preventDefault();
            toggleEditMode('dados');
        }
    });
}

// Função auxiliar para mostrar loading
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    button.disabled = true;
    
    return function hideLoading() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Smooth scroll para seções
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Validação de preços (para prestadores)
function validarPrecos() {
    const precoMinInput = document.querySelector('input[name="preco_min"]');
    const precoMaxInput = document.querySelector('input[name="preco_max"]');
    
    if (precoMinInput && precoMaxInput) {
        const min = parseFloat(precoMinInput.value) || 0;
        const max = parseFloat(precoMaxInput.value) || 0;
        
        if (max > 0 && min > max) {
            showFieldError(precoMaxInput, 'Preço máximo deve ser maior que o mínimo');
            return false;
        }
        
        hideFieldError(precoMaxInput);
        return true;
    }
    
    return true;
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Configurar validações
    setupValidation();
    
    // Configurar auto-dismiss dos alerts
    setupAlertDismiss();
    
    // Configurar atalhos de teclado
    setupKeyboardShortcuts();
    
    // Configurar preview de foto
    const fotoInput = document.getElementById('foto-input');
    if (fotoInput) {
        fotoInput.addEventListener('change', function() {
            if (validarFoto(this)) {
                previewFoto(this);
            }
        });
    }
    
    // Configurar confirmação de exclusão
    const formExcluir = document.getElementById('form-excluir');
    if (formExcluir) {
        formExcluir.addEventListener('submit', confirmDelete);
    }
    
    // Configurar evento de estado
    const estadoSelect = document.getElementById('estado');
    if (estadoSelect) {
        estadoSelect.addEventListener('change', buscarCidades);
    }
    
    // Configurar validação de preços
    const precoMinInput = document.querySelector('input[name="preco_min"]');
    const precoMaxInput = document.querySelector('input[name="preco_max"]');
    
    if (precoMinInput) {
        precoMinInput.addEventListener('blur', validarPrecos);
    }
    
    if (precoMaxInput) {
        precoMaxInput.addEventListener('blur', validarPrecos);
    }
});