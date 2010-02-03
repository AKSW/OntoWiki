function username_check(username_obj){
	//alert(username_obj.value);
	var username = username_obj.value;
	if(username == ''){
		alert('Please enter your username');
		username_obj.focus();
		username_obj.select();
		return false;
	}
	var reg   = /[^0-9a-z_]/ig; 
	if(username.match(reg) != null){
		alert('Username contains only numbers, characters, or symbol \"_\" ');
		username_obj.focus();
		username_obj.select();
		//alert("aaaaaaaaaaaa");
		return false;
	}
	return true;
	
}


function password_check(password_again){
	var password_first = document.getElementById('password');
	var password_f = password_first.value;
	var password_a = password_again.value;
	//alert(password_f);
	//alert(password_a);
	/*if(password_a == ''){
		alert('please enter your password!');
		return flase;
	}*/
	if(password_f === password_a){
		//alert("passwords are the same");
		return true;
	}
	else{
		alert('The repeated password is different from the first one!');
		password_first.focus();
		password_first.select();
		return false;
	}
	return true;
}

function email_check(email_obj){
	var email = email_obj.value;
	//alert(email);
	var pattern = /^([a-zA-Z0-9._-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/; 
	var flag = pattern.test(email); 
	if(!flag){ 
	  	alert("Wrong email-address!"); 
	  	email_obj.focus(); 
	  	return false; 
	} 
	else if(email == ''){
		alert("Please enter your Emailaddress!");
		email_obj.focus(); 
		return false;
	}
	else{
		//alert("Right Emailadress!");
		return true;
	}

}

function regist_send(){
	if(!username_check(document.getElementById('username'))){
		//alert('username_check()');
		return false;
	}
	else if(!password_check(document.getElementById('p'))){
		//alert('password_check()');
		return false;
	}
	else if(!email_check(document.getElementById('email'))){
		//alert('email_check()');
		return flase;
	}
	else{
		return true;
	}
	return true;
}