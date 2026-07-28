(function () {
    'use strict';

    function setSeenCookie() {
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);

        let cookie = 'tm_popup_seen=true; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';

        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }

        document.cookie = cookie;
    }

    function getCookie(name) {
        return document.cookie
            .split('; ')
            .find(function (row) {
                return row.startsWith(name + '=');
            });
    }

    function showOverlay(overlay) {
        if (getCookie('tm_popup_seen')) {
            return;
        }

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');

        const firstField = overlay.querySelector('input:not([type="checkbox"])');

        if (firstField) {
            firstField.focus();
        }
    }

    function closeOverlay(overlay) {
        setSeenCookie();
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function showMessage(message, type) {
        const box = document.getElementById('tm-popup-message');

        if (!box) {
            return;
        }

        box.textContent = message;
        box.className = 'tm-popup-message active ' + type;
    }

    function clearMessage() {
        const box = document.getElementById('tm-popup-message');

        if (!box) {
            return;
        }

        box.textContent = '';
        box.className = 'tm-popup-message';
    }

    function showSuccess(couponCode, emailSent) {
        const registerForm = document.getElementById('tm-register-form');
        const loginForm = document.getElementById('tm-login-form');
        const tabs = document.querySelector('.tm-popup-tabs');
        const successBox = document.getElementById('tm-success-box');
        const codeBox = document.getElementById('tm-coupon-code');
        const deliveryBox = document.getElementById('tm-coupon-delivery');

        if (registerForm) {
            registerForm.classList.remove('active');
        }

        if (loginForm) {
            loginForm.classList.remove('active');
        }

        if (tabs) {
            tabs.style.display = 'none';
        }

        if (successBox) {
            successBox.classList.add('active');
        }

        if (codeBox) {
            codeBox.textContent = couponCode;
        }

        if (deliveryBox) {
            deliveryBox.textContent = emailSent
                ? 'También te lo enviamos al correo y quedó guardado en Mi cuenta.'
                : 'El código quedó guardado en Mi cuenta.';
        }

        clearMessage();
        setSeenCookie();
    }

    function submitForm(form, action) {
        const button = form.querySelector('button[type="submit"]');
        const originalText = button ? button.textContent : '';

        const data = new URLSearchParams(new FormData(form));
        data.append('action', action);
        data.append('nonce', TMPopupBienvenida.nonce);

        if (button) {
            button.disabled = true;
            button.textContent = 'Procesando...';
        }

        clearMessage();

        fetch(TMPopupBienvenida.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: data.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (result.success) {
                    showSuccess(result.data.coupon, Boolean(result.data.email_sent));
                    return;
                }

                showMessage(result.data && result.data.message ? result.data.message : 'No se pudo completar la acción.', 'error');
            })
            .catch(function () {
                showMessage('Error de conexión. Intenta nuevamente.', 'error');
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText;
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('tm-welcome-overlay');

        if (!overlay) {
            return;
        }

        setTimeout(function () {
            showOverlay(overlay);
        }, 2000);

        const closeButton = overlay.querySelector('.tm-popup-close');

        if (closeButton) {
            closeButton.addEventListener('click', function () {
                closeOverlay(overlay);
            });
        }

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeOverlay(overlay);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && overlay.classList.contains('active')) {
                closeOverlay(overlay);
            }
        });

        const copyButton = document.getElementById('tm-copy-coupon');

        if (copyButton) {
            copyButton.addEventListener('click', function () {
                const codeBox = document.getElementById('tm-coupon-code');
                const code = codeBox ? codeBox.textContent.trim() : '';

                if (!code || !navigator.clipboard) {
                    return;
                }

                navigator.clipboard.writeText(code).then(function () {
                    copyButton.textContent = 'Código copiado';
                });
            });
        }

        document.querySelectorAll('.tm-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                const target = tab.getAttribute('data-tab');

                document.querySelectorAll('.tm-tab').forEach(function (item) {
                    item.classList.remove('active');
                });

                document.querySelectorAll('.tm-popup-form').forEach(function (form) {
                    form.classList.remove('active');
                });

                tab.classList.add('active');

                const activeForm = document.getElementById('tm-' + target + '-form');

                if (activeForm) {
                    activeForm.classList.add('active');
                }

                clearMessage();
            });
        });

        const registerForm = document.getElementById('tm-register-form');

        if (registerForm) {
            registerForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitForm(registerForm, 'tm_register_coupon');
            });
        }

        const loginForm = document.getElementById('tm-login-form');

        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitForm(loginForm, 'tm_login_coupon');
            });
        }
    });
})();
