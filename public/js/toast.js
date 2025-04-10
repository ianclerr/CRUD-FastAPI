function createToast(type, icon, title, text) {
    const notifications = document.querySelector('.notifications');
    const newToast = document.createElement('div');
    
    newToast.innerHTML = `
        <div class="toast ${type}">
            <i class="${icon}"></i>
            <div class="content">
                <div class="title">${title}</div>
                <span>${text}</span>
            </div>
            <i class="fa-solid fa-xmark" onclick="this.parentElement.remove()"></i>
        </div>`;
    
    notifications.appendChild(newToast);
    
    // Eliminar el toast después de 5 segundos
    setTimeout(() => {
        newToast.remove();
    }, 5000);
}
