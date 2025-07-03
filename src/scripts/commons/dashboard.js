// --- APLICA O TEMA SALVO (LIGHT/DARK) AO CARREGAR A PÁGINA ---
// Esta função auto-executável roda imediatamente para evitar que a página "pisque"
(function () {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();


// --- LÓGICA PRINCIPAL DO DASHBOARD ---
// Roda somente após o HTML da página estar completamente carregado
document.addEventListener('DOMContentLoaded', () => {

    // --- SELEÇÃO DE TODOS OS ELEMENTOS GLOBAIS DO DASHBOARD ---
    const navLinks = document.querySelectorAll('.nav-link[data-target]');
    const pageContents = document.querySelectorAll('.page-content');
    const viewStatementLink = document.getElementById('view-statement-link');
    const toggleBalance = document.getElementById('toggle-balance');
    const balanceValue = document.getElementById('balance-value');
    const aiFab = document.getElementById('ai-fab');
    const chatWindow = document.getElementById('ai-chat-window');
    const closeChatBtn = document.getElementById('close-chat-btn');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatBody = document.getElementById('chat-body');
    
    // Seletores da página de Perfil
    const profileDataForm = document.getElementById('profile-data-form');
    const passwordChangeForm = document.getElementById('password-change-form');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const saveProfileBtn = document.getElementById('save-profile-btn');
    const editableInputs = [document.getElementById('profile-phone'), document.getElementById('profile-email')];

    // --- FUNÇÃO CENTRAL DE NAVEGAÇÃO (REUTILIZÁVEL) ---
    function navigateTo(targetId) {
        if (!targetId) return;

        pageContents.forEach(content => content.classList.add('d-none'));
        navLinks.forEach(nav => nav.classList.remove('active'));

        const targetContent = document.getElementById(targetId);
        if (targetContent) {
            targetContent.classList.remove('d-none');
        }

        const targetLink = document.querySelector(`.nav-link[data-target="${targetId}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }
    }

    // --- EVENT LISTENERS PARA NAVEGAÇÃO ---

    // Listener para os links do MENU LATERAL
    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const targetId = link.getAttribute('data-target');
            navigateTo(targetId);
        });
    });

    // Listener para o link "Ver extrato completo" na Home
    if (viewStatementLink) {
        viewStatementLink.addEventListener('click', (event) => {
            event.preventDefault();
            navigateTo('extrato');
        });
    }

    // --- LÓGICA PARA ESCONDER/MOSTRAR SALDO ---
    if (toggleBalance && balanceValue) {
        let isBalanceVisible = true;
        const originalBalance = balanceValue.textContent;
        toggleBalance.addEventListener('click', () => {
            if (isBalanceVisible) {
                balanceValue.textContent = 'R$ ••••••••';
                toggleBalance.classList.remove('bi-eye-fill');
                toggleBalance.classList.add('bi-eye-slash-fill');
                isBalanceVisible = false;
            } else {
                balanceValue.textContent = originalBalance;
                toggleBalance.classList.remove('bi-eye-slash-fill');
                toggleBalance.classList.add('bi-eye-fill');
                isBalanceVisible = true;
            }
        });
    }

    // --- LÓGICA DO CHATBOT DE IA ---
    if (aiFab && chatWindow && closeChatBtn && chatForm) {
        aiFab.addEventListener('click', () => chatWindow.classList.toggle('active'));
        closeChatBtn.addEventListener('click', () => chatWindow.classList.remove('active'));

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const userMessage = chatInput.value.trim();
            if (userMessage === '') return;
            addMessage(userMessage, 'user');
            chatInput.value = '';
            setTimeout(() => {
                const botResponse = getBotResponse(userMessage);
                addMessage(botResponse, 'bot');
            }, 1200);
        });

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}`;
            messageDiv.innerHTML = `<p>${text}</p>`;
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function getBotResponse(userMessage) {
            const lowerCaseMessage = userMessage.toLowerCase();
            if (lowerCaseMessage.includes('cdb')) { return 'O CDB do Picpay rende 110% do CDI com liquidez diária. É uma ótima opção para sua reserva de emergência!'; }
            else if (lowerCaseMessage.includes('pix')) { return "O Pix no Picpay é gratuito e ilimitado. Você pode cadastrar sua chave na seção 'Área Pix' do menu."; }
            else if (lowerCaseMessage.includes('cartão') || lowerCaseMessage.includes('anuidade')) { return "Nosso cartão Gold não tem anuidade! Já os cartões Platinum e Black podem ter isenção da anuidade por gastos ou investimentos."; }
            else { return 'Olá! Sou a PicpayIA. Posso te ajudar com dúvidas sobre CDB, Pix e Cartão.'; }
        }
        addMessage('Olá! Sou a PicpayIA, sua assistente financeira. Como posso te ajudar hoje?', 'bot');
    }

    // --- LÓGICA DA PÁGINA DE PERFIL ---

    // Lógica para habilitar a edição do perfil
    if (editProfileBtn && saveProfileBtn) {
        editProfileBtn.addEventListener('click', () => {
            editableInputs.forEach(input => {
                input.readOnly = false;
                input.style.cursor = 'text';
            });
            editProfileBtn.classList.add('d-none');
            saveProfileBtn.classList.remove('d-none');
            editableInputs[0].focus(); // Foca no primeiro campo editável
        });
    }

    // Lógica para salvar os dados do perfil (fetch)
    if (profileDataForm) {
        profileDataForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Só executa o fetch se o botão de salvar estiver visível (modo de edição)
            if (!saveProfileBtn.classList.contains('d-none')) {
                const formData = {
                    phone: document.getElementById('profile-phone').value,
                    email: document.getElementById('profile-email').value
                };

                fetch('/Breno/Bank/api/update-profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    alert(res.body.message);
                    if (res.status === 200) {
                        // Trava os campos novamente e volta para o botão "Editar"
                        editableInputs.forEach(input => input.readOnly = true);
                        editProfileBtn.classList.remove('d-none');
                        saveProfileBtn.classList.add('d-none');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Ocorreu um erro de comunicação.');
                });
            }
        });
    }
    
    // Lógica para o formulário de alteração de senha
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password-profile').value;
            const confirmNewPassword = document.getElementById('confirm-new-password').value;

            if (newPassword !== confirmNewPassword) {
                alert('A nova senha e a confirmação não coincidem.');
                return;
            }
            if (newPassword.length < 8) {
                alert('A nova senha deve ter no mínimo 8 caracteres.');
                return;
            }

            fetch('/Breno/Bank/api/change-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    currentPassword: currentPassword,
                    newPassword: newPassword
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                alert(res.body.message);
                if (res.status === 200) {
                    passwordChangeForm.reset(); // Limpa o formulário em caso de sucesso
                }
            })
            .catch(err => {
                console.error(err);
                alert('Ocorreu um erro de comunicação ao tentar alterar a senha.');
            });
        });
    }
});