// Custom Js - Général

"use strict";

// L'entité patient en VueJS avec toute sa logique propre
// Déclaration de mes filtres globbaux
Vue.filter('date', function (value) {
    if (value != null) {
        return moment(value).format('DD/MM/YYYY');
    }
});
Vue.filter('datetime', function (value) {
    if (value != null) {
        return moment(value).format('DD/MM/YYYY HH:mm');
    }
});
Vue.filter('default', function (value, defaut) {
    if (value === null || value === '') {
        return defaut;
    }
    return value;
});

function getUrlParameter(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}


function insertParam(key, value) {
    key = encodeURIComponent(key);
    value = encodeURIComponent(value);

    // Créez un objet URL à partir de l'URL actuelle
    const url = new URL(window.location);

    // Obtenez les paramètres d'URL actuels
    const params = new URLSearchParams(url.search);

    // Ajoutez ou mettez à jour le paramètre
    params.set(key, value);

    // Mettez à jour l'URL sans recharger la page
    const newUrl = `${url.pathname}?${params.toString()}`;
    window.history.pushState({path: newUrl}, '', newUrl);
}


function removeUrlParam(param) {
    const url = new URL(window.location);
    url.searchParams.delete(param);
    history.replaceState(null, '', url.toString());
}

$(document).keydown(function (e) {
    switch (e.which) {
        case 37: // left
            console.log('fleche gauche');
            $('.arcoffice-document-left').click();
            break;

        case 39: // right
            console.log('fleche droite');
            $('.arcoffice-document-right').click();
            break;

        default:
            return; // exit this handler for other keys
    }
    e.preventDefault();
});

