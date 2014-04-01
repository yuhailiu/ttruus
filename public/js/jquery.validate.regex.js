$(function(){
	//validate password(6-22)
	jQuery.validator.addMethod("password", function(value, element) {
		return this.optional(element) || /^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]*$/.test(value);
	});
	//validate the text,allow chinese charactor
	jQuery.validator.addMethod("username", function(value, element) {
		return this.optional(element) || /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9]|[\s])*$/.test(value);
	});
	//validate address allow space and ","
	jQuery.validator.addMethod("address", function(value, element) {
		return this.optional(element) || /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9]|[,]|[，]|[.]|[。]|[:]|[：]|[；]|[\s])*$/.test(value);
	});
	//validate telephone no 
	jQuery.validator.addMethod("telephone", function(value, element) {
		return this.optional(element) || /(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/.test(value);
	});
	//validate password 
	jQuery.validator.addMethod("password", function(value, element) {
		return this.optional(element) || /^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]*$/.test(value);
	});
	//validate website
	jQuery.validator.addMethod("website", function(value, element) {
		return this.optional(element) || /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/.test(value);
	});

});