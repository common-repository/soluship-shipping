jQuery(document).ready(function(){


   jQuery(".fs_box1").change(function(){
        jQuery(this).find("option:selected").each(function(){
            if(jQuery(this).attr("value")=="nil"){

               jQuery('.minamt').attr('disabled', true);
               jQuery('.minwigt').attr('disabled', true);
                //jQuery(".minamt").hide();
                 //jQuery(".minwigt").hide();
                //jQuery(".red").show();
                
            }
            else if(jQuery(this).attr("value")=="min_amount"){
                //jQuery(".minwigt").hide();
               // jQuery(".minamt").show();
                 jQuery('.minamt').attr('disabled', false);
               jQuery('.minwigt').attr('disabled', true);
            }
            else if(jQuery(this).attr("value")=="min_weight"){
                //jQuery(".minamt").hide();
                //jQuery(".minwigt").show();
                 jQuery('.minamt').attr('disabled', true);
               jQuery('.minwigt').attr('disabled', false);
            }
             
        });
    }).change();



 jQuery(".markupdown").change(function(){
        jQuery(this).find("option:selected").each(function(){
            if(jQuery(this).attr("value")=="nil"){

               jQuery('.markuptype').attr('disabled', true);
               jQuery('.mvalue').attr('disabled', true);
                //jQuery(".minamt").hide();
                 //jQuery(".minwigt").hide();
                //jQuery(".red").show();
                
            }else{
               jQuery('.markuptype').attr('disabled', false);
               //jQuery('.mvalue').attr('disabled', true);

            }
            /*else if(jQuery(this).attr("value")=="min_amount"){
                //jQuery(".minwigt").hide();
               // jQuery(".minamt").show();
                 jQuery('.minamt').attr('disabled', false);
               jQuery('.minwigt').attr('disabled', true);
            }
            else if(jQuery(this).attr("value")=="min_weight"){
                //jQuery(".minamt").hide();
                //jQuery(".minwigt").show();
                 jQuery('.minamt').attr('disabled', true);
               jQuery('.minwigt').attr('disabled', false);
            }*/
        });
    }).change();



 jQuery(".markuptype").change(function(){
        jQuery(this).find("option:selected").each(function(){
            if(jQuery(this).attr("value")=="nil"){

               
               jQuery('.mvalue').attr('disabled', true);
                //jQuery(".minamt").hide();
                 //jQuery(".minwigt").hide();
                //jQuery(".red").show();
                
            }
             else  {
              jQuery('.mvalue').attr('disabled', false);

            }
        });
    }).change();


 jQuery(".defaultdim").change(function(){
        jQuery(this).find("option:selected").each(function(){
            if(jQuery(this).attr("value")=="nil"){

               
               jQuery('.default_package_dim_class').attr('disabled', true);
                //jQuery(".minamt").hide();
                 //jQuery(".minwigt").hide();
                //jQuery(".red").show();
                
            }
             else  {
              jQuery('.default_package_dim_class').attr('disabled', false);

            }
        });
    }).change();


  jQuery(".pk_box1").change(function(){
        jQuery(this).find("option:selected").each(function(){
            if(jQuery(this).attr("value")=="pack_slice"){

               
               jQuery('.maxvalue').attr('disabled', false);
                //jQuery(".minamt").hide();
                 //jQuery(".minwigt").hide();
                //jQuery(".red").show();
                
            }
             else  {
              jQuery('.maxvalue').attr('disabled', true);

            }
     
        });
    }).change(); 



});
 


 jQuery( "#woocommerce_soluship_shipping_method_country" ).change(function() {
			 var ws="#woocommerce_soluship_shipping_method_country";
 
		   var cc=jQuery("#woocommerce_soluship_shipping_method_country option:selected").val();
			   
			   var state=jQuery("#woocommerce_soluship_shipping_method_sender_state").val();

			 // var dd="{'countrycode':'"+cc+"'}";
			  var fruit = 'Banana';
     // alert(fruit);
    // This does the ajax request
   var data = {
			action: 'test_response',
              country: cc
		};
		// the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
	 	jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
			 //alert(response);
var obj=jQuery.parseJSON(response);
	 
	var opt="";
	var st="";
			   for (var key in obj) {
  if (obj.hasOwnProperty(key)) {
    var val = obj[key];

if(key=="default"){
	st=val;
}else{
    opt+="<option value="+key+">"+val+"</option>";
     }
  }
}

 
 
jQuery("#woocommerce_soluship_shipping_method_sender_state").html(opt);
 jQuery("#woocommerce_soluship_shipping_method_sender_state").val(st);

	 	});
 

}).change(); 

