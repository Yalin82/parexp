/**
 * Created with JetBrains PhpStorm.
 * User: vargus
 * Date: 03.03.13
 * Time: 17:42
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function(){
    $('.accordion .head').click(function() {
        $(this).next().toggle('slow');
        return false;
    }).next().hide();
});

$("#cert_1").fancybox({
    helpers: {
        title : {
            type : 'float'
        }
    }
});
$("#error_block_button").fancybox();
$("#success_block_button").fancybox();

function submit_contact_form()
{
    var contact_form =$("form[name='contact_form']")[0];
    var formData = new FormData(contact_form);
    $.ajax({
        url:    "http://parexp/contacts.php",
        type:   "POST",
        dataType:'json',
            xhr: function() {  // custom xhr
            myXhr = $.ajaxSettings.xhr();
            if(myXhr.upload){ // check if upload property exists
//                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
            }
            return myXhr;
        },
        success: function(response){
            if(response.status == 'ok')
            {
                $("#success_block_button").click();
	            setTimeout(function(){document.location = 'http://parexp'},2000)
                $('#name').removeClass('error');
                $('#phone').removeClass('error');
                $('#organization').removeClass('error');
                $('#captcha').removeClass('error');
                $('#message').removeClass('error');
            }
            if(response.status == 'error')
            {
                $("#error_block_button").click();
                document.getElementById('captcha_img').src='captcha.php?'+Math.random();
                $('#captcha').addClass('error');
                for (var k  in response.errors)
                {
                    $('#'+response.errors[k]).addClass('error');
                }
            }
        },
        // Form data
        data: formData,
        //Options to tell JQuery not to process data or worry about content-type
        cache: false,
        contentType: false,
        processData: false
    });
}