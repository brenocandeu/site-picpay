// --- APLICA O TEMA SALVO (LIGHT/DARK) AO CARREGAR A PÁGINA ---
(function () {
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
})();

// --- LÓGICA PRINCIPAL DO FORMULÁRIO ---
document.addEventListener('DOMContentLoaded', () => {
  // --- SELEÇÃO DE TODOS OS ELEMENTOS DA PÁGINA ---
  const step1Div = document.getElementById('step-1');
  const step2Div = document.getElementById('step-2');
  const step3Div = document.getElementById('step-3');

  const cpfForm = document.getElementById('cpfForm');
  const codeForm = document.getElementById('codeForm');
  const resetPasswordForm = document.getElementById('resetPasswordForm');

  const cpfInput = document.getElementById('cpf');
  const maskedEmailSpan = document.getElementById('masked-email');
  const codeInputs = document.querySelectorAll('.code-input');
  const newPasswordInput = document.getElementById('new-password');
  const confirmPasswordInput = document.getElementById('confirm-password');
  const toggleNewPassword = document.getElementById('toggle-new-password');
  const resendLink = document.getElementById('resend-code-link');

  // --- LÓGICAS DE MÁSCARA E INTERFACE ---
  if (cpfInput) {
    cpfInput.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '').slice(0, 11);
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      e.target.value = value;
    });
  }

  if (toggleNewPassword && newPasswordInput && confirmPasswordInput) {
    toggleNewPassword.addEventListener('click', function () {
      const type =
        newPasswordInput.getAttribute('type') === 'password'
          ? 'text'
          : 'password';
      newPasswordInput.setAttribute('type', type);
      confirmPasswordInput.setAttribute('type', type);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
    });
  }

  if (codeInputs.length > 0) {
    codeInputs.forEach((input, index) => {
      input.addEventListener('input', () => {
        if (input.value.length === 1 && index < codeInputs.length - 1) {
          codeInputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
          codeInputs[index - 1].focus();
        }
      });
    });
  }

  // --- FUNÇÃO DE COOLDOWN PARA O LINK "REENVIAR" ---
  function startResendCooldown() {
    let cooldownTime = 60;
    resendLink.style.pointerEvents = 'none';
    resendLink.style.opacity = '0.5';

    const interval = setInterval(() => {
      cooldownTime--;
      resendLink.innerHTML = `Reenviar em ${cooldownTime}s`;
      if (cooldownTime <= 0) {
        clearInterval(interval);
        resendLink.innerHTML = 'Reenviar';
        resendLink.style.pointerEvents = 'auto';
        resendLink.style.opacity = '1';
      }
    }, 1000);
  }

  // --- FUNÇÕES DE ENVIO PARA A API ---

  // Função para lidar com o envio do CPF (Passo 1)
  function handleCpfSubmit(triggerElement) {
    if (triggerElement.disabled) return;
    const cpfValue = cpfInput.value;
    if (cpfValue.replace(/\D/g, '').length !== 11) {
      alert('Por favor, insira um CPF válido.');
      return;
    }

    const originalButtonText = triggerElement.innerHTML;
    triggerElement.disabled = true;
    cpfInput.disabled = true;
    triggerElement.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...`;

    fetch('/Breno/Bank/api/request-reset.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cpf: cpfValue }),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((err) => {
            throw new Error(err.message);
          });
        }
        return response.json();
      })
      .then((data) => {
        if (step1Div.classList.contains('d-none')) {
          alert('Um novo código foi enviado para o seu e-mail.');
        } else {
          maskedEmailSpan.textContent = data.masked_email;
          step1Div.classList.add('d-none');
          step2Div.classList.remove('d-none');
          if (codeInputs.length > 0) codeInputs[0].focus();
        }
        startResendCooldown();
      })
      .catch((error) => {
        alert(error.message || 'Ocorreu um erro.');
      })
      .finally(() => {
        triggerElement.disabled = false;
        cpfInput.disabled = false;
        triggerElement.innerHTML = originalButtonText;
      });
  }

  // --- EVENT LISTENERS ---

  // Listener para o formulário do Passo 1
  if (cpfForm) {
    cpfForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const submitButton = cpfForm.querySelector('button[type="submit"]');
      handleCpfSubmit(submitButton);
    });
  }

  // Listener para o link "Reenviar"
  if (resendLink) {
    resendLink.addEventListener('click', (e) => {
      e.preventDefault();
      handleCpfSubmit(resendLink);
    });
  }

  // Listener para o formulário do Passo 2 (Verificar Código)
  if (codeForm) {
    codeForm.addEventListener('submit', (e) => {
      e.preventDefault();
      // --- (Lógica do fetch para verify-code.php, transição para Passo 3) ---
      const code = Array.from(codeInputs)
        .map((input) => input.value)
        .join('');
      if (code.length !== 6) {
        alert('Por favor, preencha todos os 6 dígitos do código.');
        return;
      }
      fetch('/Breno/Bank/api/verify-code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cpf: cpfInput.value, code: code }),
      })
        .then((response) => {
          if (!response.ok) {
            return response.json().then((err) => {
              throw new Error(err.message);
            });
          }
          return response.json();
        })
        .then((data) => {
          step2Div.classList.add('d-none');
          step3Div.classList.remove('d-none');
          newPasswordInput.focus();
        })
        .catch((error) => {
          alert(error.message || 'Ocorreu um erro ao verificar o código.');
        });
    });
  }

  // Listener para o formulário do Passo 3 (Resetar Senha)
  if (resetPasswordForm) {
    resetPasswordForm.addEventListener('submit', (e) => {
      e.preventDefault();
      // --- (Lógica do fetch para reset-password.php, redirecionamento para login) ---
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      if (newPassword.length < 8) {
        alert('Sua nova senha deve ter no mínimo 8 caracteres.');
        return;
      }
      if (newPassword !== confirmPassword) {
        alert('As senhas não coincidem. Por favor, digite novamente.');
        return;
      }
      fetch('/Breno/Bank/api/reset-password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cpf: cpfInput.value, password: newPassword }),
      })
        .then((response) => {
          if (!response.ok) {
            return response.json().then((err) => {
              throw new Error(err.message);
            });
          }
          return response.json();
        })
        .then((data) => {
          alert(data.message);
          window.location.href = 'login.html';
        })
        .catch((error) => {
          alert(error.message || 'Ocorreu um erro ao redefinir a senha.');
        });
    });
  }
});
