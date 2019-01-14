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
    if ((curname.indexOf('name_') + 1) > 0) {
        $curdiv.find('input#'+curname.substring(5)).val(curvalue);
    }
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
    $.each(data.items, function(key, val) {
        if (val.id) {    
            $("input#"+val.id).val(val.name);
        }
    });    
}

function onGetData(data)
{
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
                        if ('valmdtypename' in pval) {
                            if (pval.valmdtypename == 'Sets') {
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
        } else {
            $elist = $("tbody.entitylist",$("div#"+propid));
            sdata = data.SDATA[propid];
            pset = data.SETS[propid];
        }    
        loadset($elist,sdata,pset);
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
    if (eventObject.which == 27) { 
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
                $("input[name='dcs_curid']").val(curid);
                $("input[name='dcs_param_id']").val(it);
                $("input[name='dcs_param_val']").val(name);
                $("input[name='dcs_param_type']").val(itype);
                if (itype == 'propid') {    
                    $("input[name='dcs_command']").val('prop_find');
                }
                $data = $('.ajax').serializeArray();
                $.getJSON(
                    getprefix()+'/ajax/'+itemid,
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
$('input#type').dblclick(function() { 
    $(".types_list").slideUp('fast'); 
    $("#type~.types_list").slideToggle('fast');
}); 
$('input.form-control[vt=bool]').dblclick(function(e) { 
    e.preventDefault();
    action = $("input[name='dcs_action']").val();
    if ((action === 'EDIT')||(action === 'CREATE'))
    {    
        var curid = this.id;
        $(".types_list").slideUp('fast'); 
        $("#"+curid+"~.types_list").slideToggle('fast');
    }    
});
function tr_dblclick($e)
{
    var itemid = $e.attr('id');
    if (itemid === undefined || itemid === null) {
        return;
    } 
    var dop = '';
    var setid = $("input[name='dcs_setid']").val();
    var docid = $("input[name='dcs_itemid']").val();
    if (setid !== '') {
        dop = '?dcs_docid='+docid+'&dcs_propid='+setid;
    } 
    location.href=getprefix()+'/'+itemid+dop;
}
$('body').on('dblclick','#dcs-list tr',function () 
{
    tr_dblclick($(this));
});
$('body').on('dblclick','#modallist tr',function (e) 
{
    e.preventDefault();
    var itemid = $("input[name='dcs_itemid']").val(); 
    $("input[name='dcs_curid']").val(this.id); 
    $("input[name='dcs_command']").val('choice'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: onchoice
    });  
    $('#dcsModal').modal('hide');
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
    console.log($data)
}
function setvalid($obj,cid,cname)
{
    $obj.html(cname);
    $obj.attr('it',cid);
}
function setvals(data)
{
    var $row = $('tr#'+data['id']);
    $.each(data['objs'], function(key, val) 
    {
        var $td = $row.find('td#'+key);
        setvalid($td,val.id,val.name);
    });    
}
function submitModalForm(e)
{
    e.preventDefault();
    var setid = $("input[name='dcs_propid']").val();
    var $x = $('div#ivalue');
    var $ci = $x.find('input');
    var itemid = $("input[name='dcs_itemid']").val();
    var $pan = $('div#'+setid).find('table');
    var $curcol = $pan.find('#tablehead th.info');
    var propid = $curcol.attr('id');
    var $currow = $pan.find('#dcs-items tr.info');
    var $etd = $currow.find('td#'+propid);
    var cnm = $ci.val();
    var cid = $ci.attr('it');
    var typ = $ci.attr('type');
    $x.hide();
    if (typ == 'file') {
        var $photos = $('#dcsFileInput'),
            formdata = new FormData,
            validationErrors = validateFiles({
                $files: $photos,
                maxSize: 2 * 1024 * 1024,
                types: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
            });
            
        // Валидация
        if (validationErrors.length) {
            console.log('client validation errors: ', validationErrors);
            return false;
        }

        // Добавление файлов в formdata
        $photos.each(function(index, $photo) {
            if ($photo.files.length) {
                formdata.append('photos[]', $photo.files[0]);
            }
        });
        formdata.append('id', $currow.attr('id')+'_'+$curcol.attr('id'));
        // Отправка на сервер
        $.ajax({
            url: '/common/upload.php',
            data: formdata,
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            success: show_uploadfile
        });
    }   
    $("input[name='dcs_curid']").val($currow.attr('id'));
    $("input[name='dcs_command']").val('field_save');
    $("input[name='dcs_param_propid']").val(propid);
    $("input[name='dcs_param_id']").val(cid);
    $("input[name='dcs_param_val']").val(cnm);
    $("input[name='dcs_param_type']").val(typ);
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
      success: setvals
    });
}
function show_ivalue($e, it)
{
    var action = $("input[name='dcs_action']").val();
    var arr_type = ['id','cid','mdid','propid'];
    var bwidth = 0;
    var itype = 'text';
    var max_tdwidth = 200;
    var $x = $('div#ivalue');
    var tdwidth = $e.width();
    var vt = $e.attr('vt');
    var dname = $e.html();
    var ov = dname;
    var cur_offset = $e.offset().left-40;
    
    $x.empty();
    if (vt == 'date') {
        itype = 'datetime';
        max_tdwidth = 100;
    } else if (vt == 'int') {
        itype='number';
        max_tdwidth = 100;
    } else if (vt == 'float') {
        itype = 'number\" step=\"any';
        max_tdwidth = 120;
    }
    s_html = '';
    if (arr_type.indexOf(vt) >= 0) {
        ov = it;
        s_html = "<input type=\""+itype+"\" class=\"form-control ajax\" \n\
                        vt=\""+vt+"\" it=\""+it+"\" ov=\""+ov+"\" \n\
                        value=\""+dname+"\"><span class=\"input-group-btn\" \n\
                        style=\"width:0;\">\n\
                        <button id=\"list\" class=\"form-value\">\n\
                          <i class=\"material-icons\">list</i></a>\n\
                        </button>\n\
                        <button id=\"done\" class=\"form-value\">\n\
                          <i class=\"material-icons\">done</i>\n\
                        </button></span>";
        bwidth += 90; 
    } else if (vt == 'file') {
        if (action === 'VIEW') {
            s_html = "<a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a>";
        } else {    
            if (dname === '') {    
                s_html = "<input id=\"dcsFileInput\" type=\"file\" \n\
                             accept=\"image/*;capture=camera\" \n\
                        class=\"form-value\" vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" \n\
                        value=\""+dname+"\">\n\
                          <span class=\"input-group-btn\" \n\
                           style=\"width:0;\">\n\
                           <button id=\"done\" class=\"form-value\">\n\
                              <i class=\"material-icons\">done</i>\n\
                           </button></span>";
            } else {
                s_html = "<a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a>\n\
                         <span class=\"input-group-btn\" style=\"width:0;\">\n\
                         <button id=\"delete_ivalue\" class=\"form-value\">\n\
                           <i class=\"material-icons\">delete</i>\n\
                         </button>\n\
                         </span>";
            }
        }    
    } else {
        s_html = "<input type=\""+itype+"\" class=\"form-control ajax\" \n\
                           vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" value=\""+dname+"\">\n\
                          <span class=\"input-group-btn\" style=\"width:0;\">\n\
                          <button id=\"done\" class=\"form-value\">\n\
                            <i class=\"material-icons\">done</i>\n\
                          </button></span>";       
        bwidth +=50; 
    }
    $x.append(s_html);
    if (tdwidth < max_tdwidth)
    {
        tdwidth = max_tdwidth;
    }
    $x.width(tdwidth);
    $x.find('input').width(tdwidth-bwidth);
    $x.show();
    var max_width = $("body").width();
    if ((cur_offset+tdwidth) > max_width) {
        cur_offset = max_width - tdwidth-5;
    }
    $x.offset({top:$e.offset().top+$e.height(),left:cur_offset});
    $("body").one('click','button.form-value#done',submitModalForm);
}
$('body').on('dblclick','#entityhead input',function () 
{
    var action = $("input[name='dcs_action']").val();
    var $etd = $(this);
    var it = $etd.attr('it');
    var vt = $etd.attr('vt');
    var dname = $(this).html();
    if (action === 'VIEW') {
        if (vt !== 'file') {
            return;
        }    
        if (dname === '') {
            return;
        }    
    } else if (action !== 'EDIT') {
        return;
    }
    it = get_input_fieldid($etd);
    if (it) {
        show_ivalue($etd, it);
    }    
});
$('body').on('dblclick','#dcs-items tr',function () 
{
    var $action = $("input[name='dcs_action']");
    var mode = $("input[name='dcs_mode']").val();
    if (($action.val() === 'VIEW') || (mode === 'CONFIG')) {    
        if (mode === 'CONFIG') {    
            $action.val('EDIT');
        }
        tr_dblclick($(this));
    }
});
$('body').on('dblclick','#dcs-items td',function () 
{
    var action = $("input[name='dcs_action']").val();
    if ($(this).parent().attr('st') === 'erased') {
        return;
    }
    var $etd = $(this);
    var it = $etd.attr('it');
    var vt = $etd.attr('vt');
    var dname = $(this).html();
    if (action === 'VIEW') {
        if (vt !== 'file') {
            return;
        }    
        if (dname === '') {
            return;
        }    
    } else if (action !== 'EDIT') {
        return;
    }
    show_ivalue($etd, it);
});   

$('body').on('click','button.form-value#list', function(e)
{
    e.preventDefault();
    var $pan = $('.tab-pane.fade.in.active');
    var $tr = $pan.find('tr.info'); 
    var $th = $pan.find('th.info'); 
    var itemid = $("input[name='dcs_itemid']").val(); 
    $("input[name='dcs_curid']").val($tr.attr('id')); 
    $("input[name='dcs_param_propid']").val($th.attr('id')); 
    $("input[name='dcs_param_id']").val($('div#ivalue input.form-control').attr('ov')); 
    $("input[name='dcs_param_type']").val($('div#ivalue input.form-control').attr('vt')); 
    $("input[name='dcs_param_val']").val($('div#ivalue').find('input').val()); 
    $("input[name='dcs_command']").val('list'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: onloadlist
    });    
});

$('body').on('click','button.form-value#delete_ivalue', function(e)
{
    e.preventDefault();
    var itemid = $("input[name='dcs_itemid']").val();
    var $x = $('div#ivalue');
    var $ci = $x.find('input');
    var $curcol = $('th.info');
    var propid = $curcol.attr('id');
    var $currow = $('#dcs-items tr.info');
    var $etd = $currow.find('td#'+propid);
    var typ = $ci.attr('type');
    $etd.html('');
    $etd.attr('it','');
    $x.hide();
    $("input[name='dcs_curid]").val($currow.attr('id')); 
    $("input[name='dcs_param_propid']").val(propid); 
    $("input[name='dcs_param_id']").val(''); 
    $("input[name='dcs_param_val']").val(''); 
    $("input[name='dcs_param_type']").val(typ); 
    $("input[name='dcs_command']").val('field_save'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
      success: setvals
    });    
});

