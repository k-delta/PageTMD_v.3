(function () {
    function initCarousel(root, name) {
        var track = root.querySelector('[data-tmd-' + name + '-track]');
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-tmd-' + name + '-slide]'));
        var prev = root.querySelector('[data-tmd-' + name + '-prev]');
        var next = root.querySelector('[data-tmd-' + name + '-next]');
        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-tmd-' + name + '-dot]'));
        var current = 0;

        if (!track || slides.length === 0) {
            return;
        }

        function go(index) {
            current = (index + slides.length) % slides.length;
            track.style.transform = 'translateX(' + (-100 * current) + '%)';

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === current);
                dot.setAttribute('aria-current', dotIndex === current ? 'true' : 'false');
            });
        }

        if (prev) {
            prev.addEventListener('click', function () {
                go(current - 1);
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                go(current + 1);
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                go(index);
            });
        });

        go(0);
    }

    function initForms() {
        document.querySelectorAll('[data-tmd-ajax-form]').forEach(function (form) {
            var status = form.querySelector('[data-tmd-form-status]');

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var data = new FormData(form);
                data.append('action', 'tmd_site_kit_submit_form');
                data.append('nonce', window.tmdSiteKit ? window.tmdSiteKit.nonce : '');

                if (status) {
                    status.textContent = 'Enviando...';
                    status.classList.remove('is-error');
                }

                fetch(window.tmdSiteKit.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (json) {
                        if (!json.success) {
                            throw new Error((json.data && json.data.message) || 'Error al enviar.');
                        }

                        form.reset();
                        if (status) {
                            status.textContent = json.data.message;
                        }
                    })
                    .catch(function (error) {
                        if (status) {
                            status.textContent = error.message;
                            status.classList.add('is-error');
                        }
                    });
            });
        });
    }

    function initChoiceGroups() {
        document.querySelectorAll('[data-tmd-choice-group]').forEach(function (group) {
            var input = group.querySelector('input[type="hidden"]');

            group.querySelectorAll('[data-tmd-choice]').forEach(function (button) {
                button.addEventListener('click', function () {
                    group.querySelectorAll('[data-tmd-choice]').forEach(function (item) {
                        item.classList.remove('is-active');
                    });

                    button.classList.add('is-active');
                    if (input) {
                        input.value = button.dataset.tmdChoice || button.textContent.trim();
                    }
                });
            });
        });
    }

    function initQuiz(root) {
        var steps = Array.prototype.slice.call(root.querySelectorAll('[data-tmd-quiz-step]'));
        var progress = root.querySelector('[data-tmd-quiz-progress]');
        var count = root.querySelector('[data-tmd-quiz-count]');
        var next = root.querySelector('[data-tmd-quiz-next]');
        var prev = root.querySelector('[data-tmd-quiz-prev]');
        var summary = root.querySelector('[data-tmd-quiz-summary]');
        var hidden = root.querySelector('[name="message"]');
        var current = 0;
        var answers = {};

        function render() {
            steps.forEach(function (step, index) {
                step.hidden = index !== current;
            });

            if (progress) {
                progress.style.width = (((current + 1) / steps.length) * 100) + '%';
            }

            if (count) {
                count.textContent = 'Paso ' + (current + 1) + ' de ' + steps.length;
            }

            if (prev) {
                prev.hidden = current === 0;
            }

            if (next) {
                next.textContent = current === steps.length - 1 ? 'Ver recomendacion ->' : 'Siguiente ->';
            }
        }

        function selectedValue(step) {
            var selected = step.querySelector('.tmd-quiz-option.is-active');
            return selected ? selected.dataset.value : '';
        }

        root.querySelectorAll('.tmd-quiz-option').forEach(function (button) {
            button.addEventListener('click', function () {
                var step = button.closest('[data-tmd-quiz-step]');
                step.querySelectorAll('.tmd-quiz-option').forEach(function (option) {
                    option.classList.remove('is-active');
                });
                button.classList.add('is-active');
            });
        });

        if (next) {
            next.addEventListener('click', function () {
                var step = steps[current];
                var key = step.dataset.tmdQuizStep;
                var value = selectedValue(step);

                if (!value) {
                    step.classList.add('has-error');
                    return;
                }

                step.classList.remove('has-error');
                answers[key] = value;

                if (current < steps.length - 1) {
                    current += 1;
                    render();
                    return;
                }

                var recommendation = 'Recomendacion: ' + (answers.tipo || 'montacargas') + ', energia ' + (answers.energia || 'por definir') + ', capacidad ' + (answers.capacidad || 'por definir') + ', altura ' + (answers.altura || 'por definir') + ', uso ' + (answers.uso || 'por definir') + ', modalidad ' + (answers.modalidad || 'por definir') + '.';
                if (summary) {
                    summary.textContent = recommendation;
                }
                if (hidden) {
                    hidden.value = recommendation;
                }
                root.classList.add('is-complete');
            });
        }

        if (prev) {
            prev.addEventListener('click', function () {
                current = Math.max(0, current - 1);
                render();
            });
        }

        render();
    }

    function initCatalogFilters() {
        document.querySelectorAll('[data-tmd-catalog]').forEach(function (catalog) {
            var filters = Array.prototype.slice.call(catalog.querySelectorAll('[data-tmd-filter]'));
            var cards = Array.prototype.slice.call(catalog.querySelectorAll('[data-tmd-card]'));
            var clear = catalog.querySelector('[data-tmd-clear-filters]');

            function selectedValues(name) {
                return filters
                    .filter(function (input) { return input.name === name && input.checked; })
                    .map(function (input) { return input.value; });
            }

            function apply() {
                var names = Array.prototype.slice.call(new Set(filters.map(function (input) { return input.name; })));

                cards.forEach(function (card) {
                    var visible = names.every(function (name) {
                        var values = selectedValues(name);
                        return values.length === 0 || values.indexOf(card.dataset[name] || '') !== -1;
                    });

                    card.hidden = !visible;
                });
            }

            filters.forEach(function (input) {
                input.addEventListener('change', apply);
            });

            if (clear) {
                clear.addEventListener('click', function () {
                    filters.forEach(function (input) { input.checked = false; });
                    apply();
                });
            }

            apply();
        });
    }

    function initQuoteModal() {
        var modal = document.querySelector('[data-tmd-quote-modal]');
        if (!modal) return;

        var productInput = modal.querySelector('[name="product"]');
        var label = modal.querySelector('[data-tmd-quote-product]');

        function open(product) {
            if (productInput) productInput.value = product;
            if (label) label.textContent = product;
            modal.hidden = false;
            document.body.classList.add('tmd-modal-open');
        }

        function close() {
            modal.hidden = true;
            document.body.classList.remove('tmd-modal-open');
        }

        document.querySelectorAll('[data-tmd-quote]').forEach(function (button) {
            button.addEventListener('click', function () {
                open(button.dataset.tmdQuote || 'Solicitud de cotizacion');
            });
        });

        modal.querySelectorAll('[data-tmd-modal-close]').forEach(function (button) {
            button.addEventListener('click', close);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tmd-success-carousel]').forEach(function (root) {
            initCarousel(root, 'success');
        });
        document.querySelectorAll('[data-tmd-advisors-carousel]').forEach(function (root) {
            initCarousel(root, 'advisors');
        });
        document.querySelectorAll('[data-tmd-quiz]').forEach(initQuiz);
        initCatalogFilters();
        initQuoteModal();
        initChoiceGroups();
        initForms();
    });
}());
