const loginForm = document.getElementById('loginForm') || document.getElementById('registroForm');
const esLogin = !!document.getElementById('loginForm');

document.addEventListener('DOMContentLoaded', () => {
    if (esLogin) {
        manejarLogin();
    } else {
        manejarRegistro();
    }
});

function manejarLogin() {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value.trim();
        const password = this.querySelector('input[type="password"]').value;

        // Simulación simple de usuarios (en producción: backend)
        const usuarios = JSON.parse(localStorage.getItem('usuarios')) || [];
        const usuario = usuarios.find(u => u.email === email && u.password === password);

        if (usuario) {
            localStorage.setItem('usuario', JSON.stringify({
                nombre: usuario.nombre,
                email: usuario.email,
                telefono: usuario.telefono
            }));
            alert('¡Bienvenido de vuelta!');

            const redirect = new URLSearchParams(window.location.search).get('redirect') || 'index.html';
            window.location.href = redirect;
        } else {
            alert('Correo o contraseña incorrectos');
        }
    });
}

function manejarRegistro() {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const nombre = this.querySelectorAll('input[type="text"]')[0].value.trim();
        const email = this.querySelector('input[type="email"]').value.trim();
        const telefono = this.querySelector('input[type="tel"]').value.trim();
        const password = this.querySelector('input[type="password"]').value;

        if (password.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres');
            return;
        }

        let usuarios = JSON.parse(localStorage.getItem('usuarios')) || [];
        if (usuarios.some(u => u.email === email)) {
            alert('Este correo ya está registrado');
            return;
        }

        usuarios.push({ nombre, email, telefono, password });
        localStorage.setItem('usuarios', JSON.stringify(usuarios));

        alert('¡Registro exitoso! Ahora puedes iniciar sesión');
        window.location.href = 'login.html';
    });
}