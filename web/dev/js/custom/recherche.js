// selectionner un protocole

if($("#rechercheAdvanced").length > 0) {

// L'entité patient en VueJS avec toute sa logique propre
var recherche = new Vue({  

      delimiters: ['[[', ']]'],
      el: '#rechercheAdvanced',
      data: { 
          essai : { 
            query: null, 
            results: [], 
            filters: {
                statut: ''
            },
            orderKey: 'nom',
            noResult: false,
            order: {"nom": 'asc', 'statut': 'asc', 'objectif': 'asc', "statut": "asc", 'inclusions': "asc"}
          },
          patient: { 
            query: null, 
            results: [],
            filters: {
              statut: ''
            },
            orderKey: 'nom',
            noResult: false,
            order: {"nom": 'asc', "datNai": "asc", "nomNaissance": "asc"}
          },                       
          inclusion: { 
            query: null, 
            results: [],
            filters: {
              statut: ''
            },
            orderKey: 'numInc',
            noResult: false,
            order: {"numInc": 'asc', "datInc": "asc", "patient": "asc", "essai": "asc" , "statut": "asc"}
          },                       
          annuaire: { 
            query: null, 
            results: [],
            filters: {
              statut: ''
            },
            orderKey: 'numInc',
            noResult: false,
            order: {"nom": 'asc', "societe": "asc", "fonction": "asc", "essai": "asc" , "mail": "asc", "telephone": "asc"}
          },
          medecin: { 
            query: null, 
            results: [],
            filters: {
              statut: ''
            },
            orderKey: 'nom',
            noResult: false,
            order: {"nom": 'asc', "prenom": "asc", 'inclusions': "asc"}
          },
          service: "",
          visite: {
              query: null,
              results: [],
              filters: {
                  statut: ''
              },
              orderKey: 'date',
              noResult: false,
              order: {"date": 'asc', "date_fin": "asc", "patient": "asc", "essai": "asc" , "statut": "asc"}
          },
      },


      methods: {   
        searchPatients: function() {
            $.post(Routing.generate("searchPatients", {query: this.patient.query }), { filters: this.patient.filters }, function(data) {
                  recherche.patient.results = data;
                  recherche.patient.noResult = recherche.patient.results.length == 0;
            });
        },

        searchEssais: function() {
                $.post(Routing.generate("searchEssais", {query: this.essai.query }), { filters: this.essai.filters }, function(data) {
                  recherche.essai.results = data;
                  recherche.essai.noResult = recherche.essai.results.length == 0;
            });    
        },

        searchInclusion: function() {
                $.post(Routing.generate("searchInclusions", {query: this.inclusion.query }), { filters: this.inclusion.filters }, function(data) {
                  recherche.inclusion.results = data;
                  recherche.inclusion.noResult = recherche.inclusion.results.length == 0;
            });    
        },

        searchAnnuaire: function() {
                $.post(Routing.generate("searchAnnuaires", {query: this.annuaire.query }), { filters: this.annuaire.filters }, function(data) {
                  recherche.annuaire.results = data;
                  recherche.annuaire.noResult = recherche.annuaire.results.length == 0;
            });    
        },
        searchMedecin: function() {
                $.post(Routing.generate("searchMedecins", {query: this.medecin.query }), { filters: this.medecin.filters }, function(data) {
                  recherche.medecin.results = data;
                  recherche.medecin.noResult = recherche.medecin.results.length == 0;
            });    
        },
        searchByService: function() {
                $.post(Routing.generate("searchEssais", {query: this.essai.query }), { filters: {'statut' : 'Inclusions ouvertes', 'service' : this.service} }, function(data) {
                  recherche.essai.results = data;
                  recherche.essai.noResult = recherche.essai.results.length == 0;
            }); 
        },
        searchByVisite: function() {
          $.post(Routing.generate("searchVisites", {query: this.visite.query }), { filters: this.visite.filters }, function(data) {
              recherche.visite.results = data;
              recherche.visite.noResult = recherche.visite.results.length == 0;
          });
        },
        // toggle les valeurs d'order dans les instances
        orderBy: function(key, type) {
            this[type].orderKey = key;
            if(this[type].order[this[type].orderKey] == "asc") {
              this[type].order[this[type].orderKey] = "desc";
            } else {
              this[type].order[this[type].orderKey] = "asc";
            }
            
        },
        linkInclusion: function(inclusion) {
            return Routing.generate("patient",{"id": inclusion.patient.id, "id_inclusion": inclusion.id });
        },
        linkEssai: function(essai) {
            return Routing.generate("editEssai",{"id": essai.id });
        },
        linkPatient: function(patient) {
            return Routing.generate("patient",{"id": patient.id });
        },
        linkAnnuaire: function(annuaire) {
            return Routing.generate("editAnnuaire",{"id": annuaire.id });
        },
        linkMedecin: function(medecin) {
            return Routing.generate("editMedecin",{"id": medecin.id });
        },
        linkVisite: function(visite) {
            return Routing.generate("patient",{"id": visite.inclusion.patient.id, "id_inclusion": visite.inclusion.id });
        },
        dayAfter: function() {
            let date = this.visite.query;
            if(date != null) {
                this.visite.query = moment(date, 'DD/MM/YYYY').add(1, 'day').format("DD/MM/YYYY");
            } else {
                this.visite.query = moment().format("DD/MM/YYYY");
            }
            this.searchByVisite();
        },
        dayBefore: function() {
            let date = this.visite.query;
            if(date != null) {
                this.visite.query = moment(date, 'DD/MM/YYYY').subtract(1, "day").format("DD/MM/YYYY");
            } else {
                this.visite.query = moment().format("DD/MM/YYYY");
            }
            this.searchByVisite();
        }
      },

      computed: {
          patientsFiltered: function() {  
            if(this.patient.orderKey == "datNai") {
                 var dates = _.sortBy(this.patient.results, [function(o) { return o.datNai.date; }]);  
                 if(this.patient.order[this.patient.orderKey] == "asc") {
                    return dates;
                 } else {
                    return dates.reverse();
                 }
            } 
            else {
              return _.orderBy(this.patient.results, [this.patient.orderKey], [this.patient.order[this.patient.orderKey]]); 
            }
          },
          essaisFiltered: function() {  
              return _.orderBy(this.essai.results, [this.essai.orderKey], [this.essai.order[this.essai.orderKey]]); 
          },
          inclusionsFiltered: function() {  
            if(this.inclusion.orderKey == "datInc") {
                 var dates = _.sortBy(this.inclusion.results, [function(o) { 
                  if(o.datInc != null) {
                        return o.datInc.date; 
                  }
                }]);  
                 if(this.inclusion.order[this.inclusion.orderKey] == "asc") {
                    return dates;
                 } else {
                    return dates.reverse();
                 }
            } 
            else if (this.inclusion.orderKey == "patient") {
                 var patients = _.sortBy(this.inclusion.results, [function(o) { return o.patient.nom; }]);  
                 if(this.inclusion.order[this.inclusion.orderKey] == "asc") {
                    return patients;
                 } else {
                    return patients.reverse();
                 }
            }
             else if (this.inclusion.orderKey == "essai") {
                 var essais = _.sortBy(this.inclusion.results, [function(o) { return o.essai.nom; }]);  
                 if(this.inclusion.order[this.inclusion.orderKey] == "asc") {
                    return essais;
                 } else {
                    return essais.reverse();
                 }
            }
            else {
              return _.orderBy(this.inclusion.results, [this.inclusion.orderKey], [this.inclusion.order[this.inclusion.orderKey]]); 
            } 
          },
          annuairesFiltered: function() {  
            if (this.annuaire.orderKey == "essai") {
                 var essais = _.sortBy(this.annuaire.results, [function(o) { return o.essai.nom; }]);  
                 if(this.annuaire.order[this.annuaire.orderKey] == "asc") {
                    return essais;
                 } else {
                    return essais.reverse();
                 }
            }
            else {
              return _.orderBy(this.annuaire.results, [this.annuaire.orderKey], [this.annuaire.order[this.annuaire.orderKey]]); 
            } 
          },
          medecinsFiltered: function() {  
              return _.orderBy(this.medecin.results, [this.medecin.orderKey], [this.medecin.order[this.medecin.orderKey]]); 
          },
          visitesFiltered: function() {
              if(this.visite.orderKey == "date") {
                  var dates = _.sortBy(this.visite.results, [function(o) {
                      if(o.date != null) {
                          return o.date.date;
                      }
                  }]);
                  if(this.visite.order[this.visite.orderKey] == "asc") {
                      return dates;
                  } else {
                      return dates.reverse();
                  }
              }
              else if (this.visite.orderKey == "patient") {
                  var patients = _.sortBy(this.visite.results, [function(o) { return o.inclusion.patient.nom; }]);
                  if(this.visite.order[this.visite.orderKey] == "asc") {
                      return patients;
                  } else {
                      return patients.reverse();
                  }
              }
              else {
                  return _.orderBy(this.visite.results, [this.visite.orderKey], [this.visite.order[this.visite.orderKey]]);
              }
          },
      },

      filters: {                     

      },

      mounted: function() {
          $('#recherche-medecin-date').datepicker({format: 'dd/mm/yyyy', language: 'fr', todayHighlight: true})
              .on("changeDate", function(e) {
                  recherche.visite.query  = $('#recherche-medecin-date').val()
                  recherche.searchByVisite();
              });
      },

      updated: function() {
          $('#recherche-medecin-date').datepicker('update');
      } 
});
}