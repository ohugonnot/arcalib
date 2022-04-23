
 	// Custom Js - Général

	    "use strict";

        // L'entité patient en VueJS avec toute sa logique propre
        // Déclaration de mes filtres globbaux
        Vue.filter('date', function(value) {
              if(value != null) {
                    return moment(value).format('DD/MM/YYYY');
              }
        });
        Vue.filter('datetime', function(value) {
            if(value != null) {
                return moment(value).format('DD/MM/YYYY HH:mm');
            }
        });
        Vue.filter('default', function(value, defaut) {
               if(value === null || value === '') {
                  return defaut;
               }
               return value;
        });

        var getUrlParameter = function getUrlParameter(sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        };


        function insertParam(key,value)
        {
            key = encodeURIComponent(key); value = encodeURIComponent(value);

            var s = document.location.search;
            var kvp = key+"="+value;

            var r = new RegExp("(&|\\?)"+key+"=[^\&]*");

            s = s.replace(r,"$1"+kvp);

            if(!RegExp.$1) {s += (s.length>0 ? '&' : '?') + kvp;}

            if (history.pushState) {
                var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + s;
                window.history.pushState({path:newurl},'',newurl);
            }
        }


        $(document).keydown(function(e) {
            switch(e.which) {
                case 37: // left
                console.log('fleche gauche')
                $('.arcoffice-document-left').click()
                break;

                case 39: // right
                console.log('fleche droite')
                $('.arcoffice-document-right').click()
                break;

                default: return; // exit this handler for other keys
            }
            e.preventDefault();
        });

        $(function() {

            $('#messageAccueil').modal();

            // Bloc-note checkbox alerte
            var $alertCheckbox = $("#appbundle_todo_alerte");
            var $dateAlerte = $("#appbundle_todo_dateAlerte");
            var $tooltip = $('.title-tooltip');

            $(document).on('change','#appbundle_todo_alerte', function(event) {
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
            }).on('changeDate', function() {
                    $(this).datepicker("hide")
             });

            // Fix Double click blue color on canvas
            $("#canvas, #canvas2, #canvas3").on("mousedown", function () { return false; });

            // Tooltip Boostrap
            $tooltip.tooltip({
                trigger: "hover",
                title: function(){
                    return $(this).attr('title');
                }
            });

            $tooltip.on('click', function () {
                $(this).tooltip('hide')
            });

        	// Bouton - Haut de page
            $('.top','footer').click(function(e){
                e.preventDefault();
                $('html, body').animate({ scrollTop : 0 }, 300);
            });

            // Toastr - Position Bottom Right
            toastr.options = {
                "positionClass": "toast-bottom-right"
            };

            // Patients
            if ($('.arcoffice-patient').length) {
                var col_patient = $('.col-arcoffice');
                var titre_patient = $('.form-title');
                
                // Colonne agrandie au click
                titre_patient.click(function(e) {
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
                $(document).on("click", '.patient-get-inclusion', function(e) {
                    e.preventDefault();
                    $('.patient-get-inclusion').removeClass('btn-primary').parents('tr').removeClass('is-active');
                    $(e.target).addClass('btn-primary').parents('tr').addClass('is-active');
                })
            }

            // Protocole
            if ($('.arcoffice-protocole').length) {

                // Ajouter la class focus à .bootstrap-tagsinput
                $('.tt-input').on('blur', function() {
                    $(this).parents('.bootstrap-tagsinput').removeClass('is-focus');
                }).on('focus', function() {
                    $(this).parents('.bootstrap-tagsinput').addClass('is-focus');
                })
            }

            // Liste - Suppression (Sweetalert)
            $(document).on('click', '.delete-liste-item', function(event) {
                event.preventDefault();
                var route_delete_item = $(this).data('route-delete');
                var route_liste_item = $(this).data('route-liste');
                var route_liste_param_item = $(this).data('route-liste-param');
                var id_item = String($(this).data('id'));

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
                                url: Routing.generate(route_delete_item, { id: id_item }),
                                
                                success: function(response) {
                                    resolve(response)
                                },
                                error: function(xhr) {
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


            $(document).on('change','.libcim10-utile', function(event) {
                event.preventDefault();
                var that = this;
                var checked = $(this).is(":checked");
                $.post(Routing.generate("editUtile",{id : $(this).data("id") }), {checked: checked}, function(data, textStatus, xhr) {
                    /*optional stuff to do after success */
                }).fail(function() {
                toastr.error( "Vous n'avez pas les droits nécessaires pour cette action.");
                if(checked) {
                        $(that).prop("checked",false);
                } else {
                        $(that).prop("checked",true);
                }

              });
            });


            // Form User, les protocoles authorisée.
            if($("select[id*=rulesProtocole]").length) {
                showSelectProtocoles();

                $(document).on('change', 'select[id*=rulesProtocole]', function(event) {
                    event.preventDefault();
                    showSelectProtocoles();
                }); 

                showSelectProtocoles();             
            }

            function showSelectProtocoles() {
                var rule = $("select[id*=rulesProtocole]").val();

                if(rule === "ocp") {
                    $("#protocoles-available").show();
                } else {
                    $("#protocoles-available").hide();
                    $("select[id*=essais]").val([]);
                }
            }

            // Liste des inclusions 

            // var options = {
            //   valueNames: ['id', 'datInc',  'statut', 'patient', 'essai', 'numInc']
            // };

            // var userList = new List('liste-inclusions', options);

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
            var url = document.location.toString();
            if (url.match('#')) {
                $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
            }

            // Change hash for page-reload
            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                e.preventDefault();
                // eviter les sauts aux anchres lors du changement de tab
                history.pushState( null, null, $(this).attr('href'));
                window.location.hash = e.target.hash;
            })
     	});