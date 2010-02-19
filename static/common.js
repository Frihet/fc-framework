function popupShow(id) {
    var box = document.getElementById(id);
    box.style.display="block";
}

function popupHide(id) {
    var box = document.getElementById(id);
    box.style.display="none";
}

function stripe() {
    $('.striped > tbody > tr:odd > td, .striped > tbody > tr:odd > th').addClass('odd');
    $('.striped > tbody > tr:even > td, .striped > tbody > tr:even > th').removeClass('odd');
}

function installCheckFields() {
    var ok = true;
    var fields = ['admin_username','admin_password','dsn'];
    for(var i =0; i<fields.length; i++) {
	if ($("#"+fields[i])[0].value === '') {
	    $("#"+fields[i] + '_error')[0].innerHTML = "No value specified";
	    ok = false;
	}
	else {
	    $("#"+fields[i] + '_error')[0].innerHTML = "";
	}
    }

    if ($('#admin_password')[0].value != $('#admin_password2')[0].value) {
	$('#admin_password2_error')[0].innerHTML = "Passwords don't match";
	ok = false;
    }
    else {
	$('#admin_password2_error')[0].innerHTML = "";
    }
    return ok;
}

function installDbCheck() {
    for (var i=0; i<InstallData.dsn.length; i++) {
	var dsn_name = InstallData.dsn[i];
	var url = 'index.php?action=db_check&dsn_name='+encodeURIComponent(dsn_name)+'&dsn_value=' + encodeURIComponent($('#dsn_'+dsn_name)[0].value);
	$.getJSON(url, null, function(response) {
		$('#dsn_'+response.dsn+'_notification')[0].innerHTML = response.status;
	    }
	    ,'text');
    }
}

