// returns to the home dashboard, useful if you have drilled down
function returnToDashboard(){
	document.getElementById('dashframe1').src = document.getElementById('dashframe1').src;
}

// changes the input control for the dashboard
function updateProductFamily(value){
	var url = document.getElementById('dashframe1').src;
	
	url = url.replace(/Product_Family=(All|Drink|Food|Non-Consumable)/gi, "Product_Family="+value);
	
	document.getElementById('dashframe1').src = url;        
}
