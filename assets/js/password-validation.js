// ===== ELEMENTOS DO DOM =====
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const passwordStrength = document.getElementById('passwordStrength');
const strengthFill = document.getElementById('strengthFill');
const strengthText = document.getElementById('strengthText');
const passwordMatch = document.getElementById('passwordMatch');
const submitBtn = document.querySelector('button[type="submit"]');
const form = document.querySelector('form');

// ===== ESTADO DA VALIDAÇÃO =====
let passwordValid = false;
let passwordsMatch = false;

// ===== CONFIGURAÇÃO DOS REQUISITOS =====
const requirements = {
    length: { 
        regex: /.{8,}/, 
        element: document.getElementById('req-length') 
    },
    uppercase: { 
        regex: /[A-Z]/, 
        element: document.getElementById('req-uppercase') 
    },
    lowercase: { 
        regex: /[a-z]/, 
        element: document.getElementById('req-lowercase') 
    },
    number: { 
        regex: /[0-9]/, 
        element: document.getElementById('req-number') 
    },
    special: { 
        regex: /[!@#$%^&*(),.?":{}|<>]/, 
        element: document.getElementById('req-special') 
    }
};

// ===== EVENT LISTENERS =====
if (password) {
    password.addEventListener('input', handlePasswordInput);
}

if (confirmPassword) {
    confirmPassword.addEventListener('input', handleConfirmPasswordInput);
}

if (form) {
    form.addEventListener('submit', handleFormSubmit);
}

// ===== FUNÇÃO: VALIDAÇÃO DA SENHA PRINCIPAL =====
function handlePasswordInput() {
    const value = this.value;
    
    if (value.length > 0) {
        if (passwordStrength) {
            passwordStrength.classList.add('show');
        }
        validatePassword(value);
    } else {
        if (passwordStrength) {
            passwordStrength.classList.remove('show');
        }
        resetPasswordValidation();
    }
    
    // Revalidar confirmação se já foi preenchida
    if (confirmPassword && confirmPassword.value.length > 0) {
        validatePasswordMatch();
    }
    
    updateSubmitButton();
}

// ===== FUNÇÃO: VALIDAÇÃO DA CONFIRMAÇÃO =====
function handleConfirmPasswordInput() {
    validatePasswordMatch();
    updateSubmitButton();
}

// ===== FUNÇÃO: VALIDAR FORÇA DA SENHA =====
function validatePassword(passwordValue) {
    let validCount = 0;
    const totalRequirements = Object.keys(requirements).length;

    // Verificar cada requisito
    Object.entries(requirements).forEach(([key, requirement]) => {
        if (requirement.element) {
            if (requirement.regex.test(passwordValue)) {
                requirement.element.classList.add('valid');
                validCount++;
            } else {
                requirement.element.classList.remove('valid');
            }
        }
    });

    // Calcular e atualizar força da senha DINAMICAMENTE
    updatePasswordStrength(validCount, totalRequirements);

    // Definir se senha é válida
    passwordValid = validCount === totalRequirements;
    updatePasswordIcon();
}

// ===== FUNÇÃO: ATUALIZAR INDICADOR DE FORÇA DINAMICAMENTE =====
function updatePasswordStrength(validCount, totalRequirements) {
    if (!strengthFill || !strengthText) return;
    
    // Calcular porcentagem baseada nos requisitos atendidos
    const percentage = (validCount / totalRequirements) * 100;
    
    // Atualizar largura da barra dinamicamente
    strengthFill.style.width = percentage + '%';

    // Definir cor e texto baseado na quantidade de requisitos
    if (validCount === 0) {
        // Nenhum requisito
        strengthFill.className = 'strength-fill';
        strengthFill.style.backgroundColor = '#e0e0e0';
        strengthText.textContent = 'Força da senha';
        strengthText.style.color = '#666';
    } else if (validCount <= 2) {
        // Fraca (1-2 requisitos) - Vermelho
        strengthFill.className = 'strength-fill strength-weak';
        strengthFill.style.backgroundColor = '#dc3545';
        strengthText.textContent = 'Força: Fraca';
        strengthText.style.color = '#dc3545';
    } else if (validCount <= 3) {
        // Média-Fraca (3 requisitos) - Laranja
        strengthFill.className = 'strength-fill strength-fair';
        strengthFill.style.backgroundColor = '#ff6b35';
        strengthText.textContent = 'Força: Regular';
        strengthText.style.color = '#ff6b35';
    } else if (validCount <= 4) {
        // Média-Forte (4 requisitos) - Amarelo
        strengthFill.className = 'strength-fill strength-good';
        strengthFill.style.backgroundColor = '#ffc107';
        strengthText.textContent = 'Força: Boa';
        strengthText.style.color = '#ffc107';
    } else {
        // Forte (5 requisitos) - Verde
        strengthFill.className = 'strength-fill strength-strong';
        strengthFill.style.backgroundColor = '#28a745';
        strengthText.textContent = 'Força: Forte';
        strengthText.style.color = '#28a745';
    }
}

// ===== FUNÇÃO: VALIDAR CONFIRMAÇÃO DE SENHA =====
function validatePasswordMatch() {
    if (!password || !confirmPassword || !passwordMatch) return;
    
    const passwordValue = password.value;
    const confirmValue = confirmPassword.value;

    if (confirmValue.length === 0) {
        passwordMatch.classList.remove('show');
        passwordsMatch = false;
        confirmPassword.classList.remove('input-valid', 'input-invalid');
        const confirmIcon = document.getElementById('confirmIcon');
        if (confirmIcon) {
            confirmIcon.classList.remove('show');
        }
        return;
    }

    passwordMatch.classList.add('show');

    if (passwordValue === confirmValue && passwordValid) {
        passwordMatch.textContent = ' As senhas coincidem';
        passwordMatch.className = 'password-match show valid';
        confirmPassword.classList.add('input-valid');
        confirmPassword.classList.remove('input-invalid');
        
        const icon = document.getElementById('confirmIcon');
        if (icon) {
            icon.textContent = '✓';
            icon.className = 'validation-icon show valid';
        }
        
        passwordsMatch = true;
    } else {
        if (passwordValue !== confirmValue) {
            passwordMatch.textContent = ' As senhas não coincidem';
        } else {
            passwordMatch.textContent = ' Complete os requisitos da senha primeiro';
        }
        
        passwordMatch.className = 'password-match show invalid';
        confirmPassword.classList.add('input-invalid');
        confirmPassword.classList.remove('input-valid');
        
        const icon = document.getElementById('confirmIcon');
        if (icon) {
            icon.textContent = '✗';
            icon.className = 'validation-icon show invalid';
        }
        
        passwordsMatch = false;
        
        // Animação de shake para feedback visual
        confirmPassword.classList.add('shake');
        setTimeout(() => confirmPassword.classList.remove('shake'), 500);
    }
}

// ===== FUNÇÃO: ATUALIZAR ÍCONE DA SENHA =====
function updatePasswordIcon() {
    const icon = document.getElementById('passwordIcon');
    if (!icon || !password) return;
    
    if (password.value.length === 0) {
        icon.classList.remove('show');
        password.classList.remove('input-valid', 'input-invalid');
        return;
    }

    icon.classList.add('show');
    
    if (passwordValid) {
        icon.textContent = '✓';
        icon.className = 'validation-icon show valid';
        password.classList.add('input-valid');
        password.classList.remove('input-invalid');
    } else {
        icon.textContent = '✗';
        icon.className = 'validation-icon show invalid';
        password.classList.add('input-invalid');
        password.classList.remove('input-valid');
    }
}

// ===== FUNÇÃO: RESETAR VALIDAÇÃO DA SENHA =====
function resetPasswordValidation() {
    passwordValid = false;
    const passwordIcon = document.getElementById('passwordIcon');
    if (passwordIcon) {
        passwordIcon.classList.remove('show');
    }
    if (password) {
        password.classList.remove('input-valid', 'input-invalid');
    }
    
    Object.values(requirements).forEach(requirement => {
        if (requirement.element) {
            requirement.element.classList.remove('valid');
        }
    });
    
    // Resetar barra de força
    if (strengthFill) {
        strengthFill.style.width = '0%';
        strengthFill.style.backgroundColor = '#e0e0e0';
    }
    if (strengthText) {
        strengthText.textContent = 'Força da senha';
        strengthText.style.color = '#666';
    }
}

// ===== FUNÇÃO: ATUALIZAR BOTÃO DE SUBMIT =====
function updateSubmitButton() {
    if (!submitBtn) return;
    
    // Não bloquear o submit, apenas feedback visual
    if (passwordValid && passwordsMatch) {
        submitBtn.disabled = false;
    } else {
        // Não desabilitar para permitir validação do PHP
        submitBtn.disabled = false;
    }
}

// ===== FUNÇÃO: ENVIO DO FORMULÁRIO =====
function handleFormSubmit(e) {
    // Não prevenir o submit, deixar o PHP validar
    // O formulário será enviado normalmente
}

// ===== FUNÇÃO: RESETAR FORMULÁRIO =====
function resetForm() {
    if (!form) return;
    
    form.reset();
    
    if (passwordStrength) {
        passwordStrength.classList.remove('show');
    }
    if (passwordMatch) {
        passwordMatch.classList.remove('show');
    }
    
    passwordValid = false;
    passwordsMatch = false;
    
    // Limpar classes de validação
    if (password) {
        password.classList.remove('input-valid', 'input-invalid');
    }
    if (confirmPassword) {
        confirmPassword.classList.remove('input-valid', 'input-invalid');
    }
    
    const passwordIcon = document.getElementById('passwordIcon');
    const confirmIcon = document.getElementById('confirmIcon');
    
    if (passwordIcon) {
        passwordIcon.classList.remove('show');
    }
    if (confirmIcon) {
        confirmIcon.classList.remove('show');
    }
    
    // Resetar requisitos
    Object.values(requirements).forEach(requirement => {
        if (requirement.element) {
            requirement.element.classList.remove('valid');
        }
    });
    
    // Resetar barra
    resetPasswordValidation();
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de Validação de Senha carregado!');
    
    // Configurações iniciais se necessário
    if (submitBtn) {
        updateSubmitButton();
    }
});

// Exportar funções para uso externo
window.PasswordValidator = {
    getValidationState: function() {
        return {
            passwordValid,
            passwordsMatch,
            canSubmit: passwordValid && passwordsMatch
        };
    },
    resetForm: resetForm
};