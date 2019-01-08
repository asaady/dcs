function getprefix()
{
    var prefix = $("input[name='dcs_prefix']").val();    
    if (prefix !== 'CONFIG') {
        return "";
    } else {
        return "/CONFIG";
    }      
}
function loadset($elist,sdata,pset)
{
    var shtml = '';
    var dname_st;
    var dname_en;
    var cnt = 0;
    $elist.empty();
    $.each(sdata, function(id, val) {
        shtml = shtml + "<tr class=\"active\" st=\""+val.class+"\" id=\""+id+"\">";
        dname_st = "";
        dname_en = "";
        if (val.class == 'erased')
        {
            dname_st = "<del>";
            dname_en = "</del>";
        }    
        $.each(pset, function(pid, pval) {
            if (pval.class == 'hidden') {
                return;
            }
            if (pval.id in val)
            {    
                var dname = val[pval.id]['name'];
                var did = val[pval.id]['id'];
                var type = pval.name_type;
                var cls = 'dcs-text-print';
                if ((type === 'int')||(type === 'float')||(type === 'date')) {
                    cls = 'dcs-number-print';
                }
                shtml = shtml + "<td class=\""+cls+"\" id=\""+pval.id+"\" it=\""+did+"\" vt=\""+pval.type+"\">"+dname_st+dname+dname_en+"</td>";    
            }
            else
            {
                shtml = shtml + "<td class=\""+cls+"\" id=\""+pval.id+"\" it=\"\" vt=\""+pval.type+"\"></td>";    
            }    
        });
        shtml = shtml + "</tr>";
        cnt++;
    });
    $elist.append(shtml);
    return cnt;
}
function onLoadValID(data)
{
    var action = $("input[name='dcs_action']").val();
    var setid = $("input[name='dcs_setid']").val();
    var pset;
    var sdata;
    if ('LDATA' in data) {    
        $.each(data.LDATA, function(id, val) {
            $("input.form-control[id='id']").val(id);
            $.each(data.PLIST, function(pid, pval) {
                cid = pval.id;
                if (cid in val) {    
                    var did = val[cid]['id'];
                    var dname = val[cid]['name'];
                    if (pval.type=='text') {
                        $("textarea.form-control[id="+cid+"]").val(dname);
                    } else {    
                        if (did !== '') {    
                            $("input.form-control[id=name_"+cid+"]").val(dname);
                            dname = did;
                        }    
                        $("input.form-control[id="+cid+"]").val(dname);
                    }    
                }    
            });
        });
    }
    if ('SDATA' in data) {   
        sdata = data.SDATA;
        if (setid === '') {
            $elist = $("tbody#entitylist");
            pset = data.PSET;
        } else {
            $elist = $("tbody#entitylist",$("div#"+setid));
            pset = data.SETS[setid];
        }    
        loadset($elist,sdata,pset);
    }    
}
$(document).ready(function() 
{ 
    var itemid = $("input[name='dcs_itemid']").val(); 
    var mode = $("input[name='dcs_mode']").val();
    if ((mode !== 'AUTH')&&(itemid !== '')) {
        $("input[name='dcs_command']").val('load'); 
        var $data = $('.ajax').serializeArray();
        $.ajax({
          url: getprefix()+'/ajax/'+itemid,
          type: 'get',
          dataType: 'json',
          data: $data,
          success: onLoadValID,
          error: function(data) {console.log(data);}
          }
        );
    }
});