$(':input.form-control').click(function () {
    
    $("input~.types_list").slideUp('fast');
    $('div#ivalue').hide();
    var $curinp = $(":input.form-control[st='info']");
    $curinp.attr('st','active');
    $(this).attr('st','info');
});


$('body').on('click', 'ul.types_list li', function(){
    
    var itemid = $("input[name='dcs_itemid']").val();
    var tx = $(this).html(); 
    var lid = this.id; 
    var $curdiv = $(this).parent().parent();
    var $curinp = $curdiv.find("input[type='text']");
    var curtype = $curinp.attr('vt');
    set_input_fieldid($curinp,lid);
    $curinp.val(tx); 
    $(".types_list").slideUp('fast'); 
    $("input[name='dcs_param_propid']").val(''); 
    $("input[name='dcs_param_id']").val(lid); 
    $("input[name='dcs_param_val']").val(tx); 
    $("input[name='dcs_param_type']").val(curtype); 
    $("input[name='dcs_command']").val('after_choice'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: onGetData
    });    
    
});
$('body').on('click','.entitylist tr',function () 
{
  var curid = this.id;
  $('tr.info').attr("class","active");
  $('#'+curid).attr("class","info");
});
$('body').on('click','.entitylist td',function () 
{
    var curcol = this.id;
    $('th.info').attr("class","active");
    $('th#'+curcol).attr("class","info");
    $('div#ivalue').hide();
});
$('body').on('click','#modallist tr',function () 
{
  $('#modallist tr.info').attr("class","active");
  $(this).attr("class","info");
});


