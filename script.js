document.addEventListener("DOMContentLoaded", () => {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    const toggleButtons = document.querySelectorAll('.toggle-eye');

    toggleButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const input = passwordInputs[index];
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        });
    });

    const navButtons = document.querySelectorAll('.toggle button');
    navButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!button.classList.contains('active')) {
                document.querySelector('.shell').style.opacity = '0';
                document.querySelector('.shell').style.transform = 'translateY(10px)';
            }
        });
    });
});