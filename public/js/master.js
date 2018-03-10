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
var confirmBox = function(message, confirmTitle, url, onClick) {
	$('.modal').remove();


    $('body').prepend('' +
        '<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalTitle" aria-hidden="false">' +
            '<div class="modal-dialog" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title" id="confirmModalTitle">'+ confirmTitle +'</h5>' +
                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<p>'+ message +'</p>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<a href="#" class="btn btn-secondary" data-dismiss="modal">Close</a>' +
                        '<a href="'+ url +'" class="btn btn-danger" '+ onClick +'>'+ confirmTitle +'</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');

    $('#confirmModal').modal('show');
}

var confirmDeleteReceivedQuestion = function(id) {
    confirmBox('Are you sure you want to delete this question?', 'Delete', BASEURL+'/inbox/delete/'+id+'', null);
}
var confirmBlockUser = function(id, q) {
    if (q)
        var onClick = 'onClick="blockUser('+id+', true)"';
    else
        var onClick = 'onClick="blockUser('+id+', false)"';
    confirmBox('Are you sure you want to block this user?', 'Block', '#', onClick);
}
var confirmUnblockUser = function (id) {
    confirmBox('Are you sure you want to unblock this user?', 'Unblock', '#', 'onClick="unblockUser('+id+')"');
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

function blockUser(id, q) {
    if (q)
        var tag = 'q';
    else
        var tag = 'u';

    $.ajax({
        url: BASEURL + "/user/block/"+tag+"/" + id,
        type: "GET",
        dataType: "json",
        success: function (data) {
            console.log(data);
            $('#confirmModal').modal('hide');

            if (data.errorType) {
                notification(data.msg, "error");
            } else {
                notification(data.msg, "success");
                if (!q) location.reload();
            }
        },
        error: function () {
            console.log("blockUser Error");
        }
    });
}

function unblockUser(id) {
    $.ajax({
        url: BASEURL + "/user/unblock/" + id,
        type: "GET",
        dataType: "json",
        success: function (data) {
            console.log(data);
            $('#confirmModal').modal('hide');
            
            if (data.errorType) {
                notification(data.msg, "error");
            } else {
                notification(data.msg, "success");
                location.reload();
            }
        },
        error: function () {
            console.log("blockUser Error");
        }
    });
}