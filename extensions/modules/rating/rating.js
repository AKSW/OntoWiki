$(document).ready(function() {

    var rating_disp = document.getElementsByName('rating');
    var rating_in = document.getElementsByName('rating_input');
    var rating_user = document.getElementsByName('rating_user');
    var submitbutton = document.getElementsByName('submitbutton');

    var tab0 = document.getElementById('change0');
    var tab1 = document.getElementById('change1');
    var tab2 = document.getElementById('change2');
    var tab3 = document.getElementById('change3');
    var tab4 = document.getElementById('change4');
    var tab5 = document.getElementById('change5');

    document.getElementById('rate_text').innerHTML = ' '+ratingValue +' '+'votes'+' ('+count+')';

    if(count != '0'){
        $(rating_disp).rating('select',rating) ;
    }

    $(submitbutton).click(function(){

        if(getCheckedValue(rating_in) != '')
        {
            if(creator == '1')
            {
                newcount = parseInt(count);
                ratingValuenew = (((parseFloat(ratingValue)) * parseInt(count)) - parseFloat(creatorNote+1) + parseFloat(getCheckedValue(rating_in)))/newcount;
            }
            else
            {
                newcount = parseInt(count)+1;
                ratingValuenew = ((parseFloat(ratingValue) * parseInt(count))  + parseFloat(getCheckedValue(rating_in)))/newcount;
            }

            displayValue = Math.round((ratingValuenew * 2) -1);
            $(rating_user).rating('readOnly',false);
            $(rating_user).rating('select',getCheckedValue(rating_in));
            $(rating_user).rating('readOnly',true);

            $(rating_disp).rating('readOnly',false);
            $(rating_disp).rating('select',displayValue);
            $(rating_disp).rating('readOnly',true);

            tab0.style.display = 'none';
            tab1.style.display = 'none';
            tab2.style.display = 'none';

            tab3.style.display = '';
            tab4.style.display = '';
            tab5.style.display = '';

            document.getElementById('rate_text').innerHTML = ' '+  Math.round(ratingValuenew*100)/100 +' '+'votes'+' ('+newcount+')';
        }
    });

    $(rating_disp).rating('readOnly',true);
    $(rating_in).rating('readOnly',false);

    if(creator == 1){
        $(rating_user).rating('readOnly',false);
        $(rating_user).rating('select',creatorNote);
        $(rating_user).rating('readOnly',true);

        tab0.style.display = 'none';
        tab1.style.display = 'none';
        tab2.style.display = 'none';

        tab3.style.display = '';
        tab4.style.display = '';
        tab5.style.display = '';
    }



    $('#changebutton').click(function(){
        tab0.style.display = '';
        tab1.style.display = '';
        tab2.style.display = '';

        tab3.style.display = 'none';
        tab4.style.display = 'none';
        tab5.style.display = 'none';
     
    });

    $('#ratingfield').mouseout(function(){
 
    $('#submit').val(getCheckedValue(rating_in));
 
    });
 
});

function getCheckedValue(radioObj) {
    if(!radioObj)
        return "";
    var radioLength = radioObj.length;
    if(radioLength == undefined)
        if(radioObj.checked)
            return radioObj.value;
        else
            return "";
    for(var i = 0; i < radioLength; i++) {
        if(radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}





