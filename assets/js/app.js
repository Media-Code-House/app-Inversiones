/**
 * ==========================================
 * APP.JS - JavaScript Principal
 * ==========================================
 */

(function($) {
    'use strict';

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        
        // ==========================================
        // AUTO-DISMISS ALERTS
        // ==========================================
        $('.alert').not('.alert-permanent').delay(5000).fadeOut('slow');

        // ==========================================
        // CONFIRMACIÓN DE ELIMINACIÓN
        // ==========================================
        $('.btn-delete').on('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                e.preventDefault();
                return false;
            }
        });

        // ==========================================
        // TOOLTIPS DE BOOTSTRAP
        // ==========================================
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // ==========================================
        // POPOVERS DE BOOTSTRAP
        // ==========================================
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // ==========================================
        // FORMATO DE NÚMEROS
        // ==========================================
        $('.currency').each(function() {
            var value = parseFloat($(this).text());
            if (!isNaN(value)) {
                $(this).text('$' + value.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }
        });

        // ==========================================
        // VALIDACIÓN DE FORMULARIOS
        // ==========================================
        $('form').on('submit', function(e) {
            var form = $(this);
            
            if (form.hasClass('needs-validation')) {
                if (!form[0].checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.addClass('was-validated');
            }
        });

        // ==========================================
        // BÚSQUEDA EN TABLAS
        // ==========================================
        $('#tableSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#dataTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // ==========================================
        // NAVEGACIÓN ACTIVA
        // ==========================================
        var currentPath = window.location.pathname;
        $('.navbar-nav .nav-link').each(function() {
            var linkPath = $(this).attr('href');
            if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                $(this).addClass('active');
            }
        });

        // ==========================================
        // PRELOADER (si existe)
        // ==========================================
        $(window).on('load', function() {
            $('.preloader').fadeOut('slow');
        });

        // ==========================================
        // SMOOTH SCROLL
        // ==========================================
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 70
                }, 1000);
            }
        });

        // ==========================================
        // CONTADOR ANIMADO
        // ==========================================
        $('.counter').each(function() {
            var $this = $(this),
                countTo = $this.attr('data-count');
            
            $({ countNum: $this.text() }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });

        // ==========================================
        // RESPONSIVE TABLE
        // ==========================================
        function makeTablesResponsive() {
            if ($(window).width() < 768) {
                $('.table').addClass('table-responsive');
            }
        }
        
        makeTablesResponsive();
        $(window).resize(makeTablesResponsive);

    });

})(jQuery);

// ==========================================
// FUNCIONES GLOBALES
// ==========================================

/**
 * Muestra un mensaje de notificación
 */
function showNotification(message, type = 'info') {
    var alertClass = 'alert-' + type;
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 9999;">' +
        message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';
    
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Formatea un número como moneda
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Valida un email
 */
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Confirma una acción
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
