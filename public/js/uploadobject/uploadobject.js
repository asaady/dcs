const AJAX_URL = getprefix()+'/ajax/';
function get_input_fieldid($curinp)
{
    var curname = $curinp.attr('name');
    if ((curname.indexOf('name_') + 1) > 0) {
        return $('div.form-group').find('input#'+curname.substring(5)).val();
    }
    return '';
}
function set_input_fieldid($curinp, curvalue)
{
    var curname = $curinp.attr('name');
    var $curdiv = $curinp.parent('div');
    var inpid = curname;
    var $curinpid = $curinp;
    if ((curname.indexOf('name_') + 1) > 0) {
        inpid = curname.substring(5);
        $curinpid = $curdiv.find('input#'+inpid);
    }
    $curinpid.val(curvalue);
    return $curinpid.attr('id');
}
function actionlist(data)
{
    var $navtab = $('#actionlist');
    $navtab.empty();
    var a_html = '';
    for(var $item in data)
    {    
      var v_html = "<i class=\"material-icons\">"+data[$item]['icon']+"</i>";
      if (data[$item]['icon']=='')  
      {
        v_html = data[$item]['synonym'];
      }
      a_html = a_html+"<li><a class=\"btn\" id=\""+data[$item]['name']+"\">"+v_html+"</a></li>";
    }
    if (a_html !== '') {
        $navtab.append(a_html); 
    }
}
function navlist(data)
{
    var $navtab = $('ol.breadcrumb');
    $navtab.empty();
    var a_html = "<li><a href=\"/\"><i class=\"material-icons\">home</i></a></li>";
    $.each(data, function(key, val) 
    {
      a_html = a_html+"<li><a href=\""+getprefix()+"/"+val.id+"\">"+val.name+"</a></li>";
    });
    $navtab.append(a_html); 
}

function onchoice(data)
{
    var $curinp = $("div#ivalue input.form-control");
    $curinp.val(data['name']); 
    $curinp.attr('it',data['id']); 
}
function onGetMdData(data)
{
    $("input[name='dcs_param_propid']").val(''); 
    $("input[name='dcs_param_id']").val(''); 
    $("input[name='dcs_param_val']").val(''); 
    $("input[name='dcs_param_type']").val(''); 
    $.each(data.items, function(key, val) {
        if (val.id) {    
            $("input#"+val.id).val(val.name);
        }
    });    
}

function onGetData(data)
{
    $("input[name='dcs_param_propid']").val(''); 
    $("input[name='dcs_param_id']").val(''); 
    $("input[name='dcs_param_val']").val(''); 
    $("input[name='dcs_param_type']").val(''); 
    $.each(data, function(key, val) {
        if (val.id) {    
            $("input#"+key).val(val.id);
            $("input#name_"+key).val(val.name);
        } else {
            $("input#"+key).val(val.name);
        }
    });    
}
    
