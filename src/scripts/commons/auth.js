// --- APLICA O TEMA SALVO (LIGHT/DARK) AO CARREGAR A PÁGINA ---
(function () {
  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
})();

// --- LÓGICA PRINCIPAL DO FORMULÁRIO ---
document.addEventListener('DOMContentLoaded', () => {
  // --- SELEÇÃO DE TODOS OS ELEMENTOS NECESSÁRIOS ---
  const registrationForm = document.querySelector('#registrationForm');
  const emailVerifyForm = document.querySelector('#emailVerifyForm');
  const registerStep1 = document.querySelector('#register-step-1');
  const registerStep2 = document.querySelector('#register-step-2');
  const loginForm = document.querySelector('#loginForm');

  // Campos de input
  const cpfInput = document.querySelector('#cpf');
  const fullNameInput = document.querySelector('#fullName');
  const nascimentoInput = document.querySelector('#nascimento');
  const celularInput = document.querySelector('#celular');
  const emailInput = document.querySelector('#email');
  const passwordInput = document.querySelector('#password');
  const togglePassword = document.querySelector('#togglePassword');
  const termsCheck = document.querySelector('#terms');
  const codeInputs = document.querySelectorAll('#register-step-2 .code-input'); // Seletor para os campos de código do cadastro

  // --- LÓGICA DAS MÁSCARAS DE INPUT ---
  if (cpfInput) {
    cpfInput.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      e.target.value = v;
    });
  }

  if (fullNameInput) {
    fullNameInput.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, '');
    });
  }

  if (nascimentoInput) {
    nascimentoInput.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').slice(0, 8);
      v = v.replace(/(\d{2})(\d)/, '$1/$2');
      v = v.replace(/(\d{2})(\d)/, '$1/$2');
      e.target.value = v;
    });
  }

  if (celularInput) {
    celularInput.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
      v = v.replace(/(\d{5})(\d)/, '$1-$2');
      e.target.value = v;
    });
  }

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function () {
      const t =
        passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', t);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
    });
  }

  // --- LÓGICA DOS CAMPOS DE CÓDIGO DE VERIFICAÇÃO ---
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

  // --- LÓGICA DE VALIDAÇÃO DOS FORMULÁRIOS ---
  const showError = (i, m) => {
    const f = i.parentElement.querySelector('.invalid-feedback');
    i.classList.add('is-invalid');
    if (f) f.textContent = m;
  };

  const showSuccess = (i) => {
    i.classList.remove('is-invalid');
  };

  const validateRegistrationForm = () => {
    let v = true;
    const c = cpfInput.value.replace(/\D/g, '');
    if (c.length !== 11) {
      showError(cpfInput, 'CPF deve conter 11 dígitos.');
      v = false;
    } else {
      showSuccess(cpfInput);
    }

    if (fullNameInput.value.trim() === '') {
      showError(fullNameInput, 'O nome completo é obrigatório.');
      v = false;
    } else {
      showSuccess(fullNameInput);
    }

    const d = nascimentoInput.value.replace(/\D/g, '');
    if (d.length !== 8) {
      showError(nascimentoInput, 'A data de nascimento deve ser completa.');
      v = false;
    } else {
      showSuccess(nascimentoInput);
    }

    const ce = celularInput.value.replace(/\D/g, '');
    if (ce.length < 10) {
      showError(celularInput, 'O celular deve ser completo com DDD.');
      v = false;
    } else {
      showSuccess(celularInput);
    }

    const er = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!er.test(emailInput.value)) {
      showError(emailInput, 'Por favor, insira um e-mail válido.');
      v = false;
    } else {
      showSuccess(emailInput);
    }

    if (passwordInput.value.length < 8) {
      showError(
        passwordInput.parentElement,
        'A senha deve ter no mínimo 8 caracteres.',
      );
      v = false;
    } else {
      showSuccess(passwordInput);
    }

    if (!termsCheck.checked) {
      termsCheck.classList.add('is-invalid');
      v = false;
    } else {
      termsCheck.classList.remove('is-invalid');
    }

    return v;
  };

  const validateLoginForm = () => {
    let v = true;
    const c = cpfInput.value.replace(/\D/g, '');
    if (c.length === 0) {
      showError(cpfInput, 'Por favor, insira seu CPF.');
      v = false;
    } else {
      showSuccess(cpfInput);
    }

    if (passwordInput.value.trim() === '') {
      showError(passwordInput.parentElement, 'Por favor, insira sua senha.');
      v = false;
    } else {
      showSuccess(passwordInput);
    }

    return v;
  };

  // --- ENVIO DOS FORMULÁRIOS PARA A API ---

  // CADASTRO - PASSO 1
  if (registrationForm) {
    registrationForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (validateRegistrationForm()) {
        const formData = {
          cpf: cpfInput.value,
          fullName: fullNameInput.value,
          nascimento: nascimentoInput.value.split('/').reverse().join('-'),
          celular: celularInput.value,
          email: emailInput.value,
          password: passwordInput.value,
        };

        const submitButton = registrationForm.querySelector(
          'button[type="submit"]',
        );

        const originalButtonText = submitButton.innerHTML;
        const formInputs = registrationForm.querySelectorAll('input');

        submitButton.disabled = true;
        formInputs.forEach((input) => (input.disabled = true));
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando e-mail...`;

        fetch('/Breno/Bank/api/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData),
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
            registerStep1.classList.add('d-none');
            registerStep2.classList.remove('d-none');
          })
          .catch((error) => {
            console.error('Erro:', error);
            alert(error.message || 'Ocorreu um erro ao tentar se cadastrar.');
            submitButton.disabled = false;
            formInputs.forEach((input) => (input.disabled = false));
            submitButton.innerHTML = originalButtonText;
          });
      }
    });
  }

  // CADASTRO - PASSO 2
  if (emailVerifyForm) {
    emailVerifyForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const code = Array.from(codeInputs)
        .map((input) => input.value)
        .join('');

      if (code.length !== 6) {
        alert('Por favor, insira o código de 6 dígitos.');
        return;
      }

      const submitButton = emailVerifyForm.querySelector(
        'button[type="submit"]',
      );
      
      const originalButtonText = submitButton.innerHTML;
      submitButton.disabled = true;
      codeInputs.forEach((input) => (input.disabled = true));
      submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verificando...`;

      fetch('/Breno/Bank/api/verify-email.php', {
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
          alert(data.message);
          window.location.href = 'login.html';
        })
        .catch((error) => {
          alert(error.message);
          submitButton.disabled = false;
          codeInputs.forEach((input) => (input.disabled = false));
          submitButton.innerHTML = originalButtonText;
        });
    });
  }

  // LOGIN
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (validateLoginForm()) {
        const formData = { cpf: cpfInput.value, password: passwordInput.value };
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        const formInputs = loginForm.querySelectorAll('input');

        submitButton.disabled = true;
        formInputs.forEach((input) => (input.disabled = true));
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Entrando...`;

        fetch('/Breno/Bank/api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData),
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error('CPF ou senha inválidos.');
            }
            return response.json();
          })
          .then((data) => {
            window.location.href = 'dashboard.php';
          })
          .catch((error) => {
            alert(error.message);
            submitButton.disabled = false;
            formInputs.forEach((input) => (input.disabled = false));
            submitButton.innerHTML = originalButtonText;
          });
      }
    });
  }
});