$("#dcsTab a").click(function(e){
  e.preventDefault();
  $(this).tab('show');
});
$('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
    var $x = $('div.ivalue-block');
    $x.find('form').remove();
    $x.find('.form-value').remove();
    $x.hide();
    var activeid = $(e.target).attr('href').substring(1);
    var itemid = $("input[name='dcs_itemid']").val();    
    var s_param_id = '';
    if (activeid != 'entityhead') {
        s_param_id = $("div.tab-pane[id="+activeid+"]").attr('it'); 
    } else {
        activeid = '';
    } 
    $("input[name='dcs_propid']").val(activeid); 
    $("input[name='dcs_setid']").val(s_param_id); 
    $("input[name='dcs_command']").val('load'); 
    var $data = $('.ajax').serializeArray();
    $.ajax(
    {
        url: getprefix()+'/ajax/'+itemid,
        type: 'get',
        dataType: 'json',
        data: $data,
        error: function(xhr, error){
                console.debug(xhr); console.debug(error);
        },                
        success: function(result) {
            onLoadValID(result);
        }
    });      
});
$('body').on('click','a#create', function () 
{
    var itemid = $("input[name='dcs_itemid']").val();    
    if (itemid === '') {
        return;
    }    
    $("input[name='dcs_command']").val('create'); 
    $("input[name='dcs_param_id']").val('');    
    $("input[name='dcs_param_type']").val('');    
    $("input[name='dcs_param_val']").val('');    
    $("input[name='dcs_curid']").val('');    
    $data = $('.ajax').serializeArray();
    $.ajax(
    {
        url: getprefix()+'/ajax/'+itemid,
        type: 'get',
        dataType: 'json',
        data: $data,
        error: function(xhr, error){
            console.debug(xhr); console.debug(error);
        },                
        success: onLoadValID
    });
});
$('body').on('click', '#edit', function () {
    tr_dblclick($('#dcs-list tr.info'));
    tr_dblclick($('#dcs-items tr.info'));
});
$('body').on('click', '#view', function () {
    tr_dblclick($('#dcs-list tr.info'));
    tr_dblclick($('#dcs-items tr.info'));
});
function erase_success (result)
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var propid = $("input[name='dcs_propid']").val();    
    $('#dcsModal').modal('hide');
    dop='';
    if (propid !== '')
    {
        dop ='?dcs_propid='+propid; 
    }   
    location.href=getprefix()+'/'+itemid+dop;
};
function erase() {
    var $data;
    var itemid = $("input[name='dcs_itemid']").val(); 
    var $cid = $("input[name='dcs_curid']");    
    var curid = $cid.val();    
    $("input[name='dcs_command']").val('delete'); 
    $cid.val($('tr.info').attr('id')); 
    $data = $('.ajax').serializeArray();
    $cid.val(curid); 
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: erase_success
    });
};
function before_delete_success(result) 
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len=0;
    $mh.empty();
    $mt.empty();
    var shtml = "<tr><th>Объект</th><th>Наименование</th><th>Действие</th></tr>";
    if (Object.keys(result).length) 
    {
        $.each(result, function(key, val) 
        {
            if (key!='handlername')
            {
                shtml = shtml +'<tr><td>'+val.name+'</td><td>'+val.pval+'</td><td>'+val.nval+'</td></tr>';
                len++;
            }
        });    
    }
    $mh.append(shtml);
    if (len)
    {    
        $(".modal-title").text('Подтвердите действие');
        $('body').one('click', '#dcsModalOK', erase);
    }    
    else 
    {
        $(".modal-title").text('Действие не выполнено.');
        $('body').one('click', '#dcsModalOK', function () {
            $('#dcsModal').modal('hide');
        });
    }    
    $('#dcsModal').modal('show');
}   
function delete_error(result) 
{
    $(".modal-title").text('Действие не выполнено.');
    $('body').one('click', '#dcsModalOK', function () {
        $('#dcsModal').modal('hide');
    });
    $('#dcsModal').modal('show');
}   
$('#dcsModal').on('shown.bs.modal', function () {
    $(this).find('.modal-dialog').css({width:'70%',
                               height:'auto', 
                              'max-height':'100%'});
});
$('body').on('click', '#delete', function () 
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var $cid = $("input[name='dcs_curid']");    
    var curid = $cid.val();
    $cid.val($('tr.info').attr('id')); 
    $("input[name='dcs_command']").val('before_delete'); 
    $data = $('.ajax').serializeArray();
    $cid.val(curid); 
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: before_delete_success,
        error: delete_error
    });    
});
$('body').on('click', '#filter', function (e) 
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var curid = $('tr.info').attr('id');
    var curcol = $('th.info').attr('id');
    e.preventDefault();
    var $data;
    var $el_cur  = $("tr#"+curid).find("td#"+curcol);
    var $el_fval = $("input[name='dcs_param_val']");
    var filter_val = $el_fval.val();
    var curval = '';
    var fval  = $el_cur.html();
    var fid   = $el_cur.attr("it");
    $("input[name='dcs_param_id']").val(curcol); 
    if (fid !== '') {
        $el_fval.val(fid); 
        curval = fid;
    } else {
        $el_fval.val(fval); 
        curval = fval;
    }
    if (curval !== "") {
        if (filter_val != curval) {
            $el_fval.val(curval); 
        } else {
            $el_fval.val(''); 
        }    
    } else  {
        if (filter_val !== "") {
            $el_fval.val(''); 
        }    
    }    
    curval = $el_fval.val();
    $("input[name='dcs_command']").val('load'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
});
function show_history(result)
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len = 0;
    $mh.empty();
    $mt.empty();
    var s_html = '';
    for(var j in result['PSET']) 
    {
        if(result['PSET'].hasOwnProperty(j)){
            s_html = s_html +"<th class=\""+result['PSET'][j].class+
                    "\">"+result['PSET'][j].synonym+"</th>";
        }    
    }
    if (s_html !== '') {
        $mh.append("<tr>"+s_html+"</tr>");
    }
    
    if (Object.keys(result['LDATA']).length) 
    {
        s_html = '';
        for(var i in result['LDATA']) 
        {    
            s_html = s_html + '<tr>';
            for(var j in result['PSET']) 
            {
                if(result['PSET'].hasOwnProperty(j))
                {
                    s_html = s_html + "<td class=\""+result['PSET'][j].class+
                    "\">"+result['LDATA'][i][result['PSET'][j].name].name+"</td>";
                }    
            }
            s_html = s_html + '</tr>';
        }    
        if (s_html !== '') {
            $mt.append(s_html);
        }
    }
    $(".modal-title").text('История изменения реквизита: '+result['synonym']);
    $('body').one('click', '#dcsModalOK', function () {
        $('#dcsModal').modal('hide');
    });
    $('#dcsModal').modal('show');
}

