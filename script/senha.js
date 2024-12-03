function mostrarSenha() {
    const senhaInput = document.getElementById('senha');
    const olhoIcon = document.getElementById('btn-senha');
    
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        olhoIcon.classList.remove('bi-eye-fill');
        olhoIcon.classList.add('bi-eye-slash-fill');
    } else {
        senhaInput.type = 'password';
        olhoIcon.classList.remove('bi-eye-slash-fill');
        olhoIcon.classList.add('bi-eye-fill');
    }
}