$(function () {

    $('#messageAccueil').modal();

    // Bloc-note checkbox alerte
    let $alertCheckbox = $("#appbundle_todo_alerte");
    let $dateAlerte = $("#appbundle_todo_dateAlerte");
    let $tooltip = $('.title-tooltip');

    $(document).on('change', '#appbundle_todo_alerte', function (event) {
        event.preventDefault();
        toggleAlerte();
    });

    function toggleAlerte() {
        if ($alertCheckbox.is(":checked")) {
            $dateAlerte.removeAttr("disabled");
        } else {
            $dateAlerte.prop("disabled", "disabled").val("");
        }
    }

    // Load Datepicker on all pages
    $('.js-datepicker').datepicker({
        format: 'dd/mm/yyyy',
        language: 'fr',
        todayHighlight: true
    }).on('changeDate', function () {
        $(this).datepicker("hide")
    });

    // Fix Double click blue color on canvas
    $("#canvas, #canvas2, #canvas3").on("mousedown", function () {
        return false;
    });

    // Tooltip Boostrap
    $tooltip.tooltip({
        trigger: "hover",
        title: function () {
            return $(this).attr('title');
        }
    });

    $tooltip.on('click', function () {
        $(this).tooltip('hide')
    });

    // Bouton - Haut de page
    $('.top', 'footer').click(function (e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 300);
    });

    // Toastr - Position Bottom Right
    toastr.options = {
        "positionClass": "toast-bottom-right"
    };

    // Patients
    if ($('.arcoffice-patient').length) {
        let col_patient = $('.col-arcoffice');
        let titre_patient = $('.form-title');

        // Colonne agrandie au click
        titre_patient.click(function (e) {
            e.preventDefault();
            if (!($(e.target).is("button") || $(e.target).is("i"))) {
                if ($(this).parent().hasClass('is-active') || $(this).parent().hasClass('col-5')) {
                    $(this).parent().removeClass('is-active col-5');
                    $('.title-tooltip').tooltip('update');
                } else {
                    col_patient.removeClass('is-active col-5');
                    $(this).parent().addClass('is-active col-5');
                    $('.title-tooltip').tooltip('update');
                }
            }
        });

        // Class is-active sur bouton pour inclusion
        $(document).on("click", '.patient-get-inclusion', function (e) {
            e.preventDefault();
            $('.patient-get-inclusion').removeClass('btn-primary').parents('tr').removeClass('is-active');
            $(e.target).addClass('btn-primary').parents('tr').addClass('is-active');
        })
    }

    // Protocole
    if ($('.arcoffice-protocole').length) {

        // Ajouter la class focus à .bootstrap-tagsinput
        $('.tt-input').on('blur', function () {
            $(this).parents('.bootstrap-tagsinput').removeClass('is-focus');
        }).on('focus', function () {
            $(this).parents('.bootstrap-tagsinput').addClass('is-focus');
        })
    }

    // Liste - Suppression (Sweetalert)
    $(document).on('click', '.delete-liste-item', function (event) {
        event.preventDefault();
        let route_delete_item = $(this).data('route-delete');
        let route_liste_item = $(this).data('route-liste');
        let route_liste_param_item = $(this).data('route-liste-param');
        let id_item = String($(this).data('id'));

        swal({
            title: 'Êtes-vous sûr ?',
            text: "Vous allez supprimer cet élément. Vous ne pourrez pas revenir en arrière.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            preConfirm: function () {

                return new Promise(function (resolve, reject) {

                    $.ajax({
                        type: "post",
                        url: Routing.generate(route_delete_item, {id: id_item}),

                        success: function (response) {
                            resolve(response)
                        },
                        error: function (xhr) {
                            reject(xhr.responseText)
                        }
                    })
                })
            }
        }).then(function () {
            swal({
                title: 'Succès',
                type: 'success',
                html: '<p>La supression a bien été effectuée.</p>',
                showCancelButton: false,
                allowOutsideClick: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Fermer'
            }).then(function () {
                window.location = Routing.generate(route_liste_item, route_liste_param_item);
            })
        })
    });


    $(document).on('change', '.libcim10-utile', function (event) {
        event.preventDefault();
        let that = this;
        let checked = $(this).is(":checked");
        $.post(Routing.generate("editUtile", {id: $(this).data("id")}), {checked: checked}, function (data, textStatus, xhr) {
            /*optional stuff to do after success */
        }).fail(function () {
            toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");
            if (checked) {
                $(that).prop("checked", false);
            } else {
                $(that).prop("checked", true);
            }

        });
    });


    // Form User, les protocoles authorisée.
    let $selectProtocole = $("select[id*=rulesProtocole]");
    if ($selectProtocole.length) {
        showSelectProtocoles();

        $(document).on('change', 'select[id*=rulesProtocole]', function (event) {
            event.preventDefault();
            showSelectProtocoles();
        });

        showSelectProtocoles();
    }

    function showSelectProtocoles() {
        let rule = $selectProtocole.val();

        if (rule === "ocp") {
            $("#protocoles-available").show();
        } else {
            $("#protocoles-available").hide();
            $("select[id*=essais]").val([]);
        }
    }

    // Liste des inclusions

    // let options = {
    //   valueNames: ['id', 'datInc',  'statut', 'patient', 'essai', 'numInc']
    // };

    // let userList = new List('liste-inclusions', options);

    // au clic sur la classe que j'ai ajouté dans ma page
    // $(document).on("click",".deleteInclusion", function(e) {
    //     $that = $(this);
    //     e.preventDefault();
    //     swal({
    //           title: "Suppression d'une Inclusion",
    //           text: "Vous allez supprimer une Inclusion. Etes vous sûr ?",
    //           type: 'warning',
    //           showCancelButton: true,
    //           confirmButtonColor: '#3085d6',
    //           cancelButtonColor: '#d33',
    //           confirmButtonText: 'Supprimer',
    //           cancelButtonText: 'Annuler'
    //         }).then(function () {
    //              window.location.href = $that.attr("href");
    //         })

    // })

    // Validation de formulaire - Regex Email
    // $.validator.methods.email = function(value, element) {
    //     return this.optional( element ) || /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i.test( value );
    // }

    // Validation de formulaire - Inscription
    // $(".contact-form").validate({
    //     rules: {
    //         email: {
    //             required: true,
    //             email: true
    //         },
    //         telephone: {
    //             required: true,
    //             phoneUK: true
    //         },
    //         url: {
    //             required: false,
    //             url: true
    //         },
    //     }
    // });

    // Javascript to enable link to tab
    let url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
    }

    // Change hash for page-reload
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        e.preventDefault();
        // eviter les sauts aux anchres lors du changement de tab
        history.pushState(null, null, $(this).attr('href'));
        window.location.hash = e.target.hash;
    })
});