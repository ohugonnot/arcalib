// selectionner un protocole

if ($("#protocole").length > 0) {

    const couleur = {
        "Faisabilité en attente": "violet-fonce",
        "Convention signature": "bleu",
        "Inclusions ouvertes": "vert",
        "Inclusions closes, suivi": "vert-fonce",
        "Queries et finalisation": "jaune",
        "Clos, en attente payement": "orange",
        "Archivé": "rouge",
        "Autre": "blanc",
        "Refus": "noir",
        "Attente de MEP": "vert-bleu"
    };

// L'entité patient en VueJS avec toute sa logique propre
    window.protocole = new Vue({

        delimiters: ['[[', ']]'],
        el: '#protocole',
        data: {
            protocole: {
                id: 0,
                statut: null,
                typeEssai: null,
                stadeEss: null,
                typeProm: null,
                autoProm: null,
                typeConv: null,
                inclusions: [],
                fils: [],
                services: [],
                medecin: {id: null},
                arc: {id: null},
                arcBackup: {id: null},
                detail: {}
            },
            disabled: true,
            searchInclusion: null,
            loading: false,
            patientSelected: {id: 0},
            filSelected: {id: 0},
            orderKey: 'numInc',
            order: {
                "numInc": 'asc',
                "datInc": "asc",
                "nom": "asc",
                "medecin": "asc",
                'statut': "asc",
            }
        },


        methods: {
            linkPatient: function (patient) {
                if (patient) {
                    return Routing.generate("patient", {"id": patient.id})
                }
            },
            linkInclusion: function (inclusion) {
                return Routing.generate("patient", {"id": inclusion.patient.id, "id_inclusion": inclusion.id});
            },
            linkDocument: function () {
                if (this.protocole.id !== 0) {
                    return Routing.generate("voirDocumentEssai", {"id": this.protocole.id});
                }
            },
            newProtocole: function () {
                this.protocole = {
                    id: 0,
                    statut: null,
                    typeEssai: null,
                    stadeEss: null,
                    typeProm: null,
                    autoProm: null,
                    typeConv: null,
                    inclusions: [],
                    services: [],
                    medecin: {id: null},
                    arc: {id: null},
                    detail: {}
                };
                this.disabled = false;
                this.loading = false;
            },
            saveEssai: function () {

                if (this.protocole.nom == null || this.protocole.nom === "") {
                    toastr.error('Le nom du protocole ne doit pas être vide.');
                    return false;
                }

                if (!this.validateEmail(this.protocole.contactMail)) {
                    toastr.error('Email doit être un email valide.');
                    return false;
                }

                if (!this.isUrlValid(this.protocole.ecrfLink)) {
                    toastr.error('Le lien ECRF doit être un lien valide de type http(s)://');
                    return false;
                }

                $('#saveEssai').prop('disabled', true);
                let protocoleClone = jQuery.extend({}, this.protocole);
                protocoleClone.inclusions = null;
                $.post(Routing.generate("saveEssai", {id: this.protocole.id}), {appbundle_essais: protocoleClone}, function (data) {

                    if (!data.success) {
                        toastr.error(data.message);
                        $('#saveEssai').prop('disabled', false);
                        return;
                    }
                    toastr.success("Le protocole à été bien enregistré.");
                    if (protocole.protocole.id === 0) {
                        location.reload();
                    }

                    protocole.protocole.id = data.protocole.id;
                    $('a[data-id="' + protocole.protocole.id + '"').html(protocole.protocole.nom).removeClass().addClass("select-protocole title-tooltip d-flex is-active arcoffice-" + couleur[protocole.protocole.statut]).attr("data-original-title", protocole.protocole.statut)
                }).fail(function () {
                    toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");

                })
                    .always(function () {
                        $('.title-tooltip').tooltip('hide');
                        $('#saveEssai').prop('disabled', false);
                    });
            },
            validateEmail: function (email) {
                if (!email) {
                    return true;
                }
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            },
            isUrlValid: function (url) {
                if (!url) {
                    return true;
                }
                const re = /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)_\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
                return re.test(url);
            },
            deleteEssai: function () {

                let that;
                if (this.protocole.id) {
                    that = this;

                    swal({
                        title: 'Etes vous sûr ?',
                        text: "Vous allez supprimer un protocole. Vous ne pourrez pas revenir en arrière !",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Supprimer',
                        cancelButtonText: 'Annuler'
                    }).then(function () {
                        $.post(Routing.generate("deleteEssai", {id: that.protocole.id}), {}, function (data) {
                            toastr.success("Le protocole à été supprimé.");
                            $(".col-liste-protocole a[data-id='" + that.protocole.id + "'").remove();
                            protocole.newProtocole();
                        }).fail(function () {
                            toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");
                        });
                    }).catch(swal.noop)

                }

            },
            openModal: function (index) {
                this.patientSelected = this.inclusionsFiltered[index];
                this.old = JSON.parse(JSON.stringify(this.inclusionsFiltered[index]));
                this.patientSelected.index = index;
                $("#arcoffice-protocole-patient").modal('show');
            },
            openModalFil: function () {
                $("#arcoffice-protocole-fil").modal('show');
            },
            selectFil: function (index) {
                this.filSelected = this.protocole.fils[index];
            },
            addFil: function () {
                this.protocole.fils.push({date: null, type: null, texte: null})
            },
            deleteFil: function (index) {
                this.protocole.fils.splice(index, 1);
            },
            saveFil: function () {
                $.post(Routing.generate("saveFil", {id: this.protocole.id}), {appbundle_fils: this.protocole.fils}, function (data) {
                    if (!data.success) {
                        toastr.error(data.message);
                        return;
                    }
                    data.ids.forEach(function (el, index) {
                        Vue.set(protocole.protocole.fils[index], 'id', el)
                    });
                    toastr.success("Les fils ont bien étés sauvés.");
                }).fail(function () {
                    toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");
                }).always(function () {
                });
            },
            cancel: function () {
                this.inclusionsFiltered[this.patientSelected.index] = this.old;
                this.$forceUpdate();
            },
            saveInclusion: function () {

                if (!this.patientSelected.datInc) {
                    toastr.error('Le patient doit avoir une date d\'inclusion.');
                    return false;
                }
                $('#saveInclusion').prop('disabled', true);
                $.post(Routing.generate("editInclusionPartial", {id: this.patientSelected.id}), {appbundle_inclusion: this.patientSelected}, function (data) {
                    toastr.success("L\'inclusion a été bien enregistrée.");
                    protocole.$forceUpdate();
                }).fail(function () {
                    toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");

                })
                    .always(function () {
                        $('#saveInclusion').prop('disabled', false);
                        $("#arcoffice-protocole-patient").modal('hide');
                        $('.title-tooltip').tooltip('hide');
                        protocole.cancel();
                    });

            },
            orderBy: function (key) {
                this.orderKey = key;
                if (this.order[this.orderKey] === "asc") {
                    this.order[this.orderKey] = "desc";
                } else {
                    this.order[this.orderKey] = "asc";
                }
            },
            createLinkPdf: function (type) {
                return Routing.generate("removeProtocolePDF", {type: type, id: this.protocole.id});
            },
            downloadPDF: function () {
                return Routing.generate("downloadProtocolePDF", {pdf: null});
            },
            deletePDF: function (type) {

                let that = this;

                swal({
                    title: 'Etes vous sûr ?',
                    text: "Vous allez supprimer le PDF. Vous ne pourrez pas revenir en arrière !",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Supprimer',
                    cancelButtonText: 'Annuler'
                }).then(function () {
                    $.post(that.createLinkPdf(type), {}, function (data) {
                        toastr.success("Le pdf a été bien supprimé.");
                        protocole.protocole[type] = null;
                    }).fail(function () {
                        toastr.error("Vous n'avez pas les droits nécessaires pour cette action.");

                    });
                }).catch(swal.noop)

            },
        },
        computed: {
            id: function () {
                return this.protocole.id;
            },
            inclusionsFiltered: function () {
                let inclusions = this.protocole.inclusions;
                if (this.searchInclusion) {
                    let search = this.searchInclusion.toLowerCase().trim();
                    inclusions = _.filter(this.protocole.inclusions, (inc) => {
                        return (inc.datInc && inc.datInc.includes(search))
                            || (inc.numInc && inc.numInc.toLowerCase().includes(search))
                            || (inc.statut && inc.statut.toLowerCase().includes(search))
                            || (inc.patient && (inc.patient.nom.toLowerCase().includes(search) || inc.patient.prenom.toLowerCase().includes(search)))
                            || (inc.medecin && (inc.medecin.nom.toLowerCase().includes(search) || inc.medecin.prenom.toLowerCase().includes(search)))
                    });
                }
                if (this.orderKey === "datInc") {
                    let dates = _.sortBy(inclusions, [function (o) {
                        if (o.datInc) {
                            return moment(o.datInc, 'DD/MM/YYYY', true);
                        }
                    }]);
                    if (this.order[this.orderKey] === "asc") {
                        return dates;
                    } else {
                        return dates.reverse();
                    }
                } else if (this.orderKey === "nom") {
                    let patients = _.sortBy(inclusions, [function (o) {
                        if (o.patient)
                            return o.patient.nom;
                    }]);
                    if (this.order[this.orderKey] === "asc") {
                        return patients;
                    } else {
                        return patients.reverse();
                    }
                } else if (this.orderKey === "medecin") {
                    let medecin = _.sortBy(inclusions, [function (o) {
                        if (o.medecin)
                            return o.medecin.nom || '' + o.medecin.prenom || '';
                    }]);
                    if (this.order[this.orderKey] === "asc") {
                        return medecin;
                    } else {
                        return medecin.reverse();
                    }
                } else {
                    return _.orderBy(inclusions, [this.orderKey], [this.order[this.orderKey]]);
                }
            },
            eudraCtNdDisabled: function () {
                return this.protocole.eudraCtNd;
            },
            ctNdDisabled: function () {
                return this.protocole.ctNd;
            },
            datesDisabled: function () {
                return this.protocole.statut === Essais_FAISABILITE_EN_ATTENTE
                    || this.protocole.statut === Essais_CONVENTION_SIGNATURE
                    || this.protocole.statut === Essais_ATTENTE_DE_MEP
                    || this.protocole.statut === Essais_REFUS;
            },
            dateClotDisabled: function () {
                return this.protocole.statut === Essais_INCLUSIONS_OUVERTES;
            },
        },
        watch: {
            id: function () {
                $('#protocole-tags').tagsinput('removeAll');
                $('#protocole-tags').off('itemAdded itemRemoved', majTag);
                for (let index in this.protocole.tags) {
                    if (protocole.protocole.tags[index]) {
                        $('#protocole-tags').tagsinput('add', protocole.protocole.tags[index].nom);
                    }
                }
                $('#protocole-tags').on('itemAdded itemRemoved', majTag);
            },
            eudraCtNdDisabled: function (val) {
                if (val) {
                    this.protocole.numEudract = null;
                }
            },
            ctNdDisabled: function (val) {
                if (val) {
                    this.protocole.numCt = null;
                }
            },
        },


        filters: {
            date: function (value) {
                if (value != null) {
                    return moment(value).format('DD/MM/YYYY');
                }

            }
        },


        mounted: function () {
            $('#protocole-date-mep').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function (e) {
                    protocole.protocole.dateOuv = $('#protocole-date-mep').val()
                });
            $('#protocole-date-fin').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function (e) {
                    protocole.protocole.dateFinInc = $('#protocole-date-fin').val()
                });
            $('#protocole-date-close').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                .on("changeDate", function (e) {
                    protocole.protocole.dateClose = $('#protocole-date-close').val()
                });
            $('#protocole-date-signature-convention').datepicker({
                format: 'dd/mm/yyyy',
                language: 'fr',
                todayHighlight: true
            })
                .on("changeDate", function (e) {
                    protocole.protocole.dateSignConv = $('#protocole-date-signature-convention').val()
                });
            $('#protocole-patient-date-inclusion').datepicker({
                format: 'dd/mm/yyyy',
                language: 'fr',
                todayHighlight: true
            })
                .on("changeDate", function (e) {
                    protocole.patientSelected.datInc = $('#protocole-patient-date-inclusion').val()
                });
        },
        updated: function () {
            $('.fil-date').each(function (index, element) {
                $(element).datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
                    .off('changeDate.update-datepicker')
                    .on("changeDate.update-datepicker", function (e) {
                        protocole.filSelected.date = $(element).val();
                    });
            });
            $('.datepicker')
                .on('changeDate.update-datepicker', function (e) {
                    $(this).datepicker("hide")
                });
            $('.title-tooltip').tooltip({
                trigger: "hover",
                title: function () {
                    return $(this).attr('title');
                }
            });
            $('#protocole-date-mep').datepicker('update');
            $('#protocole-date-fin').datepicker('update');
            $('#protocole-date-close').datepicker('update');
            $('#protocole-date-signature-convention').datepicker('update');
            $('#protocole-patient-date-inclusion').datepicker('update');
            $('.fil-date').datepicker('update');
        }
    });


    jQuery(document).ready(function ($) {
        let id_protocole = getUrlParameter("id");
        if (id_protocole) {
            let $protocole = $("a[data-id='" + id_protocole + "'");
            if ($protocole.length !== 0) {
                $("a[data-id='" + id_protocole + "'").trigger('click');
            } else {
                toastr.error("Ce protocole n'existe pas ou vous n'avez pas les droits pour y accéder.");
            }
        }
        $('.datepicker').datepicker().on('changeDate', function (e) {
            $(this).datepicker("hide")
        });
    });

    $(document).on('click', ".select-protocole", function (e) {
        e.preventDefault();
        let id_protocole = $(this).attr("data-id");
        let archive = $("#link-archive").data("archive");
        $('.select-protocole').removeClass('is-active');
        $(this).addClass('is-active');
        protocole.loading = true;
        $.post(Routing.generate("selectProtocole", {id: id_protocole}), {}, function (data) {
            protocole.protocole = data;
            protocole.loading = false;
            protocole.disabled = false;
            insertParam("id", id_protocole);
            insertParam("archive", archive);
        });

        $('.js-datepicker').datepicker().on('changeDate', function (e) {
            $(this).datepicker("hide")
        });
    });

    // Autocompletation du moteur de recherche de patient
    const essais = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: Routing.generate("rechercheEssai", {query: 'QUERY'}),
            wildcard: 'QUERY',
            cache: false,
            transform: function (response) {
                // Map the remote source JSON array to a JavaScript object array
                return $.map(response, function (essai) {
                    return {
                        value: essai.nom,
                        id: essai.id
                    };
                });
            }
        }
    });

    $('#input-recherche').typeahead({
        highlight: true,
        minLength: 1,
        hint: true,
    }, {
        name: 'essais',
        display: 'value',
        source: essais,
        limit: 1000
    }).bind('typeahead:select', function (ev, suggestion) {

        $('.select-protocole').removeClass('is-active');
        $('.select-protocole[data-id="' + suggestion.id + '"]').addClass('is-active');
        protocole.loading = true;
        $.post(Routing.generate("selectProtocole", {id: suggestion.id}), {}, function (data) {
            protocole.protocole = data;
            $('#input-recherche').typeahead('close').typeahead('val', '');
            protocole.loading = false;
            protocole.disabled = false;
        });

    });

    const tags = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('nom'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: Routing.generate("searchTag", {query: 'QUERY'}),
            wildcard: 'QUERY',
            transform: function (response) {
                return $.map(response, function (tag) {
                    return {
                        nom: tag.nom,
                        id: tag.id
                    };
                });
            }
        }
    });
    tags.initialize();

    $('#protocole-tags').tagsinput({
        typeaheadjs: {
            name: 'tags',
            displayKey: 'nom',
            valueKey: 'nom',
            source: tags.ttAdapter()
        }
    });

    $('#protocole-tags').on('beforeItemRemove', function (event) {

        if (confirm("Vous désirez vraiment supprimer le tag du protocole ?")) {

        } else {
            event.cancel = true;
        }
    });


    function majTag(e) {
        protocole.protocole.tags = $('#protocole-tags').tagsinput("items");
    }

}