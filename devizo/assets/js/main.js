/**
 * DEVIZO - JavaScript Principal
 *
 * Contine functii JavaScript comune pentru toata aplicatia
 */

// Functie pentru afisare notificare
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `flash-message ${type}`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px; animation: slideIn 0.3s;';

    const icon = type === 'success' ? 'fa-check-circle' :
                 type === 'error' ? 'fa-exclamation-circle' :
                 type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        ${message}
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Functie pentru confirmare stergere
function confirmDelete(message = 'Sigur doriti sa stergeti?') {
    return confirm(message);
}

// Functie pentru deschidere modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

// Functie pentru inchidere modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Functie pentru formatare pret
function formatPrice(amount) {
    return parseFloat(amount).toFixed(2);
}

// Functie pentru validare email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Functie pentru calcul total (folosita in devize)
function calculateTotal(cantitate, pret) {
    return formatPrice(parseFloat(cantitate) * parseFloat(pret));
}

// Loading overlay
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    loading.innerHTML = '<div class="loading" style="width: 50px; height: 50px; border-width: 5px;"></div>';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// AJAX helper
async function apiRequest(url, method = 'GET', data = null) {
    try {
        showLoading();

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        const result = await response.json();

        hideLoading();

        return result;

    } catch (error) {
        hideLoading();
        showNotification('Eroare la comunicarea cu serverul', 'error');
        console.error('Error:', error);
        return { success: false, message: error.message };
    }
}

// Event listeners pentru modals
document.addEventListener('DOMContentLoaded', function() {
    // Inchidere modal la click pe fundal
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Inchidere modal la click pe buton close
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('active');
        });
    });
});

// Animatii
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
