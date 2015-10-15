function getidorder(text){
    var regex = /id_order\=(\d+)\&/gi;
    match = regex.exec(text);
    return match[1];
}


function filesmanager(id){
    
    var text = $("#menu").html();
    var regex = /orderfilestab\&amp\;token\=(\w+)\"\>/gi;
    match = regex.exec(text);
    
    /*
    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    for (var i = 0; i < parts.length; i++) {
        var temp = parts[i].split("=");
        $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
    }
    */


    $('.table tr:last-child td:last-child').append('<form id="filesmanager'+id+'" action="?controller=orderfilestab&token='+match[1]+'" method="POST"><input type="hidden" name="filemanager" value="1"/><input type="hidden" name="oid" value="'+id+'" /></form>');
    $('#filesmanager'+id+'').submit();
    
}

$(document).ready(function() {
    $('.table tr:not(:first) td:last-child').append('<span class="filesicon" onclick="filesmanager(getidorder($(this).parent().html()));"><span>');
});