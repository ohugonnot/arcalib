        $(function() {

        	var id;
            var $modal = $("#arcoffice-document-pdf");
            var type = $("#document-type").data("type");

        	if ($modal.length > 0) {

        		var $input = $("#document-input");
        		var $download = $("#document-download");
                var $object = $("object");
                var $embed = $("embed");
                var $viewer = $("#viewer");
                var $removePdfButton = $("#remove-pdf-button");
                var $voirPdfButton = $("#voir-pdf-button");
                var button;

                $(document).on("click", "#viewer-content", function() {
                	$viewer.trigger("click");
				});

        		$(document).on("click", ".document-modal-button", function(e) {
                    button = $(this);
        			e.preventDefault();
        			id = $(this).data("id");
        			let route = 'getDocumentPDF';
        			if(type === "essai") {
        				route = 'getDocumentEssaiPDF';
					}
        			$.post(Routing.generate(route, {id: id}), {}, function(data) {
        				if (data.document != null) {
        					$input.hide();
        					$download.show();
        					$download.find("a").attr("href", data.document)
        				} else {
        					$input.show();
        					$download.hide();
        				}
        				$modal.modal('show');
        			});

        		});

        		$(document).on("click", ".remove-document-button", function(e) {

        			e.preventDefault();
					if ($(this).attr('data-id')) {
					  id = $(this).data("id");
					}

                    var route = 'removeDocumentPDF';
                    if(type === "essai") {
                        route = 'removeDocumentEssaiPDF';
                    }

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
                                    url: Routing.generate(route, {id: id}),

                                    success: function(response) {
                                        if (response.success) {
                                            $input.show();
                                            $download.hide();
                                            $download.find("a").removeAttr("href");
                                            if(typeof button !== 'undefined') {
                                                button.find("i").addClass("fa-upload").removeClass("fa-file-text");
                                            }
                                            $embed.attr("src", null);
                                            $object.addClass("d-none").attr("data", null);
                                            $viewer.removeClass("d-none").addClass("d-flex");
                                            $removePdfButton.addClass("d-none");
                                            $voirPdfButton.addClass("d-none");
                                            toastr.success( "Le pdf a été bien supprimé.");
                                        }
                                        resolve(response)
                                    },
                                    error: function(xhr) {
                                        toastr.error( "Vous n'avez pas les droits nécessaires pour cette action.");
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
                        })
                    })
                });

                function loaderShow(context) {
                    $("#"+context+"-loader").show();
                    $("#"+context+"-file").hide();
                }

                function loaderHide(context) {
                    $("#"+context+"-loader").hide();
                    $("#"+context+"-file").show();
                }

            if($("#document-input").length > 0) {
                var documentDropzone = new Dropzone("#document-input", {
                    url: "/", autoProcessQueue: true,
                    maxFilesize: 5,
                    acceptedFiles: "application/pdf"
                });

                documentDropzone.on("success", function(file, response) {
                    loaderHide("document");
                    $input.hide();
                    $download.show();
                    toastr.success( "Le pdf a été bien enregistré.");
                    button.find("i").removeClass("fa-upload").addClass("fa-file-text");
                    $download.find("a").attr("href", response.fileName);
                    $embed.attr("src", response.fileName);
                    $object.attr("data", response.fileName).show();
                });

                documentDropzone.on("sending", function() {
                    loaderShow("document");
                });

                documentDropzone.on("processing", function() {
                    var route = 'uploadDocumentPDF';
                    if(type == "essai") {
                        route = 'uploadDocumentEssaiPDF';
                    }
                    this.options.url = Routing.generate(route, {id: id});
                });

                documentDropzone.on("error", function() {
                    toastr.error( "Vous n'avez pas les droits nécessaires pour cette action ou le format PDF de maximum 5 Mo n'est pas respecté.");
                    loaderHide("document");
                });

            }

            if($("#viewer").length > 0) {
                  var documentViewerDropzone = new Dropzone("#viewer", { url: "/", autoProcessQueue: true,  maxFilesize: 5, acceptedFiles: "application/pdf"});

                    documentViewerDropzone.on("success", function(file, response) {
                        toastr.success( "Le pdf a été bien enregistré.");
                        $embed.attr("src", response.fileName);
                        $object.removeClass("d-none").attr("data", response.fileName);
                        $viewer.addClass("d-none").removeClass("d-flex");
                        $removePdfButton.removeClass("d-none");
                        $voirPdfButton.removeClass("d-none").find("a").attr("href", response.fileName);
                    });
                    documentViewerDropzone.on("processing", function() {
                        var id = $viewer.data("id");
                        $object.show();
                        var route = 'uploadDocumentPDF';
                        if(type == "essai") {
                            route = 'uploadDocumentEssaiPDF';
                        }
                        this.options.url = Routing.generate(route, {id: id});
                    });
                    documentViewerDropzone.on("error", function() {
                        toastr.error( "Vous n'avez pas les droits nécessaires pour cette action ou le format PDF de maximum 5 Mo n'est pas respecté.");
                    });
                }
        	}
        });