$('body').on('click', '#history', function (e)
{
    var itemid = $("input[name='dcs_itemid']").val();
    var $curinp = $(":input.form-control[st='info']");
    if (itemid != '') 
    {
        var tcurid = $curinp.attr('id');  
        if (tcurid != '') 
        {
            if (-1 < tcurid.indexOf('name_')) 
            {  
                tcurid = tcurid.replace('name_', '');
            }
            $("input[name='dcs_propid']").val(tcurid);
            $("input[name='dcs_command']").val('history'); 
            $data = $('.ajax').serializeArray();
            $.ajax({
              url: getprefix()+'/ajax/'+itemid,
              type: 'get',
              dataType: 'json',
              data: $data,
                success: show_history
            });    
            $("input[name='dcs_propid']").val('');
        }
    }    
});

$('body').on('click', '#submit', function (e)
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var prefix = $("input[name='dcs_prefix']").val();  
    var act = $("input[name='act']").val();  
    var $data;
    var curl = '/';
    $data = $('.ajax').serializeArray();
    if (prefix === 'AUTH') {
        curl = '/auth/'+act;
    } else {
        curl = getprefix()+'/ajax/'+itemid;
    }
    $.ajax({
      url: curl,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: function(result) {
            location.href=result['redirect'];
        }
    });    
});

