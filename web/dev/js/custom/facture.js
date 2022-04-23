        $(function() {

        	var id;
            var $modal = $("#arcoffice-facture-pdf");

        	if ($modal.length > 0) {

        		var $input = $("#facture-input");
        		var $download = $("#facture-download");

        		$(document).on("click", ".facture-modal-button", function(e) {

        			e.preventDefault();
        			id = $(this).data("id");
        			$.post(Routing.generate("getFacturePDF", {id: id}), {}, function(data) {
        				if (data.facture != null) {
        					$input.hide();
        					$download.show();
        					$download.find("a").attr("href", data.facture)
        				} else {
        					$input.show();
        					$download.hide();
        				}
        				$modal.modal('show');
        			});

        		});

        		$(document).on("click", ".remove-facture-button", function(e) {

        			e.preventDefault();
        			$.post(Routing.generate("removeFacturePDF", {id: id}), {}, function(data) {
        				if (data.success) {
        					$input.show();
        					$download.hide();
        					$download.find("a").removeAttr("href");
        					toastr.success( "Le pdf a été bien supprimé.");
        				} 
        			}).fail(function() {
		                toastr.error( "Vous n'avez pas les droits nécessaires pour cette action.");
		            });

        		});
			
			function loaderShow(context) {
			    $("#"+context+"-loader").show();
			    $("#"+context+"-file").hide();
			}

			function loaderHide(context) {
				$("#"+context+"-loader").hide();
			    $("#"+context+"-file").show();			
			}

			var factureDropzone = new Dropzone("#facture-input", { url: "/", autoProcessQueue: true,  maxFilesize: 3, acceptedFiles: "application/pdf"});

			  factureDropzone.on("success", function(file, response) {
			  	loaderHide("facture");
			  	$input.hide();
        		$download.show();
			    toastr.success( "Le pdf a été bien enregistré.");
        		$download.find("a").attr("href", response.fileName)
			  });
			  factureDropzone.on("sending", function() {
			   	loaderShow("facture");
			  });
			  factureDropzone.on("processing", function() {
			     this.options.url = Routing.generate("uploadFacturePDF", {id: id});
			  });
			  factureDropzone.on("error", function() {
			     toastr.error( "Vous n'avez pas les droits nécessaires pour cette action ou le format PDF de maximum 3 Mo n'est pas respecté.");
				 loaderHide("facture");
			  });

        	}

        });