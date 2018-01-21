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
    console.log(data);
    $.each(data, function(key, val) 
    {
      a_html = a_html+"<li><a href=\""+getprefix()+"/"+key+"\">"+val+"</a></li>";
    });
    $navtab.append(a_html); 
}

function onchoice(data)
{
    var curinp = $("div#ivalue input.form-control");
    curinp.val(data['name']); 
    curinp.attr('it',data['id']); 
}
$('a').on('show.bs.tab', function (e) {
    $('div#ivalue').hide();
    var action = $("input[name='action']").val(); 
    var itemid = $("input[name='itemid']").val(); 
    var activeid = $(e.target).attr('href').substring(1);
    if (activeid != 'entityhead')
    {   
        if ((action=='EDIT')||(action=='VIEW'))
        {
            action = 'SET_'+action; 
        }    
        var $x = $('div#ivalue');
        
        $x.empty();
        $x.hide();
        $("input[name='action']").val(action); 
        $("input[name='curid']").val(activeid); 
        $("input[name='command']").val('load'); 
        $data = $('.ajax').serializeArray();
        $.ajax(
        {
            url: getprefix()+'/ajax/'+itemid+'/'+activeid+'/'+action,
            type: 'get',
            dataType: 'json',
            data: $data,
            error: function(xhr, error){
                    console.debug(xhr); console.debug(error);
                window.history.pushState({},null, getprefix()+'/'+itemid+'/'+activeid+'/'+action);            
            },                
            success: function(response) {
                onLoadValID(response);
                window.history.pushState({},null, getprefix()+'/'+itemid+'/'+activeid+'/'+action);            
            }
        });      
    } else {
        if ((action=='SET_EDIT')||(action=='SET_VIEW'))
        {
            action = action.substring(4);
        }    
        $("input[name='curid']").val(''); 
        $("input[name='action']").val(action); 
        $("input[name='command']").val('load'); 
        $data = $('.ajax').serializeArray();
        $.ajax({
            url: getprefix()+'/ajax/'+itemid+'/'+action,
            type: 'get',
            dataType: 'json',
            data: $data,
            success: function(response) {
                onLoadValID(response);
                window.history.pushState({},null, getprefix()+'/'+itemid+'/'+action);            
            }
        });
    }    
});

function onGetMdData(data)
{
    $.each(data.items, function(key, val) 
    {
        if (val.id)
        {    
            $("input#"+val.id).val(val.name);
        }
    });    
}

function onGetData(data)
{
    $.each(data.items, function(key, val) 
    {
        if (val.id)
        {    
            $("input#"+key).val(val.id);
            $("input#name_"+key).val(val.name);
        }
    });    
}
    
