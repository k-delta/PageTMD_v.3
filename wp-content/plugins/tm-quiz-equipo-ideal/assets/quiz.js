const TMQ_STEPS = [
    {
        step: 0,
        field: "intent",
        question: "¿Qué estás buscando?",
        description: "Selecciona si quieres comprar o alquilar un equipo.",
        options: ["Comprar un equipo", "Alquilar un equipo"]
    },
    {
        step: 1,
        field: "capacity_range",
        question: "¿Cuánto necesitas levantar?",
        description: "Selecciona el rango aproximado de carga que necesitas mover.",
        options: ["Hasta 2 ton", "2-5 ton", "5-10 ton", "Más de 10 ton"]
    },
    {
        step: 2,
        field: "height_range",
        question: "¿A qué altura necesitas llegar?",
        description: "Selecciona la altura máxima aproximada de elevación.",
        options: ["Hasta 3m", "3-6m", "6-12m", "Más de 12m"]
    },
    {
        step: 3,
        field: "operation_environment",
        question: "¿Dónde va a operar el equipo?",
        description: "Esto ayuda a definir el tipo de equipo, energía y llantas.",
        options: ["Solo interiores", "Solo exteriores", "Ambos"]
    },
    {
        step: 4,
        field: "special_condition",
        question: "¿Alguna condición especial?",
        description: "Selecciona la condición que más se parezca a tu operación.",
        options: ["Cámara de frío", "Pasillo angosto", "Contenedores", "Terreno irregular", "Ninguna"]
    },
    {
        step: 5,
        field: "email",
        question: "¿Quieres recibir la recomendación en tu correo?",
        description: "Este paso es opcional. Puedes dejar tu correo o ver la recomendación directamente.",
        options: []
    }
];

let tmqCurrentStep = 0;
let tmqAnswers = {};

document.addEventListener("DOMContentLoaded", function () {
    const quiz = document.getElementById("tmq-quiz");

    if (!quiz) {
        return;
    }

    document.getElementById("tmq-next").addEventListener("click", tmqNextStep);
    document.getElementById("tmq-prev").addEventListener("click", tmqPrevStep);

    tmqRenderStep();
});

function tmqRenderStep() {
    const stepData = TMQ_STEPS[tmqCurrentStep];

    document.getElementById("tmq-step-label").textContent = `Paso ${tmqCurrentStep} de 6`;
    document.getElementById("tmq-progress-fill").style.width = `${(tmqCurrentStep / 6) * 100}%`;

    document.getElementById("tmq-question").textContent = stepData.question;
    document.getElementById("tmq-description").textContent = stepData.description;

    const optionsContainer = document.getElementById("tmq-options");
    const emailBox = document.getElementById("tmq-email-box");
    const resultBox = document.getElementById("tmq-result");

    optionsContainer.innerHTML = "";
    resultBox.style.display = "none";

    if (stepData.field === "email") {
        emailBox.style.display = "block";
    } else {
        emailBox.style.display = "none";

        stepData.options.forEach(function (option) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "tmq-option";
            button.textContent = option;

            if (tmqAnswers[stepData.field] === option) {
                button.classList.add("selected");
            }

            button.addEventListener("click", function () {
                tmqAnswers[stepData.field] = option;
                tmqRenderStep();
            });

            optionsContainer.appendChild(button);
        });
    }

    const prevButton = document.getElementById("tmq-prev");
    const nextButton = document.getElementById("tmq-next");

    prevButton.style.visibility = tmqCurrentStep === 0 ? "hidden" : "visible";
    nextButton.textContent = tmqCurrentStep === 5 ? "Ver recomendación" : "Siguiente";
}

function tmqNextStep() {
    const stepData = TMQ_STEPS[tmqCurrentStep];

    if (stepData.field !== "email" && !tmqAnswers[stepData.field]) {
        alert("Selecciona una opción para continuar.");
        return;
    }

    if (tmqCurrentStep < 5) {
        tmqCurrentStep++;
        tmqRenderStep();
        return;
    }

    const email = document.getElementById("tmq-email").value.trim();

    if (email !== "") {
        tmqAnswers.email = email;
    }

    tmqShowResult();
}

function tmqPrevStep() {
    if (tmqCurrentStep > 0) {
        tmqCurrentStep--;
        tmqRenderStep();
    }
}

function tmqShowResult() {
    const recommendedEquipment = tmqGetRecommendation();

    document.getElementById("tmq-step-label").textContent = "Resultado";
    document.getElementById("tmq-progress-fill").style.width = "100%";

    document.getElementById("tmq-question").textContent = "Tu equipo recomendado";
    document.getElementById("tmq-description").textContent = "Según tus respuestas, esta es una recomendación inicial.";

    document.getElementById("tmq-options").innerHTML = "";
    document.getElementById("tmq-email-box").style.display = "none";

    const resultBox = document.getElementById("tmq-result");
    resultBox.style.display = "block";

    resultBox.innerHTML = `
        <div class="tmq-result-card">
            <h2>${recommendedEquipment}</h2>
            <p>
                Esta recomendación es una guía inicial. Para una selección precisa se debe validar carga real,
                espacio disponible, altura, terreno y frecuencia de operación.
            </p>

            <div class="tmq-summary">
                <p><strong>Intención:</strong> ${tmqAnswers.intent || "-"}</p>
                <p><strong>Capacidad:</strong> ${tmqAnswers.capacity_range || "-"}</p>
                <p><strong>Altura:</strong> ${tmqAnswers.height_range || "-"}</p>
                <p><strong>Entorno:</strong> ${tmqAnswers.operation_environment || "-"}</p>
                <p><strong>Condición especial:</strong> ${tmqAnswers.special_condition || "-"}</p>
                <p><strong>Email:</strong> ${tmqAnswers.email || "No dejó email"}</p>
            </div>

            <div class="tmq-result-actions">
                <a href="/equipos/">Ver equipos</a>
                <a href="/contacto/">Hablar con asesor</a>
            </div>
        </div>
    `;

    document.getElementById("tmq-prev").style.display = "none";
    document.getElementById("tmq-next").style.display = "none";
}

function tmqGetRecommendation() {
    const capacity = tmqAnswers.capacity_range;
    const height = tmqAnswers.height_range;
    const environment = tmqAnswers.operation_environment;
    const condition = tmqAnswers.special_condition;

    if (condition === "Pasillo angosto") {
        if (height === "6-12m" || height === "Más de 12m") {
            return "Montacargas retráctil";
        }

        return "Apilador eléctrico";
    }

    if (condition === "Terreno irregular" || environment === "Solo exteriores") {
        return "Montacargas todoterreno";
    }

    if (condition === "Cámara de frío") {
        return "Montacargas eléctrico para cámara de frío";
    }

    if (condition === "Contenedores") {
        return "Montacargas contrabalanceado compacto";
    }

    if (capacity === "Hasta 2 ton" && height === "Hasta 3m") {
        return "Apilador eléctrico";
    }

    if (capacity === "2-5 ton") {
        return "Montacargas contrabalanceado";
    }

    if (capacity === "5-10 ton" || capacity === "Más de 10 ton") {
        return "Montacargas de alta capacidad";
    }

    return "Montacargas eléctrico estándar";
}