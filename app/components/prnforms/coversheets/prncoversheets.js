function onLoadValID(data)
{
    if ('SDATA' in data)
    {    
        for(var cid in data['PLIST'])
        {
            if (cid in data['SDATA'])
            {    
                var did = data['SDATA'][cid]['id'];
                var dname = data['SDATA'][cid]['name'];
                if (did!='')
                {    
                    $("input.form-control[id=name_"+cid+"]").val(dname);
                    dname = did;
                }    
                $("input.form-control[id="+cid+"]").val(dname);
            }    
        }
    }    
    if ('LDATA' in data)
    {    
        $("tbody#entitylist tr").remove();
        for(var id in data['LDATA'])
        {
            $("tbody#entitylist").append("<tr class=\"active\" id=\""+id+"\">");
            for(var cid in data['PSET'])
            {
                cls = data['PSET'][cid]['class'];
                if (cls!='hidden')
                {
                    cls='';
                }    
                if (cid in data['LDATA'][id])
                {    
                    var dname = data['LDATA'][id][cid]['name'];
                    var did = data['LDATA'][id][cid]['id'];
                    $("tr#"+id).append("<td id=\""+cid+"\" it=\""+did+"\" vt=\""+data['PSET'][cid]['type']+"\" "+cls+">"+dname+"</td>");    
                }
                else
                {
                    $("tr#"+id).append("<td id=\""+cid+"\" it=\"\" vt=\""+data['PSET'][cid]['type']+" "+cls+"\"></td>");    
                }    
            }
            $("tbody#entitylist").append("</tr>");
        }
    }    
    actionlist(data['actionlist']);
}
$(document).ready(function() 
{ 
    $("input[name='command']").val('load'); 
    $data = $('.row :input').serializeArray();
    $.ajax({
      url: '/app/components/prnforms/coversheets/prncoversheets_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
});
