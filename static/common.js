function popupShow(id) {
    var box = document.getElementById(id);
    //$(box).parent().css({display:"block"});
    box.style.display="block";
}

function popupHide(id) {
    var box = document.getElementById(id);
    //$(box).parent().css({display:"none"});
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


/*************************************************************
 *    DYNIFS - Dynamic IFrame Auto Size v1.0.0
 *
 *    Copyright (C) 2006, Markus (phpMiX)
 *    This script is released under GPL License.
 *    Feel free to use this script (or part of it) wherever you need
 *    it ...but please, give credit to original author. Thank you. :-)
 *    We will also appreciate any links you could give us.
 *    http://www.phpmix.org
 *
 *    Enjoy! ;-)
 *************************************************************/

var dynamicIFrame = {
	// Storage for known IFrames.
  iframes: {},
  // Here we save any previously installed onresize handler.
  oldresize: null,
  // Flag that tell us if we have already installed our onresize handler.
  ready: false,
  // The document dimensions last time onresize was executed.
  dim: [-1,-1],
  // Timer ID used to defer the actual resize action.
  timerID: 0,
  // Obtain the dimensions (width,height) of the given document.
  getDim: function(d) {
		var w=200, h=200, scr_h, off_h;
		if( d.height ) { return [d.width,d.height]; }
		with( d.body ) {
			if( scrollHeight ) { h=scr_h=scrollHeight; w=scrollWidth; }
			if( offsetHeight ) { h=off_h=offsetHeight; w=offsetWidth; }
			if( scr_h && off_h ) h=Math.max(scr_h, off_h);
		}
		return [w,h];
	},
  // This is our window.onresize handler.
  onresize: function() {
		// Invoke any previously installed onresize handler.
		if( typeof this.oldresize == 'function' ) { this.oldresize(); }
		// Check if the document dimensions really changed.
		var dim = this.getDim(document);
		if( this.dim[0] == dim[0] && this.dim[1] == dim[1] ) return;
		// Defer the resize action to prevent endless loop in quirksmode.
		if( this.timerID ) return;
		this.timerID = setTimeout('dynamicIFrame.deferred_resize();', 10);
	},
  // This is where the actual IFrame resize is invoked.
  deferred_resize: function() {
		// Walk the list of known IFrames to see if they need to be resized.
		for( var id in this.iframes ) this.resize(id);
		// Store resulting document dimensions.
		this.dim = this.getDim(document);
		// Clear the timer flag.
		this.timerID = 0;
	},
  // This is invoked when the IFrame is loaded or when the main window is resized.
  resize: function(id) {
		// Browser compatibility check.
		if( !window.frames || !window.frames[id] || !document.getElementById || !document.body )
			return;
		// Get references to the IFrame window and layer.
		var iframe = window.frames[id];
		var div = document.getElementById(id);
		if( !div ) return;
		// Save the IFrame id for later use in our onresize handler.
		if( !this.iframes[id] ) {
			this.iframes[id] = true;
		}
		// Should we inject our onresize event handler?
		if( !this.ready ) {
			this.ready = true;
			this.oldresize = window.onresize;
			window.onresize = new Function('dynamicIFrame.onresize();');
		}
		// This appears to be necessary in MSIE to compute the height
		// when the IFrame'd document is in quirksmode.
		// OTOH, it doesn't seem to break anything in standards mode, so...
		if( document.all ) div.style.height = '0px';
		// Resize the IFrame container.
		var dim = this.getDim(iframe.document);
		div.style.height = (dim[1]+30) + 'px';
	}
};