function getprefix()
{
    var prefix = $("input[name='prefix']").val();    
    if (prefix !== 'CONFIG') {
        return "";
    } else {
        return "/CONFIG";
    }      
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
    if ('LDATA' in data) {    
        loadset(data,$mt);
    }    
    $(".modal-title").text('Выбор из списка');
    $('body').one('click', '#dcsModalOK', function () {
        $('#dcsModal').modal('hide');
    });
    $('#dcsModal').modal('show');
}
function loadset(data,$elist)
{
    var shtml = '';
    var dname_st;
    var dname_en;
    var cnt = 0;
    $elist.empty();
    $.each(data.LDATA, function(id, val) {
        shtml = shtml + "<tr class=\"active\" st=\""+val.class+"\" id=\""+id+"\">";
        dname_st = "";
        dname_en = "";
        if (val.class == 'erased')
        {
            dname_st = "<del>";
            dname_en = "</del>";
        }    
        $.each(data.PSET, function(pid, pval) {
            if (pval.id in val)
            {    
                var dname = val[pval.id]['name'];
                var did = val[pval.id]['id'];
                shtml = shtml + "<td class=\""+pval.class+"\" id=\""+pval.id+"\" it=\""+did+"\" vt=\""+pval.type+"\">"+dname_st+dname+dname_en+"</td>";    
            }
            else
            {
                shtml = shtml + "<td class=\""+pval.class+"\" id=\""+pval.id+"\" it=\"\" vt=\""+pval.type+"\"></td>";    
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
    var action = $("input[name='action']").val();
    var curid = $("input[name='curid']").val();
    var arr_type = ['id','cid','mdid','propid'];
    if ('SDATA' in data) {    
        $.each(data.SDATA, function(id, val) {
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
                if (action === 'VIEW') {
                    $("input.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    if (arr_type.indexOf(pval.type) >= 0) {    
                        $("input.form-control[id=name_"+cid+"]").attr('readonly', 'readonly');
                    } else if (pval.type == 'text') {    
                        $("textarea.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    }    
                }    
            });
        });
    }
    if ('LDATA' in data) {    
        if (curid === '') {
            $elist = $("tbody#entitylist");
        } else {
            $elist = $("tbody#entitylist",$("div#"+curid));
        }    
        loadset(data,$elist);
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
    $.each(data.items, function(key, val) 
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
    
    var action = $("input[name='action']").val();
    var itemid = $("input[name='itemid']").val();
    if (action==='VIEW')
    {
        return;
    }
    var itype = $(this).attr("it");
    var curid = this.id;
    var $curinp = $(":input.form-control[st='info']");
    var arr_type = ['id','cid','mdid','propid'];
    if ($curinp[0] != $(this)[0])
    {
        $curinp.attr('st','active');
        this.attr('st','info');
    }    
    if (eventObject.which==27) 
    { 
        $("#"+curid+"~.types_list").slideUp('fast');
    }
    else 
    {
        var vt = this.attr("vt");
        if (vt == '')
        {
            vt = $("input[it='mdid'][type='hidden']").val();
        }    
        var $data = {action:action, id:vt, type:itype, name:this.val(), command:'find', prefix:'field'};
        if (curid == 'name_valmdid')
        {    
            var $curtype = $("input#type");
            $data = {action:action, id:$("input#valmdid").val(),type: 'mdid',name:this.val(),'command':'find', prefix:'field'};
            itype = $curtype.val();
        }
        if (arr_type.indexOf(itype)>=0) {
            if (itype=='propid') {    
                $("input[name='curid']").val(curid);
                $("input[name='command']").val('prop_find');
                $data = $('.ajax').serializeArray();
            }
            $("#"+curid+"~.types_list").slideUp('fast'); 
            if ($(this).val().length>1) 
            {
               $.getJSON(
                    getprefix()+'/ajax/'+itemid+'/'+curid+'/'+action,
                    $data,
                    onLoadGetData
                );
            } else {
                if (this.val().length === 0) 
                {
                    var curname = $curinp.attr('name');
                    if((curname.indexOf('name_') + 1)>0)
                    {
                        curid = curname.substring(5);
                        curinpid = $('div.form-group').find('input#'+curid);
                        curinpid.val(''); 
                    }    
                }    
            }    
        }	
    }  
}); 
$('input#type').dblclick(function() { 
    $(".types_list").slideUp('fast'); 
    $("#type~.types_list").slideToggle('fast');
}); 
$('input.form-control[it=bool]').dblclick(function(e) { 
    e.preventDefault();
    action = $("input[name='action']").val();
    if ((action === 'EDIT')||(action === 'CREATE'))
    {    
        var curid = this.id;
        $(".types_list").slideUp('fast'); 
        $("#"+curid+"~.types_list").slideToggle('fast');
    }    
});
$('body').on('dblclick','#entitylist tr',function () 
{
    var action = $("input[name='action']").val();
    var docid = $("input[name='itemid']").val();
    var curid = $("input[name='curid']").val();
    var itemid = this.id;
    if (getprefix() === 'CONFIG') {
        action = "edit";
    } 
    if (action.substring(0,4) === 'SET_') {
        action = action.substring(4);
    } 
    location.href=getprefix()+'/'+itemid+'/'+action+'?docid='+docid+'\&propid='+curid;
});
$('body').on('dblclick','#modallist tr',function (e) 
{
    e.preventDefault();
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val(); 
    $("input[name='curid']").val(this.id); 
    $("input[name='command']").val('choice'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+'/'+this.id+'/'+action,
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
    if (!'status' in data.items)
    {
        console.log(data);
        return;
    }    
    if (data.items['status'] != 'OK')
    {
        console.log(data);
        return;
    }   
    var $row = $('tr#'+data.items['id']);
    $.each(data.items['objs'], function(key, val) 
    {
        var $td = $row.find('td#'+key);
        setvalid($td,val.id,val.name);
    });    
}
function submitModalForm(e)
{
    e.preventDefault();
    var $x = $('div#ivalue');
    var $ci = $x.find('input');
    var action = $("input[name='action']").val();
    var itemid = $("input[name='itemid']").val();
    var $pan = $('.tab-pane.fade.in.active');
    var $curcol = $pan.find('#tablehead th.info');
    var propid = $curcol.attr('id');
    var $currow = $pan.find('#entitylist tr.info');
    var $etd = $currow.find('td#'+propid);
    var cnm = $ci.val();
    var cid = $ci.attr('it');
    var typ = $ci.attr('type');
    $x.hide();
    if (typ == 'file')
    {
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
    var $data = {action:action, propid: propid, id: cid, type:typ, name:cnm, itemid:$currow.attr('id'), command:'save', prefix:'field'};
    $.getJSON(
         getprefix()+'/ajax/'+itemid+'/'+propid+'/'+action,
         $data,
         setvals
     );
}

$('body').on('dblclick','#entitylist td',function () 
{
    var action = $("input[name='action']").val();
    if ($("input[name='prefix']").val() === 'CONFIG') {
        return;
    }
    if ($(this).parent().attr('st') === 'erased') {
        return;
    }
    var $etd = $(this);
    var it = this.it;
    var vt = this.vt;
    var dname = $(this).html();
    var arr_type = ['id','cid','mdid','propid'];
    if ((action !== 'SET_EDIT')&&(action !== 'SET_VIEW'))
    {
        return;
    }
    if (action === 'SET_VIEW')
    {
        if (vt !== 'file')
        {
            return;
        }    
        if (dname === '')
        {
            return;
        }    
        
    }    
    var tdwidth = $(this).width();
    var $x = $('div#ivalue');
    $x.empty();
    var bwidth = 0;
    var ov = dname;
    var itype='text';
    var max_tdwidth = 200;
    if (vt=='date')
    {
        itype='datetime';
        max_tdwidth = 100;
    }
    else if (vt=='int')
    {
        itype='number';
        max_tdwidth = 100;
    }
    else if (vt=='float')
    {
        itype='number\" step=\"any';
        max_tdwidth = 120;
    }
    s_html = '';
    if (arr_type.indexOf(vt)>=0)
    {
        ov = it;
        s_html = "<input type=\""+itype+"\" class=\"form-control ajax\" vt=\""+vt+"\" it=\""+it+"\" ov=\""+ov+"\" value=\""+dname+"\"><span class=\"input-group-btn\" style=\"width:0;\"><button id=\"list\" class=\"form-value\"><i class=\"material-icons\">list</i></a></button><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></span>";
        bwidth +=90; 
    }    
    else if (vt=='file')
    {
        if (action === 'SET_VIEW')
        {
            s_html = "<a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a>";
        }   
        else
        {    
            if (dname === '')
            {    
                s_html = "<input id=\"dcsFileInput\" type=\"file\" accept=\"image/*;capture=camera\" class=\"form-value\" vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" value=\""+dname+"\"><span class=\"input-group-btn\" style=\"width:0;\"><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></span>";
            }    
            else
            {
                s_html = "<a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a><span class=\"input-group-btn\" style=\"width:0;\"><button id=\"delete_ivalue\" class=\"form-value\"><i class=\"material-icons\">delete</i></button></span>";
            }
        }    
    }    
    else
    {
        s_html = "<input type=\""+itype+"\" class=\"form-control ajax\" vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" value=\""+dname+"\"><span class=\"input-group-btn\" style=\"width:0;\"><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></span>";       
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
    var cur_offset = $etd.offset().left-40;
    var max_width = $("body").width();
    if ((cur_offset+tdwidth) > max_width) {
        cur_offset = max_width - tdwidth-5;
    }
    $x.offset({top:$etd.offset().top+$etd.height(),left:cur_offset});
    $("body").one('click','button.form-value#done',submitModalForm);
});   

$('body').on('click','button.form-value#list', function(e)
{
    e.preventDefault();
    var $pan = $('.tab-pane.fade.in.active');
    var $tr = $pan.find('tr.info'); 
    var $th = $pan.find('th.info'); 
    var action = $("input[name='action']").val();  
    var itemid = $("input[name='itemid']").val(); 
    $("input[name='curid']").val($tr.attr('id')); 
    $("input[name='filter_id']").val($th.attr('id')); 
    $("input[name='command']").val('list'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+'/'+$tr.attr('id')+'/'+action,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: onloadlist
    });    
});

$('body').on('click','button.form-value#delete_ivalue', function(e)
{
    e.preventDefault();
    var action = $("input[name='action']").val();
    var itemid = $("input[name='itemid']").val();
    var $x = $('div#ivalue');
    var $ci = $x.find('input');
    var $curcol = $('th.info');
    var propid = $curcol.attr('id');
    var $currow = $('tr.info');
    var $etd = $currow.find('td#'+propid);
    var typ = $ci.attr('type');
    $etd.html('');
    $etd.attr('it','');
    $x.hide();
    var $data = {action:action, propid: propid, id: '', type:typ, name:'', itemid:$currow.attr('id'), command:'save', prefix:'field'};
    $.getJSON(
         getprefix()+'/ajax/'+itemid+'/'+propid+'/'+action,
         $data

     );
});

$(':input.form-control').click(function () {
    
    $("input~.types_list").slideUp('fast');
    var $curinp = $(":input.form-control[st='info']");
    $curinp.attr('st','active');
    $(this).attr('st','info');
});


$('body').on('click', 'ul.types_list li', function(){
    
    var action = $("input[name='action']").val();
    var itemid = $("input[name='itemid']").val();
    var tx = $(this).html(); 
    var lid = this.id; 
    var $curdiv = $(this).parent().parent();
    var $curinp = $curdiv.find("input[type='text']");
    var curname = $curinp.attr('name');
    var curtype = $curinp.attr('it');
    var curid = '';
    if((curname.indexOf('name_') + 1)>0)
    {
        curid = curname.substring(5);
        $curinpid = $('div.form-group').find('input#'+curid);
        $curinpid.val(lid); 
    }    
    $curinp.val(tx); 
    $(".types_list").slideUp('fast'); 
    if ((curname=='name_propid')||(curname=='name_valmdid'))
    {
        $.getJSON(
            getprefix()+'/ajax/'+itemid+curid+'/'+action,
            {action:action, id:lid, type:curname, name:tx, command:'get', prefix:'mdname'},
            onGetMdData
        );
    }    
    else
    {
        if ((curtype=='id')||(curtype=='cid'))
        {
            var scurid = '';
            if (curid != '')
            {
                scurid = '/'+curid;
            }    
            $.getJSON(
                getprefix()+'/ajax/'+itemid+scurid+'/'+action,
                {action:action, id:itemid, type:curtype, name:lid, command:'Choice', prefix:'After', propid:curid},
                onGetData
            );    
        }    
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


$('body').on('click','a#create', function () 
{
    var itemid = $("input[name='itemid']").val();    
    var action = $("input[name='action']").val();    
    $("input[name='command']").val('create'); 
    if (itemid != '') 
    {
        if (action == 'SET_EDIT')
        {
            var curid = $("ul#dcsTab").find("li.active a").attr('href').substring(1);
            $("input[name='curid']").val(curid);    
            $data = $('.ajax').serializeArray();
            $.ajax(
            {
                url: getprefix()+'/ajax/'+itemid+'/'+curid+'/'+action,
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
        }   
        else
        {
            var curid = $("input[name='curid']").val();    
            dop='';
            if (curid !== '')
            {
                dop +='/'+curid; 
            }   
            location.href=getprefix()+'/'+itemid+dop+"/create";
        }    
    }  
});
$('body').on('click', '#edit', function () {
    var action = $("input[name='action']").val();    
    if ((action === 'EDIT')||(action === 'SET_EDIT'))
    {    
        var id = $('tr.info').attr('id');
        if (id !== '') 
        {
          location.href=getprefix()+'/'+id+"/edit";
        }  
    }
    else
    {
        var itemid = $("input[name='itemid']").val();    
        var curid = $("input[name='curid']").val();    
        url = getprefix()+'/'+itemid;
        if (curid !== '')
        {
            url += "/"+curid;
        }    
        location.href = url+"/edit";
    }    
});
$('body').on('click', '#view', function () {
    var id = $('tr.info').attr('id');
    if (id === undefined || id === null) 
    {
        return;
    } 
    location.href=getprefix()+'/'+id+'/view';
});
function erase_success (result)
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val(); 
    var curid = $("input[name='curid']").val();    
    $('#dcsModal').modal('hide');
    dop='';
    if (curid != '')
    {
        dop +='/'+curid; 
    }   
    location.href=getprefix()+'/'+itemid+dop+'/'+action;
};
function erase() {
    var $data;
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val(); 
    var $cid = $("input[name='curid']");    
    var curid = $cid.val();    
    $("input[name='command']").val('delete'); 
    $cid.val($('tr.info').attr('id')); 
    $data = $('.ajax').serializeArray();
    $cid.val(curid); 
    dop='';
    if (curid != '')
    {
        dop +='/'+curid; 
    }   
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
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
$('#dcsModal').on('shown.bs.modal', function () {
    $(this).find('.modal-dialog').css({width:'70%',
                               height:'auto', 
                              'max-height':'100%'});
});
$('body').on('click', '#delete', function () 
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val();  
    var $cid = $("input[name='curid']");    
    var curid = $cid.val();
    $cid.val($('tr.info').attr('id')); 
    $("input[name='command']").val('before_delete'); 
    $data = $('.ajax').serializeArray();
    $cid.val(curid); 
    var dop='';
    if (curid != '')
    {
        dop +='/'+curid; 
    }   
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: before_delete_success
    });    
});
$('body').on('click', '#filter', function (e) 
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val();  
    var curid = $('tr.info').attr('id');
    var curcol = $('th.info').attr('id');
    e.preventDefault();
    var $data;
    var $el_cur  = $("tr#"+curid).find("td#"+curcol);
    var $el_fval = $("input[name='filter_val']");
    var filter_val=$el_fval.val();
    var curval='';
    var fval  = $el_cur.html();
    var fid   = $el_cur.attr("it");
    $("input[name='filter_id']").val(curcol); 
    if (fid !== '')
    {
        $el_fval.val(fid); 
        curval = fid;
    }
    else 
    {
        $el_fval.val(fval); 
        curval = fval;
    }
    if (curval !== "") 
    {
        if (filter_val != curval) 
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
        if (filter_val !== "") 
        {
            $el_fval.val(''); 
        }    
    }    
    curval = $el_fval.val();
    $("input[name='command']").val('load'); 
    $data = $('.ajax').serializeArray();
    var dop='';
    if (curid != '')
    {
        dop +='/'+curid; 
    }   
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
      type: 'get',
      dataType: 'json',
      data: $data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
});
$('body').on('click', '#sort', function (e) 
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val();  
    e.preventDefault();
    var $data;
    var $el_sort_id = $("input[name='sort_id']");
    var $el_sort_dir = $("input[name='sort_dir']");
    var curcol = $('th.info').attr('id');
    var cur_sort_dir =$el_sort_dir.val();
    var cur_sort_id =$el_sort_id.val();
    if (cur_sort_id != curcol)
    {
        $el_sort_id.val(curcol); 
    }
    else
    {
        if (cur_sort_dir != '')
        {
            $el_sort_dir.val(''); 
        }
        else
        {
            $el_sort_dir.val('1'); 
        }    
    }
    $("input[name='command']").val('load'); 
    $data = $('.ajax').serializeArray();
    var dop='';
    if (curcol != '')
    {
        dop +='/'+curcol; 
    }   
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
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
    var len=0;
    $mh.empty();
    $mt.empty();
    var s_html = '';
    for(var j in result['PSET']) 
    {
        if(result['PSET'].hasOwnProperty(j))
        {
            s_html = s_html +"<th class=\""+result['PSET'][j].class+"\">"+result['PSET'][j].synonym+"</th>";
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
            $mt.append('<tr></tr>');
            s_html = s_html + '<tr>';
            for(var j in result['PSET']) 
            {
                if(result['PSET'].hasOwnProperty(j))
                {
                    s_html = s_html + "<td class=\""+result['PSET'][j].class+"\">"+result['LDATA'][i][result['PSET'][j].name].name+"</td>";
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
    var itemid = $("input[name='itemid']").val();
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
            $("input[name='curid']").val(tcurid);
            $("input[name='command']").val('history'); 
            $data = $('.ajax').serializeArray();
            $.ajax({
              url: getprefix()+'/ajax/'+itemid+'/'+tcurid+'/HISTORY',
              type: 'get',
              dataType: 'json',
              data: $data,
                success: show_history
            });    
        }
    }    
});

$('body').on('click', '#submit', function (e)
{
    var itemid = $("input[name='itemid']").val(); 
    var mode = $("input[name='mode']").val();  
    var action = $("input[name='action']").val();  
    var act = $("input[name='act']").val();  
    var $data;
    var curl = '/';
    $data = $('.ajax').serializeArray();
    if (mode == 'AUTH') {
        curl = '/auth/'+act;
    } else {
        curl = getprefix()+'/ajax/'+itemid+'/'+action;
    }
    $.ajax({
      url: curl,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: function(result) {
            location.href=result['redirect'];
            console.log(result);
        }
    });    
});

$('body').on('click', '#print', function (e) 
{
    var itemid = $("input[name='itemid']").val();
    var href='';
    e.preventDefault();
    href=getprefix()+"\\print\\"+itemid;
    var otherWindow = window.open(href,"_blank");
    otherWindow.opener = null;
});

function before_save() 
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val();  
    var curid = $("input[name='curid']").val();  
    var $data;
    var dop='';
    if (curid !== '') {
        dop +='/'+curid; 
    }   
    $("input[name='command']").val('before_save'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: before_save_success
    });    
};
function save() 
{
    var itemid = $("input[name='itemid']").val(); 
    var action = $("input[name='action']").val();  
    var curid = $("input[name='curid']").val();  
    var $data;
    var dop='';
    if (curid !== '') {
        dop +='/'+curid; 
    }   
    $("input[name='command']").val('save'); 
    $data = $('.ajax').serializeArray();
    $.ajax({
      url: getprefix()+'/ajax/'+itemid+dop+'/'+action,
      type: 'get',
      dataType: 'json',
      data: $data,
        success: save_success
    });
};
function save_success (result)
{
    $('#dcsModal').modal('hide');
    
    location.href=getprefix()+'/'+result['id']+'/edit';
    console.log(result);
};
function before_save_success(result) 
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len=0;
    $mh.empty();
    $mt.empty();
    shtml = '';
    shtml = "<tr><th>Реквизит</th><th>Значение было</th><th>Новое значение</th></tr>";
    mh.append(shtml)
    if (Object.keys(result).length) 
    {
        shtml = '';
        $.each(result, function(key, val) 
        {
            if (key!='handlername')
            {
                shtml = shtml +'<tr><td>'+val.name+'</td><td>'+val.pval+'</td><td>'+val.nval+'</td></tr>';
                len++;
            }
        });    
        $mt.append(shtml);
    }
    if (len)
    {    
        $(".modal-title").text('Saving the modified data');
        $('body').one('click', '#dcsModalOK', save);
    }    
    else 
    {
        $(".modal-title").text('Saving data is not required');
        $('body').one('click', '#dcsModalOK', function () {
            $('#dcsModal').modal('hide');
        });
    }    
    $('#dcsModal').modal('show');
}   
$('body').on('click','#save',function(e) {
    var action = $("input[name='action']").val();  
    if (action==='EDIT') {
        before_save();  
    } else {
        save();  
    }
});
function logout()
{
    $("input[name='command']").val('logout');
    $("input[name='mode']").val('AUTH'); 
    var data = $('.ajax').serializeArray();
    $.ajax(
    {
        url: '/auth/logout',
        type: 'get',
        dataType: 'json',
        data: data,
        success: function(result) {
            location.href=result['redirect'];
            console.log(result);
        }
    })      
};
function activate_pickadate(action)
{
    var action = $("input[name='action']").val();
    if ((action === 'EDIT')||
        (action === 'CREATE')) {
        $('input.form-control[it=date]').pickadate({
                    selectMonths: true,
                    format: 'yyyy-mm-dd',
                    formatSubmit: 'yyyy-mm-dd'
                  });
    }              
};
$(document).ready(function() 
{ 
    var curid = $("input[name='curid']").val(); 
    var itemid = $("input[name='itemid']").val(); 
    var mode = $("input[name='mode']").val();
    activate_pickadate(action);
    if (curid !== '') {
        itemid = itemid + '/' + curid;
    }
    if ((mode !== 'AUTH')&&(itemid !== '')) {
        $("input[name='command']").val('load'); 
        var action = $("input[name='action']").val();
        var $data = $('.ajax').serializeArray();
        $.ajax({
          url: getprefix()+'/ajax/'+itemid+'/'+action,
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
