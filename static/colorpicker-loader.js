$(document).ready(function() {
	$('.colorpickerinput').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val("#" + hex);
			$(el).css('background', "#" + hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}

	});
});