jQuery( "form" ).submit(function( event ) { 
var ws="#woocommerce_soluship_shipping_method_";
var domain=jQuery(ws+"domain").val();
var domainerr="";
var i=0;
 var re =  /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
//var re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;
if (!re.test(domain)) { 
   
   // return false;
   i++;
   if(domain==""){
   		jQuery("#domain").html(" DOMAIN Required. <br>");
   }else{
   	jQuery("#domain").html("INVALID DOMAIN <br>");
   }
  jQuery("#woocommerce_soluship_shipping_method_domain").css('border-color', 'red');
 
}
 
var apiusername=jQuery(ws+"apiusername").val();

if(apiusername==""){
	jQuery("#apiusername").html(" API USERNAME  Required. <br>");
	jQuery(ws+"apiusername").css('border-color', 'red');
	i++;
}




var apipassword=jQuery(ws+"apipassword").val();
if(apipassword==""){
	jQuery("#apipassword").html(" API PASSWORD  Required. <br>");
	jQuery(ws+"apipassword").css('border-color', 'red');
	i++;
}


var origin=jQuery(ws+"origin").val();
if(origin==""){
	jQuery("#origin").html(" ORIGIN ZIPCODE  Required. <br>");
	jQuery(ws+"origin").css('border-color', 'red');
	i++;
}

var sender_company_name=jQuery(ws+"sender_company_name").val();
if(sender_company_name==""){
	jQuery("#sender_company_name").html(" SENDER COMPANY NAME   Required. <br>");
	jQuery(ws+"sender_company_name").css('border-color', 'red');
	i++;
}

var sender_contact_phone=jQuery("#woocommerce_soluship_shipping_method_sender_contact_phone").val();
 
 if(sender_contact_phone==""){
i++;
	jQuery("#sender_contact_phone").html(" SENDER CONTACT NUMBER   Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_contact_phone").css('border-color', 'red');
} 


var  sender_address_line1=jQuery("#woocommerce_soluship_shipping_method_sender_address_line1").val();

 if(sender_address_line1==""){
i++;
	jQuery("#sender_address_line1").html(" SENDER ADDRESS LINE1   Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_address_line1").css('border-color', 'red');
} 
 

var  sender_contact_email=jQuery("#woocommerce_soluship_shipping_method_sender_contact_email").val();

 if(sender_contact_email==""){
i++;
	jQuery("#sender_contact_email").html(" SENDER CONTACT EMAIL   Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_contact_email").css('border-color', 'red');
}else{

	 var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

	 if(!(re.test(sender_contact_email))){
i++;
	jQuery("#sender_contact_email").html(" SENDER CONTACT EMAIL   is not valid Email. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_contact_email").css('border-color', 'red');

	 } 
}


var  origin=jQuery("#woocommerce_soluship_shipping_method_origin").val();

 if(sender_address_line1==""){
i++;
	jQuery("#origin").html(" ORIGIN ZIPCODE Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_origin").css('border-color', 'red');
} 
 
var  sender_city=jQuery("#woocommerce_soluship_shipping_method_sender_city").val();

 if(sender_city==""){
i++;
	jQuery("#sender_city").html(" SENDER CITY  Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_city").css('border-color', 'red');
} 

  
  var state=jQuery("#woocommerce_soluship_shipping_method_sender_state").val();



if(state==null){
	i++;
	jQuery("#sender_state").html(" SENDER STATE  Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_sender_state").css('border-color', 'red');

}



var pack=jQuery("#woocommerce_soluship_shipping_method_packaging_soluship option:selected").val();
if(pack=="pack_slice"){

 
var max_weight=jQuery("#woocommerce_soluship_shipping_method_max_weight").val();

 if(max_weight==""){
i++;
	jQuery("#max_weight").html(" MAX PACKAGE WEIGHT   Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_max_weight").css('border-color', 'red');
}else{

	if(isNaN(max_weight)){
		i++;
	jQuery("#max_weight").html(" MAX PACKAGE WEIGHT   not valid Weight. <br>");
	jQuery("#woocommerce_soluship_shipping_method_max_weight").css('border-color', 'red');

	}else if(max_weight<0 || max_weight==0){
		i++;
	jQuery("#max_weight").html(" MAX PACKAGE WEIGHT   not valid. <br>");
	jQuery("#woocommerce_soluship_shipping_method_max_weight").css('border-color', 'red');

	}else{
		jQuery("#max_weight").html("");
	    jQuery("#woocommerce_soluship_shipping_method_max_weight").css('border-color', '');
	}
}

}else{
	jQuery("#max_weight").html("");
	jQuery("#woocommerce_soluship_shipping_method_max_weight").css('border-color', '');
}


var free_ship_soluship=jQuery("#woocommerce_soluship_shipping_method_free_ship_soluship option:selected").val();



if(free_ship_soluship !="nil"){


var min_amount_free_ship=jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").val();
var min_weight_free_ship=jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").val();

if(free_ship_soluship=="min_amount"){

 if(min_amount_free_ship==""){

  
i++;
	jQuery("#min_amount_free_ship").html(" MINIMUM ORDER AMOUNT  Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', 'red');
	jQuery("#min_weight_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', '');
	
}else{


	if(isNaN(min_amount_free_ship)){
		i++;
		  
	jQuery("#min_amount_free_ship").html(" MINIMUM ORDER AMOUNT  not valid Amount. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', 'red');
	jQuery("#min_weight_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', '');
	
	}else if(min_amount_free_ship<0 || min_amount_free_ship==0){
		i++;
 
	jQuery("#min_amount_free_ship").html(" MINIMUM ORDER AMOUNT   not valid Amount. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', 'red');
		jQuery("#min_weight_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', '');
	
	}else{
		 
		jQuery("#min_amount_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', '');
	}
} 


}else if(free_ship_soluship=="min_weight"){

 if(min_weight_free_ship==""){
i++;
	jQuery("#min_weight_free_ship").html(" MINIMUM ORDER WEIGHT  Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', 'red');
	jQuery("#min_amount_free_ship").html("");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', '');
	

}else{


	if(isNaN(min_weight_free_ship)){
		i++;
	jQuery("#min_weight_free_ship").html(" MINIMUM WEIGHT    not valid. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', 'red');
	jQuery("#min_amount_free_ship").html("");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', '');
	


	}else if(min_weight_free_ship<0 || min_weight_free_ship==0){
		i++;
	jQuery("#min_weight_free_ship").html(" MINIMUM WEIGHT   not valid. <br>");
	jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', 'red');
	jQuery("#min_amount_free_ship").html("");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', '');
	
	}else{
		jQuery("#min_weight_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', '');
	}
} 




}
}else{
	jQuery("#min_weight_free_ship").html("");
	    jQuery("#woocommerce_soluship_shipping_method_min_weight_free_ship").css('border-color', '');
	jQuery("#min_amount_free_ship").html("");
	jQuery("#woocommerce_soluship_shipping_method_min_amount_free_ship").css('border-color', '');
	
}


var markup_down=jQuery("#woocommerce_soluship_shipping_method_markup_down option:selected").val();

var markup_down_type=jQuery("#woocommerce_soluship_shipping_method_markup_down_type  option:selected").val();

var markup_value=jQuery("#woocommerce_soluship_shipping_method_markup_value").val();

if(markup_down!="nil"){

if(markup_down_type=="nil"){

i++;
		jQuery("#markup_down_type").html(" MARKUP VALUE    Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_markup_down_type").css('border-color', 'red');
	 

}else{

	if(markup_value==""){
		//nil
		i++;
		jQuery("#markup_value").html(" MARKUP VALUE    Required. <br>");
	jQuery("#woocommerce_soluship_shipping_method_markup_value").css('border-color', 'red');
	 
	}  if(isNaN(markup_value)){
		i++;
	jQuery("#markup_value").html(" MARKUP VALUE    not valid. <br>");
	jQuery("#woocommerce_soluship_shipping_method_markup_value").css('border-color', 'red');
	 

	}else if(markup_value<0 || markup_value==0){
		i++;
	jQuery("#markup_value").html(" MARKUP VALUE    not valid. <br>");
	jQuery("#woocommerce_soluship_shipping_method_markup_value").css('border-color', 'red');
 
	}else{
		jQuery("#markup_value").html("");
	    jQuery("#woocommerce_soluship_shipping_method_markup_value").css('border-color', '');
	    jQuery("#markup_down_type").html("");
	    jQuery("#woocommerce_soluship_shipping_method_markup_down_type").css('border-color', '');
	
	}

	}
} else{

}






if(i>0){
	jQuery("html, body").animate({ scrollTop: 0 }, "slow");
	event.preventDefault();	

}
 

}); 