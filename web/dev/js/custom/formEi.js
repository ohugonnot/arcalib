// selectionner un protocole

if ($("#formEi").length > 0) {

    window.formEi = new Vue({

        delimiters: ['[[', ']]'],
        el: '#formEi',
        data: {
            soc: '',
            term: '',
            grade: '',
            termOptions: [],
            gradeOptions: []
        },

        methods: {
            termOptionsSelect: function () {

                if (this.soc === '') {
                    this.termOptions = [{value: '', text: "Choisir d'abord une Soc"}];
                    return this
                }
                var $that = this;

                $.ajax({
                    url: Routing.generate("getTerms", {id: this.soc}),
                    type: 'POST',
                    data: {},
                    success: function (response) {
                        $that.termOptions = response;
                    }
                });
                return this;
            },
            gradeOptionsSelect: function () {

                if (this.term === '') {
                    this.gradeOptions = [{value: '', text: "Choisir d'abord un Term"}];
                    return this;
                }
                var $that = this;

                $.ajax({
                    url: Routing.generate("getGrades", {id: this.term}),
                    type: 'POST',
                    data: {},
                    success: function (response) {
                        $that.gradeOptions = response;
                    }
                });
                return this;
            },
            changeSoc: function () {
                this.grade = '';
                this.term = '';
                this.termOptionsSelect();
                this.gradeOptionsSelect();
            },
            changeTerm: function () {
                this.grade = '';
                this.gradeOptionsSelect();
            }
        },

        computed: {},

        watch: {},

        filters: {},

        created: function () {
            var selects = JSON.parse($("#ctcae").val());
            this.soc = selects.soc;
            this.term = selects.term;
            this.grade = selects.grade;
            this.termOptionsSelect().gradeOptionsSelect();
        },

        mounted: function () {


        },

        updated: function () {

        }
    });

}