if ($("#patient").length > 0) {

    Vue.component('date-picker', VueBootstrapDatetimePicker);
    $.extend(true, $.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });

    window.patient = new Vue({

        delimiters: ['[[', ']]'],
        el: '#patient',
        data: {
            patient: {
                id: 0,
                sexe: null,
                deces: Patient_VIVANT,
                evolution: null,
                medecin: {id: null},
                libCim10: {id: null}
            },
            disabled: true
        },


        methods: {
            linkEssai: function (essai) {
                return Routing.generate("editEssai", {"id": essai.id});
            },
            newPatient: function () {
                this.patient = {
                    id: 0,
                    sexe: null,
                    deces: Patient_VIVANT,
                    evolution: null,
                    medecin: {id: null},
                    libCim10: {id: null}
                };
                this.disabled = false;
                inclusion.newInclusion();
                visite.resetVisite();
            },
            savePatient: function () {

                if (this.patient.nom == null || this.patient.nom === "") {
                    toastr.error('Le nom du patient ne doit pas être vide.');
                    return false;
                }
                if (this.patient.prenom == null || this.patient.prenom === "") {
                    toastr.error('Le prénom du patient ne doit pas être vide.');
                    return false;
                }
                if (!/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/.test(this.patient.datNai) && (!this.patient.datNai || !this.patient.datNai.date)) {
                    toastr.error("La date de naissance ne doit pas être vide et doit être une date valide.");
                    return false;
                }

                $('#savePatient').prop('disabled', true);
                $.post(Routing.generate("savePatient", {id: this.patient.id}), {appbundle_patient: this.patient}, function (data) {
                    if (!data.success) {
                        toastr.error(data.message);
                        $('#savePatient').prop('disabled', false);
                        return;
                    }
                    toastr.success("Le patient à été bien enregistré.");
                    patient.patient.id = data.patient.id;
                    inclusion.refreshPatient();
                    inclusion.patient = patient;
                })
                    .fail(function () {
                        toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");
                    })
                    .always(function () {
                        $('.title-tooltip').tooltip('hide');
                        $('#savePatient').prop('disabled', false);
                    });
            },
            deletePatient: function () {

                if (this.patient.id) {
                    let that = this;
                    swal({
                        title: 'Etes vous sûr ?',
                        text: "Vous allez supprimer un patient. Vous ne pourrez pas revenir en arrière !",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Supprimer',
                        cancelButtonText: 'Annuler'
                    }).then(function () {
                        $.post(Routing.generate("deletePatient", {id: that.patient.id}), {}, function () {
                            toastr.success("Le patient à été supprimé.");
                            that.newPatient();
                            inclusion.newInclusion();
                            visite.newVisite();
                            visite.visites = [];
                            patient.disabled = true;
                        }).fail(function () {
                            toastr.error(" Vous n'avez pas les droits nécessaires pour cette action.");
                        });
                    }).catch(swal.noop)
                }
            },
            getInclusion: function (id) {
                inclusion.loading = true;
                visite.loading = true;
                $.post(Routing.generate('getInclusion', {id: id}), {}, function (data) {
                    inclusion.inclusion = data;
                    visite.visites = inclusion.inclusion.visites;
                    visite.inclusion = inclusion;
                    inclusion.loading = false;
                    visite.loading = false;
                    $('.patient-liste-inclusion tr').removeClass("is-active");
                    $('.patient-liste-inclusion tr .patient-get-inclusion').removeClass("btn-primary");
                    $('.patient-liste-inclusion tr[data-id=' + id + ']').addClass("is-active");
                    $('.patient-liste-inclusion tr[data-id=' + id + '] .patient-get-inclusion').addClass("btn-primary");
                    insertParam("id_inclusion", id);
                    insertParam("id", patient.patient.id);
                    let id_visite = getUrlParameter("id_visite");
                    if (id_visite) {
                        const index = visite.visites.findIndex(function (element) {
                            return parseInt(element.id) === parseInt(id_visite)
                        });
                        if (index >= 0)
                            visite.openModal(index)
                    }
                });
            }
        },

        mounted: function () {
            $('#date-dernieres-nouvelles').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    patient.patient.datLast = $('#date-dernieres-nouvelles').val()
                });
            $('#date-diagnostic').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    patient.patient.datDiag = $('#date-diagnostic').val()
                });
            $('#date-de-naissance').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    patient.patient.datNai = $('#date-de-naissance').val()
                });
            $('#date-deces').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    patient.patient.datDeces = $('#date-deces').val()
                });
        },

        updated: function () {
            $('.title-tooltip').tooltip({
                trigger: "hover",
                title: function () {
                    return $(this).attr('title');
                }
            });
            $('#date-dernieres-nouvelles').datepicker('update');
            $('#date-diagnostic').datepicker('update');
            $('#date-de-naissance').datepicker('update');
            $('#date-deces').datepicker('update');
        }
    });

    window.recherche = new Vue({
        delimiters: ['[[', ']]'],
        el: '#recherche',
        data: {
            recherche: null
        },

        methods: {
            search: function () {
                let that = this;
                $.post(Routing.generate('recherchePatient', {query: this.recherche}), {}, function (data) {
                    if (data[0]) {
                        selectPatient(data[0]);
                        that.recherche = '';
                    } else {
                        toastr.warning("Aucun résultat pour votre recherche.");
                    }
                });
            },
            selectPatient: function (id) {
                $.post(Routing.generate("selectPatient", {id: id}), {}, function (data) {

                    if (data.length === 0) {
                        toastr.error("Vous n'avez pas les droits pour accéder à ce patient.");
                        return false;
                    }

                    patient.patient = data;
                    patient.disabled = false;

                    let id_inclusion = getUrlParameter("id_inclusion");
                    if (id_inclusion) {
                        patient.getInclusion(id_inclusion);
                    } else if (patient.patient.inclusions.length > 0 && inclusion.inclusion.id === 0) {
                        patient.getInclusion(patient.patient.inclusions[0].id);
                    }

                    insertParam("id", id);
                });
            }

        }
    });


    window.inclusion = new Vue({

        delimiters: ['[[', ']]'],
        el: '#inclusion',
        data: {
            inclusion: {
                id: 0,
                medecin: {id: null},
                arc: {id: null, service: {nom: null}},
                essai: {id: null},
                service: {id: null},
                statut: null,
                documents: [],
                traitements: [],
                eis: [],
                events: [],
                motifSortie: null,
                booRa: null,
                booBras: null
            },
            patient: null,
            loading: false
        },


        methods: {
            newInclusion: function () {
                this.inclusion = {
                    id: 0,
                    medecin: {id: null},
                    arc: {id: null, service: {nom: null}},
                    essai: {id: null},
                    service: {id: null},
                    statut: null,
                    documents: [],
                    traitements: [],
                    eis: [],
                    events: [],
                    motifSortie: null,
                    booRa: null,
                    booBras: null
                };
                this.loading = false;
                visite.visites = [];
            },

            saveInclusion: function () {

                if (patient.patient.id === 0) {
                    toastr.warning("Il faut séléctionner un patient sauvegardé avant de créer une inclusion.");
                    return;
                }

                if (this.inclusion.essai.id == null) {
                    toastr.warning("Il faut séléctionner un protocole pour pouvoir sauvegarder une inclusion.");
                    return;
                }

                $('#saveInclusion').prop('disabled', true);
                $.post(Routing.generate("saveInclusion", {id: this.inclusion.id}), {
                    appbundle_inclusion: this.inclusion,
                    patient: patient.patient.id
                }, function (data) {
                    inclusion.patient = patient;
                    inclusion.inclusion.id = data.inclusion.id;
                    inclusion.refreshPatient(inclusion.inclusion.id);
                    toastr.success("L'inclusion a bien été enregistrée.");
                }).fail(function () {
                    toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");

                }).always(function () {
                    $('.title-tooltip').tooltip('hide');
                    $('#saveInclusion').prop('disabled', false);
                });
            },

            deleteInclusion: function () {

                if (this.inclusion.id) {

                    swal({
                        title: 'Etes vous sûr ?',
                        text: "Vous allez supprimer une inclusion. Vous ne pourrez pas revenir en arrière!",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Supprimer',
                        cancelButtonText: 'Annuler'
                    }).then(function () {
                        $.post(Routing.generate("deleteInclusion", {id: inclusion.inclusion.id}), {}, function () {
                            inclusion.refreshPatient();
                            inclusion.newInclusion();
                            toastr.success("L'inclusion à été supprimée.");
                        }).fail(function () {
                            toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");
                        });
                    }).catch(swal.noop)

                }

            },

            refreshPatient: function (id_inclusion) {
                $.post(Routing.generate("selectPatient", {id: patient.patient.id}), {}, function (data) {
                    patient.patient = data;
                    if (id_inclusion !== undefined) {
                        patient.getInclusion(id_inclusion);
                    } else if (patient.patient.inclusions.length > 0 && inclusion.inclusion.id === 0) {
                        patient.getInclusion(patient.patient.inclusions[0].id);
                    }
                    patient.disabled = false;
                });
            }
        },
        computed: {
            motif: function () {
                return this.inclusion.statut;
            },
            datRanDisabled: function () {
                return this.inclusion.booRa;
            },
            braTrtDisabled: function () {
                return this.inclusion.booBras;
            },
            disabled: function () {
                return !patient.patient.id;
            },
            linkListeDocuments: function () {
                return (this.inclusion.id) ? Routing.generate('inclusion_list_documents', {id: this.inclusion.id}) : null;
            },
            linkListeTraitements: function () {
                return (this.inclusion.id) ? Routing.generate('listeTraitements', {id: this.inclusion.id}) : null;
            },
            firstTraitement: function () {
                return (this.inclusion.id) ? Routing.generate('voirTraitement', {id: this.inclusion.id}) : null;
            },
            firstEi: function () {
                return (this.inclusion.id) ? Routing.generate('voirEi', {id: this.inclusion.id}) : null;
            },
            firstEvent: function () {
                return (this.inclusion.id) ? Routing.generate('voirEvent', {id: this.inclusion.id}) : null;
            },
            addDocument: function () {
                return (this.inclusion.id) ? Routing.generate('addDocument', {id: this.inclusion.id}) : null;
            },
            firstDocument: function () {
                return (this.inclusion.id) ? Routing.generate('voirDocument', {id: this.inclusion.id}) : null;
            }
        },

        watch: {
            motif: function (val) {
                if (val !== Inclusion_OUI_SORTIE) {
                    this.inclusion.datOut = null;
                    this.inclusion.motifSortie = null;
                    $('#sortie').datepicker('update');
                }
            },
            datRanDisabled: function (val) {
                if (val) {
                    this.inclusion.datRan = null;
                    $('#randomisation').datepicker('update');
                }
            },
            braTrtDisabled: function (val) {
                if (val) {
                    this.inclusion.braTrt = null;
                }
            }
        },
        mounted: function () {

            $('#consentement').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datCst = $('#consentement').val()
                });
            $('#screen').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datScr = $('#screen').val()
                });
            $('#date-inclusion').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datInc = $('#date-inclusion').val()
                });
            $('#randomisation').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datRan = $('#randomisation').val()
                });
            $('#date-j0').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datJ0 = $('#date-j0').val()
                });
            $('#sortie').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    inclusion.inclusion.datOut = $('#sortie').val();
                });

            $('#arc').select2({
                width: '100%',
                language: "fr"
            }).on('select2:select', function () {
                inclusion.inclusion.arc.id = $(this).val();
            });

        },
        updated: function () {
            $('.title-tooltip').tooltip({
                trigger: "hover",
                title: function () {
                    return $(this).attr('title');
                }
            });
            $('#arc').trigger("change");
            $('#consentement').datepicker('update');
            $('#screen').datepicker('update');
            $('#date-inclusion').datepicker('update');
            $('#randomisation').datepicker('update');
            $('#date-j0').datepicker('update');
            $('#sortie').datepicker('update');
        }
    });

    window.visite = new Vue({

        delimiters: ['[[', ']]'],
        el: '#visite',
        data: {
            visites: [],
            inclusion: null,
            visiteSelected: {id: 0, arc: {id: null}, type: null, statut: null},
            loading: false,
            options: {
                format: 'DD/MM/YYYY HH:mm',
                useCurrent: false,
                //    enabledHours: [8,9,10,11,12,13,14,15,16,17,18,19],
            },
        },

        methods: {
            openModal: function (index) {
                this.visiteSelected = this.visites[index];
                this.old = JSON.parse(JSON.stringify(this.visites[index]));
                this.visiteSelected.index = index;
                $("#arcoffice-add-visite").modal('show');
            },
            newVisite: function () {
                this.visiteSelected = {id: 0, arc: {id: null}, type: null, statut: null};
            },
            resetVisite: function () {
                this.visites = [];
                this.inclusion = null;
                this.visiteSelected = {id: 0, arc: {id: null}, type: null, statut: null};
            },
            deleteVisite: function () {
                $('#deleteVisite').prop('disabled', true);
                $.post(Routing.generate('deleteVisite', {id: this.visiteSelected.id}), {}, function () {
                    visite.visites = visite.visites.filter(function (item) {
                        return visite.visiteSelected.id !== item.id;
                    });
                    visite.newVisite();
                    $("#arcoffice-add-visite").modal('hide');
                    toastr.success("La visite à bien été supprimée.");
                    $('#deleteVisite').prop('disabled', false);
                });

            },

            cancel: function () {
                this.visites[this.visiteSelected.index] = this.old;
                visite.$forceUpdate();
            },
            isPast: function (visite) {
                if (visite.date) {
                    let difhours = moment().diff(moment(visite.date, 'DD/MM/YYYY HH:mm'), 'hours');
                    if (difhours < -30 * 24) {
                        return "more-30-days";
                    } else if (difhours > -30 * 24 && difhours < 0) {
                        return "less-30-days";
                    } else {
                        return "past";
                    }
                }

            },
            prevVisite: function () {
                if (this.visiteSelected.id === 0) {
                    return;
                }
                let indexPrevVisite = (this.visiteSelected.index - 1);
                indexPrevVisite = (indexPrevVisite < 0) ? this.visites.length - 1 : indexPrevVisite;
                this.visites[this.visiteSelected.index] = this.old;
                this.old = JSON.parse(JSON.stringify(this.visites[indexPrevVisite]));
                this.visiteSelected = this.visites[indexPrevVisite];
                this.visiteSelected.index = indexPrevVisite;
            },
            nextVisite: function () {
                if (this.visiteSelected.id === 0) {
                    return;
                }
                let indexNextVisite = (this.visiteSelected.index + 1) % this.visites.length;
                this.visites[this.visiteSelected.index] = this.old;
                this.old = JSON.parse(JSON.stringify(this.visites[indexNextVisite]));
                this.visiteSelected = this.visites[indexNextVisite];
                this.visiteSelected.index = indexNextVisite;
            },
            changeDateVisite: function () {
                if (!this.visiteSelected.date_fin && moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm").isValid()) {
                    this.visiteSelected.date_fin = moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm").add(1, 'hours').format("DD/MM/YYYY HH:mm");
                    visite.$forceUpdate();
                }
            },
            changeAllDay: function (e) {
                console.log(this.visiteSelected, e);
                if (this.visiteSelected.all_day) {
                    this.visiteSelected.date = moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm").startOf('day').format("DD/MM/YYYY HH:mm");
                    this.visiteSelected.date_fin = moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm").endOf('day').format("DD/MM/YYYY HH:mm");
                }
            },
            saveVisite: function () {

                if (!this.visiteSelected.date) {
                    toastr.error('La visite doit avoir une date de debut.');
                    return false;
                }

                if (!this.visiteSelected.date_fin) {
                    this.visiteSelected.date_fin = moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm").add(1, 'hours').format("DD/MM/YYYY HH:mm");
                } else {
                    let debut = moment(this.visiteSelected.date, "DD/MM/YYYY HH:mm");
                    let fin = moment(this.visiteSelected.date_fin, "DD/MM/YYYY HH:mm");
                    if (fin < debut) {
                        toastr.error('La date de fin doit être après la date de debut.');
                        return false;
                    }
                }

                $('#saveVisite').prop('disabled', true);
                $.post(Routing.generate("saveVisite", {id: this.visiteSelected.id}), {
                    appbundle_visite: this.visiteSelected,
                    inclusion: inclusion.inclusion.id
                }, function (data) {
                    toastr.success("La visite a été bien enregistrée.");
                    if (visite.visiteSelected.id === 0) {
                        visite.visiteSelected.id = data.visite.id;
                        visite.visites.push(visite.visiteSelected);
                    }
                    visite.newVisite();
                    visite.$forceUpdate();
                    $('#saveVisite').prop('disabled', false);
                    $('.title-tooltip').tooltip('hide');
                    $("#arcoffice-add-visite").modal('hide');
                }).fail(function (e) {
                    toastr.success("Erreur dans la requête.");
                    console.log(e);
                });

            }

        },
        computed: {
            action: function () {
                if (this.visiteSelected.id) {
                    return "Édition de la visite - " + patient.patient.nom + " " + patient.patient.prenom;
                } else {
                    return "Création d'une nouvelle visite";
                }
            },
            disabled: function () {
                return !inclusion.inclusion.id;
            }
            // modalText: function() {
            //   if(!this.visiteSelected.id){
            //      return "Vous allez ajouter une nouvelle visite à "+patient.patient.nom+" "+patient.patient.prenom;
            //   } else {
            //      return "Vous allez éditer la visite N°"+ this.visiteSelected.id+" du patient "+patient.patient.nom+" "+patient.patient.prenom;
            //    }
            // }
        },
        mounted: function () {
            $('#date-visite').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function () {
                    visite.visiteSelected.date = $('#date-visite').val();
                });
            $('#arc-visite').select2({
                width: '100%',
                language: "fr"
            }).on('select2:select', function () {
                visite.visiteSelected.arc.id = $(this).val();
            });

        },
        updated: function () {
            $('.title-tooltip').tooltip({
                trigger: "hover",
                title: function () {
                    return $(this).attr('title');
                }
            });
            $('#arc-visite').trigger("change");
            $('#date-visite').datepicker('update');
        }
    });

    // Autocompletation du moteur de recherche de patient
    const patients = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: Routing.generate("recherchePatient", {query: 'QUERY'}),
            wildcard: 'QUERY',
            cache: false,
            transform: function (response) {
                // Map the remote source JSON array to a JavaScript object array
                return $.map(response, function (patient) {
                    return {
                        value: patient.nom + ' ' + patient.prenom,
                        patient: patient
                    };
                });
            }
        }
    });

    let recherche_input = $('#input-recherche');

    recherche_input.typeahead({
        highlight: true,
        minLength: 1,
        hint: true
    }, {
        name: 'patients',
        display: 'value',
        source: patients,
        limit: 1000
    }).bind('typeahead:select', function (ev, suggestion) {
        recherche.recherche = suggestion.value;
        selectPatient(suggestion.patient);

    });

    function selectPatient(patientReponse) {

        // Quand on selectionne un patient on ferme le typehead et on repasse la recherche a vide
        recherche_input.typeahead('close').typeahead('val', '');
        recherche.recherche = '';
        patient.patient.id = patientReponse.id;
        patient.disabled = false;
        inclusion.refreshPatient();
        inclusion.newInclusion();
        visite.resetVisite();
        inclusion.patient = patient;
        $(".patient-liste-inclusion tr").removeClass("is-active");
        $(".patient-liste-inclusion tr .patient-get-inclusion").removeClass("btn-primary");
    }


    jQuery(document).ready(function ($) {
        let id_patient = getUrlParameter("id");
        if (id_patient) {
            recherche.selectPatient(id_patient);
        }

        $('.js-datepicker').datepicker().on('changeDate', function () {
            $(this).datepicker("hide")
        });
    });
}