function getprefix()
{
    var prefix = $("input[name='dcs_prefix']").val();    
    return "/"+prefix;
}
function onloadlist(data)
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    $mh.empty();
    $mt.empty();
    $("input[name='dcs_curid']").val('');
    $("input[name='dcs_param_propid']").val(''); 
    $("input[name='dcs_param_id']").val('');
    $("input[name='dcs_param_val']").val('');
    $("input[name='dcs_param_type']").val('');
    if (!Object.keys(data).length) {
        return;
    }
    shtml = '<tr>';
    $.each(data.PSET, function(cid, pval) {
        shtml = shtml + "<th class=\""+pval.class+"\" id=\""+cid+"\">"+pval.synonym+"</th>";
    });
    shtml = shtml + '</tr>';
    $mh.append(shtml);
    if ('SDATA' in data) {    
        loadset($mt,data.SDATA,data.PSET);
    }    
    $(".modal-title").text('Выбор из списка');
    $('body').one('click', '#dcsModalOK', function () {
        $('#dcsModal').modal('hide');
    });
    $('#dcsModal').modal('show');
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
            if (pval.id in val)
            {    
                var dname = val[pval.id]['name'];
                var did = val[pval.id]['id'];
                shtml = shtml + "<td class=\""+pval.class+"\" id=\""+pval.id+"\" it=\""+did+"\" vt=\""+pval.name_type+"\">"+dname_st+dname+dname_en+"</td>";    
            }
            else
            {
                shtml = shtml + "<td class=\""+pval.class+"\" id=\""+pval.id+"\" it=\"\" vt=\""+pval.name_type+"\"></td>";    
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
    if ('redirect' in data) {    
        location.href = data['redirect'];        
    }           
    var action = $("input[name='dcs_action']").val();
    var propid = $("input[name='dcs_propid']").val();
    var arr_type = ['id','cid','mdid','propid'];
    var pset;
    var sdata;
    if ('SETID' in data) {    
        $("input[name='dcs_setid']").val(data['SETID']);
    }
    if ('LDATA' in data) {    
        $.each(data.LDATA, function(id, val) {
            $("input.form-control[id='id']").val(id);
            $.each(data.PLIST, function(pid, pval) {
                cid = pval.id;
                if (cid in val) {    
                    var did = val[cid]['id'];
                    var dname = val[cid]['name'];
                    if (pval.name_type=='text') {
                        $("textarea.form-control[id="+cid+"]").val(dname);
                    } else {    
                        if ('name_valmditem' in pval) {
                            if (pval.name_valmditem == 'Sets') {
                                $("div.tab-pane[id="+cid+"]").attr('it',did);
                            }
                        }
                        if (did !== '') {    
                            $("input.form-control[id=name_"+cid+"]").val(dname);
                            dname = did;
                        }    
                        $("input.form-control[id="+cid+"]").val(dname);
                    }    
                }    
                if (action === 'VIEW') {
                    $("input.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    if (arr_type.indexOf(pval.name_type) >= 0) {    
                        $("input.form-control[id=name_"+cid+"]").attr('readonly', 'readonly');
                    } else if (pval.name_type == 'text') {    
                        $("textarea.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    }    
                }    
            });
        });
    }
    if ('SDATA' in data) {   
        if (propid === '') {
            $elist = $("tbody.entitylist");
            sdata = data.SDATA;
            pset = data.PSET;
            loadset($elist,sdata,pset);
        } else {
            if ('SETS' in data) {   
                $elist = $("tbody.entitylist",$("div#"+propid));
                sdata = data.SDATA[propid];
                pset = data.SETS[propid];
                loadset($elist,sdata,pset);
            }    
        }    
    }    
    actionlist(data['actionlist']);
    navlist(data['navlist']);
}
function onLoadGetData(data) {
    var $curinp = $(":input.form-control[st='info']");
    var curid = $curinp.attr('id');
    var exd = 0;
    var shtml = '';
    var $curlist = $("#"+curid+"~.types_list");
    $("input[name='dcs_curid']").val('');
    $("input[name='dcs_param_id']").val('');
    $("input[name='dcs_param_val']").val('');
    $("input[name='dcs_param_type']").val('');
    $curlist.empty();
    $.each(data, function(key, val) 
    {
        if (val.id)
        {    
            shtml = shtml + '<li id='+val.id+' class="active">'+val.name+'</li>';
        }
    });
    $curlist.append(shtml);        
    $curlist.slideToggle('fast');
};
$('body').keyup(function(eventObject) { 
    if (eventObject.which==27) { 
        $(".types_list").slideUp('fast');
    }
});
$('input.form-control').keyup(function(eventObject) { 
    
    var action = $("input[name='dcs_action']").val();
    if (action === 'VIEW')
    {
        return;
    }
    var itemid = $("input[name='dcs_itemid']").val();
    var itype = $(this).attr("vt");
    var name = $(this).val();
    var it = $(this).attr("it");
    var curid = this.id;
    var $curinp = $(":input.form-control[st='info']");
    var arr_type = ['id','cid','mdid','propid'];
    if ($curinp[0] != $(this)[0])
    {
        $curinp.attr('st','active');
        $(this).attr('st','info');
    }    
    if (eventObject.which==27) { 
        $("#"+curid+"~.types_list").slideUp('fast');
    } else {
        if (it == '') {
            it = $("input[vt='mdid'][type='hidden']").val();
        }    
        if (curid == 'name_valmdid') {    
            it = $("input#valmdid").val();
            itype = 'mdid';
        }
        if (arr_type.indexOf(itype)>=0) {
            $("#"+curid+"~.types_list").slideUp('fast'); 
            if (name.length>1) {
                $("input[name='dcs_command']").val('find');
                if (itype == 'propid') {    
                    $("input[name='dcs_command']").val('prop_find');
                }
                $("input[name='dcs_curid']").val(curid);
                $("input[name='dcs_param_id']").val(it);
                $("input[name='dcs_param_val']").val(name);
                $("input[name='dcs_param_type']").val(itype);
                $("input[name='dcs_mode']").val('ajax'); 
                var itemid = $("input[name='dcs_itemid']").val(); 
                $data = $('.ajax').serializeArray();
                $.getJSON(
                    AJAX_URL+itemid,
                    $data,
                    onLoadGetData
                );
            } else {
                if (name.length === 0) {
                    set_input_fieldid($curinp,'');
                }    
            }    
        }	
    }  
}); 
$('.row input').dblclick(function () {
    var curinp = $(".row input[st='info']");
    if (curinp!=this)
    {
        $(curinp).attr('st','active');
        $(this).attr('st','info');
    }    
});
$('input#type').dblclick(function() { 
    $(".types_list").slideUp('fast'); 
    $("#type~.types_list").slideToggle('fast');
}); 
$('input.form-control[it=bool]').dblclick(function() { 
    $action = $("input[name='dcs_action']").val();
    var curid = this.id;
    $(".types_list").slideUp('fast'); 
    $("#"+curid+"~.types_list").slideToggle('fast');
});
$('body').on('dblclick','#entitylist tr',function () 
{
    var $itemid = $("input[name='dcs_itemid']").val();
    var $action = $("input[name='dcs_action']").val();
    var $curcol = $('th.info').attr('id');
    var href='';
});


$('body').on('dblclick','#modallist tr',function (e) 
{
    e.preventDefault();
    var itemid = $("input[name='dcs_itemid']").val(); 
    $("input[name='dcs_curid']").val(this.id); 
    $("input[name='dcs_command']").val('choice'); 
    $("input[name='dcs_mode']").val('ajax'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: AJAX_URL+itemid,
      type: 'post',
      dataType: 'json',
      data: $data,
        success: onchoice
    });  
    $('#tzModal').modal('hide');
});


$('.row input').click(function () {
    $("input~.types_list").slideUp('fast');
    var curinp = $(".row input[st='info']");
    $(curinp).attr('st','active');
    $(this).attr('st','info');
});


$('body').on('click', 'ul.types_list li', function(){
    var tx = $(this).html(); 
    var lid = $(this).attr('id'); 
    var curdiv = $(this).parent().parent();
    var curinp = curdiv.find("input[type='text']");
    var curname = curinp.attr('name');
    var curtype = curinp.attr('it');
    if((curname.indexOf('name_') + 1)>0)
    {
        curid = curname.substring(5);
        curinpid = $('div.form-group').find('input#'+curid);
        curinpid.val(lid); 
    }    
    curinp.val(tx); 
    $(".types_list").slideUp('fast'); 
    if ((curname=='name_propid')||(curname=='name_valmdid'))
    {
        var itemid = $("input[name='dcs_itemid']").val(); 
        $("input[name='dcs_mode']").val('ajax'); 
        var data = $('.ajax').serializeArray();
        $.getJSON(
             AJAX_URL+itemid,
             data,
             onGetData
         );
    }    
});
$('body').on('click','#entitylist tr',function () 
{
  var curid = this.id;
  $('tr.info').attr("class","active");
  $('#'+curid).attr("class","info");
});
$('body').on('click','#entitylist td',function () 
{
    var curcol = this.id;
    $('th.info').attr("class","active");
    $('th#'+curcol).attr("class","info");
    $('div.ivalue-block').hide();
});

$('body').on('click', '#filter', function (e) 
{
    var curid = $('tr.info').attr('id');
    var curcol = $('th.info').attr('id');
    e.preventDefault();
    var $data;
    var $el_cur  = $("tr#"+curid).find("td#"+curcol);
    var $el_fval = $("input[name='dcs_param_val']");
    var $filter_val=$el_fval.val();
    var curval='';
    var $fval  = $el_cur.html();
    var $fid   = $el_cur.attr("it");
    $("input[name='dcs_param_id']").val(curcol); 
    if ($fid!='')
    {
        $el_fval.val($fid); 
        curval = $fid;
    }
    else 
    {
        $el_fval.val($fval); 
        curval = $fval;
    }
    if (curval!="") 
    {
        if ($filter_val!=curval) 
        {
            $el_fval.val(curval); 
        }
        else 
        {
            $el_fval.val(''); 
        }    
    }
    else 
    {
        if ($filter_val!="") 
        {
            $el_fval.val(''); 
        }    
    }    
    curval = $el_fval.val();
    var itemid = $("input[name='dcs_itemid']").val(); 
    $("input[name='dcs_command']").val('load'); 
    var data = $('.ajax').serializeArray();
    $.ajax({
      url: AJAX_URL+itemid,
      type: 'post',
      dataType: 'json',
      data: data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
});
$('body').on('click', '#exec', function (e) 
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    $("input[name='dcs_command']").val('import'); 
    e.preventDefault();
    var $data;
    var filename = $("input[name='filename']").val();
    var curid = $("input[name='target_mdid']").val();
    var formdata;
    
    if (filename !== '')
    {
        var $csvs = $('input#filename'),
            formdata = new FormData,
            validationErrors = validateFiles({
                $files: $csvs,
                maxSize: 2 * 1024 * 1024,
                types: ['text/csv','text/txt']
            });
            
        // Валидация
        if (validationErrors.length) {
            console.log('client validation errors: ', validationErrors);
            return false;
        }

        // Добавление файлов в formdata
        $csvs.each(function(index, $csv) {
            if ($csv.files.length) {
                formdata.append('csv[]', $csv.files[0]);
            }
        });
        formdata.append('id', curid);
        var itemid = $("input[name='dcs_itemid']").val(); 
        // Отправка на сервер
        $.ajax({
            url: AJAX_URL+itemid,
            data: formdata,
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            success: show_uploadfile
        });
    }   
});

    // Валидация файлов
function validateFiles(options) {
    var result = [],
        file;

    // Перебираем файлы
    options.$files.each(function(index, $file) {
        // Выбран ли файл
        if (!$file.files.length) {
            result.push({index: index, errorCode: 'no_file'});
            // Остальные проверки не имеют смысла, переходим к следующему файлу
            return;
        }

        file = $file.files[0];
        // Проверяем размер
        if (file.size > options.maxSize) {
            result.push({index: index, name: file.name, errorCode: 'big_file'});
        }
        // Проверяем тип файла
        if (options.types.indexOf(file.type) === -1) {
            result.push({index: index, name: file.name, errorCode: 'wrong_type'});
        }
    });

    return result;
}
function show_uploadfile($data)
{
    $("input[name='dcs_command']").val('import'); 
    var itemid = $("input[name='dcs_itemid']").val(); 
    var data = $('.ajax').serializeArray();
    $.ajax({
      url: AJAX_URL+itemid,
      type: 'post',
      dataType: 'json',
      data: data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
}


$('body').on('click', '#build', function (e) 
{
    
});
$(document).ready(function() 
{ 
    $("input[name='dcs_command']").val('load'); 
    var itemid = $("input[name='dcs_itemid']").val(); 
    var data = $('.ajax').serializeArray();
    $.ajax({
      url: AJAX_URL+itemid,
      type: 'get',
      dataType: 'json',
      data: data,
      success: onLoadValID,
      error: function(data) {console.log(data);}
      }
    );
    window.onpopstate = function(event) {
        location.href = document.location;
    };
});

