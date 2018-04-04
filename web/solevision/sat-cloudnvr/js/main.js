/*
 *  Automatic bookmarks
 */

function bMarks(strWhichTag, sBookMarkNode){

    var cAnchorCount = 0;
    var oList = $('<ul id="bookmarkList">');
    
    //need to get this to work...
    $("div:not([id=header]) " + strWhichTag).each(function(){

        $(this).html("<a name='bookmark" + cAnchorCount + "'></a>" + $(this).html());
        oList.append($("<li><a href='#bookmark" + cAnchorCount++ + "'> " + $(this).text() + "</a></li>"));
    });
    
     
    $('#' + sBookMarkNode).append(oList);
};

/*
 * FAQ - Frequently Asked Questions loading json
 **/
$('document').ready(function(){
    
    
   $.getJSON("js/faq.json",{
    
    },function(fields){
        var faqList = $("<dl id='faqList'>");
        $.each(fields, function(key, fields){
            faqList.append("<dt>" +fields.title+ "</dt>")
            for(var i = 0; i < fields.faqlist.length; i++){
                //console.log(fields.faqlist[i]);
                faqList.append("<dd> Q:" +fields.faqlist[i].Question +"<br/> A: "+ fields.faqlist[i].Answer +"</dd>");
           }

            });
         $('#faq').append(faqList);
    });

});
