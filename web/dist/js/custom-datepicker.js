$('#arcoffice-add-visite').on('shown.bs.modal', function () {
    $('#dtStartVisite').datetimepicker().on("dp.change",function() {
        var dtPickerEndVisite = $('#dtEndVisite');
        var dtStart = $('#dtStartVisite').val();
        var dtEnd = moment(dtStart, "DD/MM/YYYY hh:mm").add(1, 'hours');
        dtPickerEndVisite.val(dtEnd.format("DD/MM/YYYY hh:mm"));
    });

})