$('body').on('click', '#print', function (e) 
{
    var itemid = $("input[name='dcs_itemid']").val();
    var setid = $("input[name='dcs_setid']").val();
    var href = '';
    e.preventDefault();
    var dop = '';
    if (setid !== '') {
        dop = '?dcs_propid='+setid;
    }
    href = getprefix()+'/'+itemid+'/print'+dop;
    var otherWindow = window.open(href,"_blank");
    otherWindow.opener = null;
});

function before_save() 
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var $data;
    $("input[name='dcs_command']").val('before_save'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: before_save_success,
        error: save_error
    });    
};
function save() 
{
    var itemid = $("input[name='dcs_itemid']").val(); 
    var $data;
    $("input[name='dcs_command']").val('save'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: save_success,
        error: save_error
      });
};
function save_success (result)
{
    $('#dcsModal').modal('hide');
    
    location.href=getprefix()+'/'+result['id'];
};
function save_error (result)
{
    $(".modal-title").text('Object is not saved : Объект не записан');
    $('body').one('click', '#dcsModalOK', function () {
            $('#dcsModal').modal('hide');
    });
    $('#dcsModal').modal('show');
};
function before_save_success(result) 
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len=0;
    $mh.empty();
    $mt.empty();
    if (Object.keys(result).length) {
        shtml = "<tr><th>Реквизит</th><th>Значение было</th><th>Новое значение</th></tr>";
        $mh.append(shtml)
        shtml = '';
        $.each(result, function(key, val) {
            if (key != 'handlername') {
                shtml = shtml +'<tr><td>'+val.name+'</td><td>'+val.pval+'</td><td>'+val.nval+'</td></tr>';
                len++;
            }
        });    
        $mt.append(shtml);
    }
    if (len) {    
        $(".modal-title").text('Saving the modified data');
        $('body').one('click', '#dcsModalOK', save);
    } else {
        $(".modal-title").text('Saving data is not required');
        $('body').one('click', '#dcsModalOK', function () {
            $('#dcsModal').modal('hide');
        });
    }    
    $('#dcsModal').modal('show');
};  
$('body').on('click','#save',function(e) {
    var action = $("input[name='dcs_action']").val();  
    if (action==='EDIT') {
        before_save();  
    } else {
        save();  
    }
});
function logout()
{
    $("input[name='dcs_command']").val('logout');
    $("input[name='dcs_prefix']").val('AUTH'); 
    var data = $('.ajax').serializeArray();
    $.ajax(
    {
        url: '/auth/form/logout',
        type: 'get',
        dataType: 'json',
        data: data,
        success: function(result) {
            location.href=result['redirect'];
        }
    })      
};
function saymsg(msg)
{
    console.log(msg);
};
function activate_pickadate()
{
    var action = $("input[name='dcs_action']").val();
    if ((action === 'EDIT')||
        (action === 'CREATE')) {
        $('.datepicker').datepicker();
 //       $("input.form-control[vt='date']").pickadate({
 //                   selectMonths: true,
 //                   format: 'dd.mm.yyyy',
 //                   formatSubmit: 'dd.mm.yyyy'
 //                 });
    }              
};
$(document).ready(function() 
{ 
    var itemid = $("input[name='dcs_itemid']").val(); 
    var action = $("input[name='dcs_action']").val(); 
    var prefix = $("input[name='dcs_prefix']").val();
    var command = $("input[name='dcs_command']").val();
//    activate_pickadate();
    if (command !== '') {
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
    window.onpopstate = function(event) {
        location.href = document.location;
    };
});
