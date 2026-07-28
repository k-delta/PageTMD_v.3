<?php
/**
 * Plugin Name: TM Quiz Equipo Ideal
 * Description: Quiz para recomendar equipos de montacargas y capturar información comercial.
 * Version: 1.0.0
 * Author: Tecni Montacargas
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('tm_quiz_equipo_ideal', 'tmq_render_quiz');

function tmq_render_quiz() {
    wp_enqueue_script(
        'tmq-quiz-js',
        plugin_dir_url(__FILE__) . 'assets/quiz.js',
        array(),
        '1.0.0',
        true
    );

    ob_start();
    ?>

    <section id="tmq-quiz" style="max-width:1100px;margin:40px auto;padding:24px;font-family:Arial,sans-serif;">
        
        <div style="margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span><strong>Progreso del Quiz</strong></span>
                <span id="tmq-step-label">Paso 0 de 6</span>
            </div>

            <div style="width:100%;background:#e5e7eb;height:10px;border-radius:999px;overflow:hidden;">
                <div id="tmq-progress-fill" style="width:0%;background:#128CEB;height:10px;transition:width .3s ease;"></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1.4fr 0.9fr;gap:32px;align-items:start;">
            
            <div>
                <h1 id="tmq-question" style="font-size:36px;color:#262E4F;margin-bottom:12px;">
                    ¿Qué estás buscando?
                </h1>

                <p id="tmq-description" style="font-size:18px;color:#555;margin-bottom:24px;">
                    Selecciona si quieres comprar o alquilar un equipo.
                </p>

                <div id="tmq-options" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                </div>

                <div id="tmq-email-box" style="display:none;margin-top:24px;">
                    <input 
                        type="email" 
                        id="tmq-email" 
                        placeholder="tu@email.com"
                        style="width:100%;padding:16px;border:1px solid #ddd;border-radius:10px;font-size:16px;"
                    >

                    <p style="font-size:14px;color:#666;margin-top:8px;">
                        Este campo es opcional.
                    </p>
                </div>

                <div id="tmq-result" style="display:none;"></div>

                <div style="display:flex;justify-content:space-between;margin-top:32px;">
                    <button 
                        id="tmq-prev" 
                        type="button" 
                        style="padding:12px 20px;border-radius:8px;border:1px solid #ddd;background:#f8fafc;cursor:pointer;"
                    >
                        Anterior
                    </button>

                    <button 
                        id="tmq-next" 
                        type="button" 
                        style="padding:12px 24px;border-radius:8px;border:0;background:#128CEB;color:white;font-weight:bold;cursor:pointer;"
                    >
                        Siguiente
                    </button>
                </div>
            </div>

            <div>
                <div style="border-radius:18px;overflow:hidden;background:#f1f5f9;padding:24px;">
                    <h3 style="color:#262E4F;margin-top:0;font-size:28px;">
                        Selector de equipo ideal
                    </h3>

                    <p style="color:#555;font-size:17px;line-height:1.6;">
                        Responde unas preguntas rápidas y te recomendaremos un tipo de equipo según tu operación.
                    </p>
                </div>
            </div>

        </div>

        <style>
            .tmq-option {
                padding: 24px;
                border: 1px solid #ddd;
                border-radius: 12px;
                background: white;
                font-weight: bold;
                cursor: pointer;
                font-size: 16px;
                transition: all .2s ease;
            }

            .tmq-option:hover {
                border-color: #128CEB;
                background: rgba(18, 140, 235, 0.06);
            }

            .tmq-option.selected {
                border-color: #128CEB;
                background: rgba(18, 140, 235, 0.12);
                color: #128CEB;
            }

            .tmq-result-card {
                margin-top: 24px;
                padding: 24px;
                border: 1px solid #ddd;
                border-radius: 16px;
                background: #ffffff;
            }

            .tmq-result-card h2 {
                color: #262E4F;
                font-size: 28px;
                margin-top: 0;
            }

            .tmq-summary {
                margin-top: 20px;
                padding: 16px;
                background: #f8fafc;
                border-radius: 12px;
            }

            .tmq-summary p {
                margin: 6px 0;
            }

            .tmq-result-actions {
                display: flex;
                gap: 12px;
                margin-top: 24px;
            }

            .tmq-result-actions a {
                display: inline-block;
                padding: 12px 18px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
            }

            .tmq-result-actions a:first-child {
                background: #f1f5f9;
                color: #262E4F;
            }

            .tmq-result-actions a:last-child {
                background: #128CEB;
                color: white;
            }

            @media (max-width: 800px) {
                #tmq-quiz > div:nth-of-type(2) {
                    grid-template-columns: 1fr !important;
                }

                #tmq-options {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>

    </section>

    <?php
    return ob_get_clean();
}