// NOTIFICATIONS
function notification(msg, type) {
	$("#noty_topCenter_layout_container").remove();

	noty({
		layout: 'topCenter',
		theme: 'relax',
		animation: {
        open: 'animated slideInDown', // Animate.css class names
        close: 'animated slideOutUp', // Animate.css class names
        easing: 'swing', // unavailable - no need
        speed: 500 // unavailable - no need
    },
		timeout: 8000,
		text: msg,
		type: type
	});
}

//COMFIRM BOX
var confirmBox = function(message, url) {
	$('.modal').remove();


    $('body').prepend('' +
        '<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="false">' +
            '<div class="modal-dialog" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title" id="deleteModalTitle">Delete</h5>' +
                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<p>'+ message +'</p>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<a href="#" class="btn btn-secondary" data-dismiss="modal">Close</a>' +
                        '<a href="'+ url +'" class="btn btn-danger">Delete</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');

    $('#deleteModal').modal('show')
}

var confirmDeleteReceivedQuestion = function(id) {
    confirmBox('Are you sure you want to delete this question?',''+BASEURL+'/inbox/delete/'+id+'');
}

/* AJAX FUNCTIONS */

function favorite(qid) {
	$.ajax({
		url: BASEURL + "/question/favorite/" + qid,
		type: "GET",
		dataType: "json",
		success: function (data) {
			console.log(data);
			if (data.errorType) {
				notification(data.msg, "error");
			} else {
				$("#question-"+	qid + " .fav-button").addClass("active").attr("onclick","deleteFavorite("+qid+");");
                notification(data.msg, "success");
            }
		},
		error: function () {
			console.log("favorite Error");
		}
	});
}

function deleteFavorite(qid) {
	$.ajax({
		url: BASEURL + "/question/unfavorite/" + qid,
		type: "GET",
		dataType: "json",
		success: function (data) {
            console.log(data);
            if (data.errorType) {
                notification(data.msg, "error");
            } else  {
                $("#question-"+	qid + " .fav-button").removeClass("active").attr("onclick","favorite("+qid+");");
                notification(data.msg, "success");
            }
		},
		error: function () {
			console.log("deleteFavorite Error");
		}
	});
}