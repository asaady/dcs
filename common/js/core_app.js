function actionlist(data)
{
    var $navtab = $('#actionlist');
    $navtab.find('li').remove();
    for(var $item in data)
    {    
      if (data[$item]['icon']=='')  
      {
        $navtab.append("<li><a class=\"btn\" id=\""+data[$item]['name']+"\" >"+data[$item]['synonym']+"</a></li>");  
      }else{
        $navtab.append("<li><a class=\"btn\" id=\""+data[$item]['name']+"\" ><i class=\"material-icons\">"+data[$item]['icon']+"</i></a></li>"); 
      }    
    }
}
function onchoice(data)
{
    var curinp = $("input.form-value");
    curinp.val(data['name']); 
    curinp.attr('it',data['id']); 
    console.log(data);
}
$('a').on('show.bs.tab', function (e) {
    $('div.ivalue-block').hide();
    var $action = $("input[name='action']").val(); 
    var $activeid = $(e.target).attr('href').substring(1);
    if ($activeid!='entityhead')
    {   
        if ($action=='EDIT')
        {
            $("input[name='action']").val('SET_EDIT'); 
        }    
        else if ($action=='VIEW')
        {
            $("input[name='action']").val('SET_VIEW'); 
        }    
        var x = $('div.ivalue-block');
        
        $(x).find('form').remove();
        $(x).find('.form-value').remove();
        $(x).hide();
        $("input[name='curid']").val($activeid); 
        $("input[name='command']").val('load'); 
        $data = $('.row input').serializeArray();
        $.ajax(
        {
            url: '/common/post_ajax.php',
            type: 'post',
            dataType: 'json',
            data: $data,
            success: function(result) {
                onLoadSet(result);
            }
        });      
    }
    else
    {
        $("input[name='curid']").val(''); 
        if ($action=='SET_EDIT')
        {
            $("input[name='action']").val('EDIT'); 
        }    
        else if ($action=='SET_VIEW')
        {
            $("input[name='action']").val('VIEW'); 
        }    
        $.getJSON(
            '/common/get_ajax.php',
            {action:$("input[name='action']").val(), id:$("input[name='itemid']").val(),mode:$("input[name='mode']").val(),command:'actionlist',prefix:'get'},
            function(data){
                actionlist(data['items']);
            }          
        );
    }    
});

function onGetData(data)
{
    $.each(data.items, function(key, val) 
    {
        if (val.id)
        {    
            $("input#"+val.id).val(val.name);
            exd++;
        }
    });    
}
    
function getprefix()
{
    var mode = $("input[name='mode']").val();    
    if (mode=='CONFIG')
    {
        return "/"+mode+"/";
    }
    else    
    {
        return "/";
    }    
}
function onloadlist(data)
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len=0;
    $mh.find("tr").remove();
    $mh.find("th").remove();
    $mt.find("tr").remove();
    $mt.find("td").remove();
    
    $mh.append("<tr>");
    for(var cid in data['PSET'])
    {
        cls = data['PSET'][cid]['class'];
        $mh.find('tr').append("<th class=\""+cls+"\" id=\""+cid+"\">"+data['PSET'][cid]['synonym']+"</th>");    
    }
    $mh.append("</tr>");
    if (Object.keys(data).length) 
    {
        if ('LDATA' in data)
        {    
            for(var id in data['LDATA'])
            {
                $mt.append("<tr class=\"active\" st=\""+data['LDATA'][id].class+"\" id=\""+id+"\">");
                for(var cid in data['PSET'])
                {
                    cls = data['PSET'][cid]['class'];
                    if (cid in data['LDATA'][id])
                    {    
                        var dname = data['LDATA'][id][cid]['name'];
                        if (data['LDATA'][id].class=='erased')
                        {
                            dname = "<del>"+dname+"</del>";
                        }    
                        var did = data['LDATA'][id][cid]['id'];
                        $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\""+did+"\" vt=\""+data['PSET'][cid]['type']+"\">"+dname+"</td>");    
                    }
                    else
                    {
                        $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\"\" vt=\""+data['PSET'][cid]['type']+"\"></td>");    
                    }    
                }
                $mt.append("</tr>");
            }
        }    
    }
    $(".modal-title").text('Выбор из списка');
    $('body').one('click', '#tzModalOK', function () {
        $('#tzModal').modal('hide');
    });
    $('#tzModal').modal('show');
    console.log(data);
}
function onLoadValID(data)
{
    var cls;
    var $action = $("input[name='action']").val();
    if ('SDATA' in data)
    {    
        for(var id in data['SDATA'])
        {
            $("input.form-control[id='id']").val(id);
            for(var cid in data['PLIST'])
            {
                if (cid in data['SDATA'][id])
                {    
                    var did = data['SDATA'][id][cid]['id'];
                    var dname = data['SDATA'][id][cid]['name'];
                    if (did!='')
                    {    
                        $("input.form-control[id=name_"+cid+"]").val(dname);
                        dname = did;
                    }    
                    $("input.form-control[id="+cid+"]").val(dname);
                }    
                if ($action==='VIEW')
                {
                    $("input.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    if ((data['PLIST'][cid]['type']=='id')||(data['PLIST'][cid]['type']=='cid')||(data['PLIST'][cid]['type']=='mdid')||(data['PLIST'][cid]['type']=='propid'))
                    {    
                        $("input.form-control[id=name_"+cid+"]").attr('readonly', 'readonly');
                    }    
                    else if (data['PLIST'][cid]['type']=='text')
                    {    
                        $("textarea.form-control[id="+cid+"]").attr('readonly', 'readonly');
                    }    
                }    
            }
        }
    }    
    if ('LDATA' in data)
    {    
        $("tbody#entitylist tr").remove();
        for(var id in data['LDATA'])
        {
            $("tbody#entitylist").append("<tr class=\"active\" st=\""+data['LDATA'][id].class+"\" id=\""+id+"\">");
            for(var cid in data['PSET'])
            {
                cls = data['PSET'][cid]['class'];
                if (cid in data['LDATA'][id])
                {    
                    var dname = data['LDATA'][id][cid]['name'];
                    if (data['LDATA'][id].class=='erased')
                    {
                        dname = "<del>"+dname+"</del>";
                    }    
                    var did = data['LDATA'][id][cid]['id'];
                    $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\""+did+"\" vt=\""+data['PSET'][cid]['type']+"\">"+dname+"</td>");    
                }
                else
                {
                    $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\"\" vt=\""+data['PSET'][cid]['type']+"\"></td>");    
                }    
            }
            $("tbody#entitylist").append("</tr>");
        }
    }    
    actionlist(data['actionlist']);
}
function onLoadSet(data) 
{
    var $action = $("input[name='action']").val(); 
    var $activeid = data['ITEMID'];
    var $obj = $("#"+$activeid).find("tbody#entitylist");
    $obj.find("tr").remove();
    for(var id in data['LDATA'])
    {
        $obj.append("<tr id=\""+id+"\" class=\"active\" st=\""+data['LDATA'][id].class+"\">");
        for(var cid in data['PSET'])
        {
            cls = data['PSET'][cid]['class'];
            if (cid in data['LDATA'][id]){
                var dname = data['LDATA'][id][cid]['name'];
                if (data['LDATA'][id].class=='erased')
                {
                    dname = "<del>"+dname+"</del>";
                }    
                var did = data['LDATA'][id][cid]['id'];
                $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\""+did+"\" vt=\""+data['PSET'][cid]['type']+"\">"+dname+"</td>");    
            }
            else
            {
                $obj.find("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\"\" vt=\""+data['PSET'][cid]['type']+"\"></td>");
            }    
        }
        $obj.append("</tr>");
    }
    actionlist(data['actionlist']);
}
function onLoadGetData(data) {
    var curinp = $(".row input[st='info']");
    var curname = curinp.attr('name');
    var curid = $(curinp).attr('id');
    var exd = 0;
    $("#"+curid+"~.types_list").find('li').remove();
    $.each(data.items, function(key, val) 
    {
        if (val.id)
        {    
            $("#"+curid+"~.types_list").append('<li id='+val.id+' class="active">'+val.name+'</li>');
            exd++;
        }
    }    
    );
    $("#"+curid+"~.types_list").slideToggle('fast');
};
$('body').keyup(function(eventObject) { 
    if (eventObject.which==27) { 
        $(".types_list").slideUp('fast');
    }
});
$('input.form-control').keyup(function(eventObject) { 
    var $action = $("input[name='action']").val();
    if ($action==='VIEW')
    {
        return;
    }
    var itype = $(this).attr("it");
    var curid = this.id;
    var curinp = $(".row input[st='info']");
    if (curinp!=this)
    {
        $(curinp).attr('st','active');
        $(this).attr('st','info');
    }    
    if (eventObject.which==27) 
    { 
        $("#"+curid+"~.types_list").slideUp('fast');
    }
    else 
    {
        var $data = {action:$action, id:$(this).attr("vt"), type:itype, name:$(this).val(), command:'find', prefix:'field'};
        if (curid=='name_valmdid')
        {    
            var $curtype = $("input#type");
            $data = {action:$action, id:$("input#valmdid").val(),type: 'mdid',name:$(this).val(),'command':'find', prefix:'field'};
            itype = $curtype.val();
        }
        if ((itype=='id')||(itype=='cid')||(itype=='mdid')||(itype=='propid'))
        {
            if (itype=='propid')
            {    
                $("input[name='curid']").val(curid);
                $("input[name='command']").val('find');
                $data = $('.row input').serializeArray();
            }
            $("#"+curid+"~.types_list").slideUp('fast'); 
            if ($(this).val().length>1) 
            {
               $.getJSON(
                    '/common/get_ajax.php',
                    $data,
                    onLoadGetData
                );
            }
            else
            {
                if ($(this).val().length==0) 
                {
                    var curname = curinp.attr('name');
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
$('input.form-control[it=bool]').dblclick(function(e) { 
    e.preventDefault();
    $action = $("input[name='action']").val();
    if (($action==='EDIT')||($action==='CREATE'))
    {    
        var curid = this.id;
        $(".types_list").slideUp('fast'); 
        $("#"+curid+"~.types_list").slideToggle('fast');
    }    
});
$('body').on('dblclick','#entitylist tr',function () 
{
    var $action = $("input[name='action']").val();
    var $mode = $("input[name='mode']").val();
    if ($action==='VIEW')
    {    
        location.href=getprefix()+this.id+"/view";
    }
    if ($mode==='CONFIG')
    {
        location.href=getprefix()+this.id+"/edit";
    }
});
$('body').on('dblclick','#modallist tr',function (e) 
{
    e.preventDefault();
    $("input[name='curid']").val(this.id); 
    $("input[name='command']").val('choice'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
        success: onchoice
    });  
    $('#tzModal').modal('hide');
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

function submitModalForm(e)
{
    e.preventDefault();
    var x = $('div.ivalue-block');
    var $ci = $(x).find('input');
    var action = $("input[name='action']").val();
    var $curcol = $('#tablehead th.info');
    var propid = $curcol.attr('id');
    var $currow = $('#entitylist tr.info');
    var etd = $currow.find('td#'+propid);
    var cnm = $ci.val();
    var cid = $ci.attr('it');
    var typ = $ci.attr('type');
    etd.html(cnm);
    etd.attr('it',cid);
    $(x).hide();
    if (typ=='file')
    {
        var $photos = $('#tzFileInput'),
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
         '/common/get_ajax.php',
         $data

     );
}

$('body').on('dblclick','#entitylist td',function () 
{
    var $action = $("input[name='action']").val();
    var $mode = $("input[name='mode']").val();
    if ($mode==='CONFIG')
    {
        return;
    }
    if ($(this).parent().attr('st')=='erased')
    {
        return;
    }
    var etd = $(this);
    var it = $(this).attr('it');
    var vt = $(this).attr('vt');
    var dname = $(this).html();
    if (($action!=='SET_EDIT')&&($action!=='SET_VIEW'))
    {
        return;
    }
    if ($action==='SET_VIEW')
    {
        if (vt!='file')
        {
            return;
        }    
        if (dname=='')
        {
            return;
        }    
        
    }    
    var tdwidth = $(this).width();
    var x = $('div.ivalue-block');
    $(x).find('form').remove();
    $(x).find('.form-value').remove();
    var bwidth = 0;
    var ov = dname;
    var itype='text';
    if (vt=='date')
    {
        itype='datetime';
    }
    else if (vt=='int')
    {
        itype='number';
    }

    if ((vt=='id')||(vt=='cid')||(vt=='mdid')||(vt=='propid'))
    {
        ov = it;
        $(x).append("<form name=\"ivalue\"><input type=\""+itype+"\" class=\"form-value\" vt=\""+vt+"\" it=\""+it+"\" ov=\""+ov+"\" value=\""+dname+"\"><button id=\"list\" class=\"form-value\"><i class=\"material-icons\">list</i></a></button><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></form>");
        bwidth +=90; 
    }    
    else if (vt=='file')
    {
        if ($action==='SET_VIEW')
        {
            $(x).append("<form name=\"ivalue\"><a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a></form>");
        }   
        else
        {    
            if (dname=='')
            {    
                $(x).append("<form name=\"ivalue\"><input id=\"tzFileInput\" type=\"file\" accept=\"image/*;capture=camera\" class=\"form-value\" vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" value=\""+dname+"\"><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></form>");
            }    
            else
            {
                $(x).append("<form name=\"ivalue\"><a href=\""+it+"\" download=\""+dname+"\">"+dname+"</a><button id=\"delete_ivalue\" class=\"form-value\"><i class=\"material-icons\">delete</i></button></form>");
            }
        }    
    }    
    else
    {
        $(x).append("<form name=\"ivalue\"><input type=\""+itype+"\" class=\"form-value\" vt=\""+vt+"\" it=\"\" ov=\""+ov+"\" value=\""+dname+"\"><button id=\"done\" class=\"form-value\"><i class=\"material-icons\">done</i></button></form>");       
        bwidth +=50; 
    }
    if (tdwidth<120)
    {
        tdwidth = 120;
    }
    $(x).width(tdwidth);
    $(x).find('input').width(tdwidth-bwidth);
    $(x).show();
    $(x).offset({top:etd.offset().top+etd.height(),left:etd.offset().left-40});
    $("body").one('click','button.form-value#done',submitModalForm);
});   

$('body').on('click','button.form-value#list', function(e)
{
    e.preventDefault();
    var action = $("input[name='action']").val();  
    $("input[name='curid']").val($('tr.info').attr('id')); 
    $("input[name='filter_id']").val($('th.info').attr('id')); 
    $("input[name='command']").val('list'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
        success: onloadlist
    });    
});

$('body').on('click','button.form-value#delete_ivalue', function(e)
{
    e.preventDefault();
    var action = $("input[name='action']").val();
    var x = $('div.ivalue-block');
    var $ci = $(x).find('input');
    var $curcol = $('th.info');
    var propid = $curcol.attr('id');
    var $currow = $('tr.info');
    var etd = $currow.find('td#'+propid);
    var typ = $ci.attr('type');
    etd.html('');
    etd.attr('it','');
    $(x).hide();
    var $data = {action:action, propid: propid, id: '', type:typ, name:'', itemid:$currow.attr('id'), command:'save', prefix:'field'};
    $.getJSON(
         '/common/get_ajax.php',
         $data

     );
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
        $.getJSON(
             '/common/get_ajax.php',
             {action:$("input[name='action']").val(), id:lid, type:curname, name:tx, command:'get', prefix:'mdname'},
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
$('body').on('click','#modallist tr',function () 
{
  $('#modallist tr.info').attr("class","active");
  $(this).attr("class","info");
});


$("#tzTab a").click(function(e){
  e.preventDefault();
  $(this).tab('show');
});


$('body').on('click','a#create', function () 
{
    var itemid = $("input[name='itemid']").val();    
    var mode = $("input[name='mode']").val();    
    var action = $("input[name='action']").val();    
    $("input[name='command']").val('create'); 
    if (itemid!='') 
    {
        if (action=='SET_EDIT')
        {
            var curid = $("ul#tzTab").find("li.active a").attr('href').substring(1);
            $("input[name='curid']").val(curid);    
            $data = $('.row input').serializeArray();
            $.ajax(
            {
                url: '/common/post_ajax.php',
                type: 'post',
                dataType: 'json',
                data: $data,
                success: function(result) {
                    onLoadSet(result);
                }
            });
        }   
        else
        {
            var curid = $("input[name='curid']").val();    
            dop='';
            if (curid!='')
            {
                dop +='/'+curid; 
            }   
            location.href=getprefix()+itemid+"/create"+dop;
        }    
    }  
});
$('body').on('click', '#edit', function () {
    var action = $("input[name='action']").val();    
    if ((action=='EDIT')||(action=='SET_EDIT'))
    {    
        var id = $('tr.info').attr('id');
        if (id!='') 
        {
          location.href=getprefix()+id+"/edit";
        }  
    }
    else
    {
        var itemid = $("input[name='itemid']").val();    
        location.href=getprefix()+itemid+"/edit";
    }    
});
$('body').on('click', '#view', function () {
    var id = $('tr.info').attr('id');
    if (id!='') 
    {
      location.href=getprefix()+id+'/view';
    }  
});
function erase_success (result)
{
    var $itemid = $("input[name='itemid']").val(); 
    var $action = $("input[name='action']").val(); 
    $('#tzModal').modal('hide');
    location.href=getprefix()+$itemid+'/'+$action;
    console.log(result);
};
function erase() {
    var $data;
    $("input[name='command']").val('delete'); 
    $("input[name='curid']").val($('tr.info').attr('id')); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
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
    $mh.find("tr").remove();
    $mh.find("th").remove();
    $mt.find("tr").remove();
    $mt.find("td").remove();
    
    $mh.append("<tr>");
    $mh.find('tr').append("<th>Объект</th>");    
    $mh.find('tr').append("<th>Наименование</th>");    
    $mh.find('tr').append("<th>Действие</th>");    
    $mh.append("</tr>");
    
    if (Object.keys(result).length) 
    {
        for(var i in result) 
        {
            if(result.hasOwnProperty(i))
            {
                $mt.append('<tr>');
                $mt.append('<td>'+result[i].name+'</td>');
                $mt.append('<td>'+result[i].pval+'</td>');
                $mt.append('<td>'+result[i].nval+'</td>');
                $mt.append('</tr>');
                len++;
            }    
        }
    }
    if (len)
    {    
        $(".modal-title").text('Подтвердите действие');
        $('body').one('click', '#tzModalOK', erase);
    }    
    else 
    {
        $(".modal-title").text('Действие не выполнено.');
        $('body').one('click', '#tzModalOK', function () {
            $('#tzModal').modal('hide');
        });
    }    
    $('#tzModal').modal('show');
    console.log(result);
}   
$('body').on('click', '#delete', function () 
{
    var action = $("input[name='action']").val();  
    $("input[name='curid']").val($('tr.info').attr('id')); 
    $("input[name='command']").val('before_delete'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
        success: before_delete_success
    });    
});
$('body').on('click', '#filter', function (e) 
{
    var curid = $('tr.info').attr('id');
    var curcol = $('th.info').attr('id');
    e.preventDefault();
    var $data;
    var $el_cur  = $("tr#"+curid).find("td#"+curcol);
    var $el_fval = $("input[name='filter_val']");
    var $filter_val=$el_fval.val();
    var curval='';
    var $fval  = $el_cur.html();
    var $fid   = $el_cur.attr("it");
    $("input[name='filter_id']").val(curcol); 
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
    $("input[name='command']").val('load'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
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
    e.preventDefault();
    var $data;
    var $el_sort_id = $("input[name='sort_id']");
    var $el_sort_dir = $("input[name='sort_dir']");
    var $cur_sort_dir =$el_sort_dir.val();
    var $cur_sort_id =$el_sort_id.val();
    if ($cur_sort_id!=curcol)
    {
        $el_sort_id.val(curcol); 
    }
    else
    {
        if ($cur_sort_dir!='')
        {
            $el_sort_dir.val(''); 
        }
        else
        {
            $el_sort_dir.val('1'); 
        }    
    }
    $("input[name='command']").val('load'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
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
    $mh.find("tr").remove();
    $mh.find("th").remove();
    $mt.find("tr").remove();
    $mt.find("td").remove();
    
    $mh.append("<tr>");
    for(var j in result['PSET']) 
    {
        if(result['PSET'].hasOwnProperty(j))
        {
            $mh.find('tr').append("<th class=\""+result['PSET'][j].class+"\">"+result['PSET'][j].synonym+"</th>");
        }    
    }
    $mh.append("</tr>");
    if (Object.keys(result['LDATA']).length) 
    {
        for(var i in result['LDATA']) 
        {    
            $mt.append('<tr>');
            for(var j in result['PSET']) 
            {
                if(result['PSET'].hasOwnProperty(j))
                {
                    $mt.append("<td class=\""+result['PSET'][j].class+"\">"+result['LDATA'][i][result['PSET'][j].name].name+"</td>");
                    len++;
                }    
            }
            $mt.append('</tr>');
        }    
    }
    $(".modal-title").text('История изменения реквизита: '+result['synonym']);
    $('body').one('click', '#tzModalOK', function () {
        $('#tzModal').modal('hide');
    });
    $('#tzModal').modal('show');
}

$('body').on('click', '#history', function (e)
{
    var $entityid = $("input[name='itemid']").val();
    var curinp = $(".row input[st='info']").attr('id');
    if ($entityid!='') 
    {
        if (curinp!='') 
        {
            var tcurid = curinp;  
            if (-1 < curinp.indexOf('name_')) 
            {  
                tcurid = curinp.replace('name_', '');
            }
            $("input[name='curid']").val(tcurid);
            $("input[name='command']").val('history'); 
            $data = $('.row input').serializeArray();
            $.ajax({
              url: '/common/post_ajax.php',
              type: 'post',
              dataType: 'json',
              data: $data,
                success: show_history
            });    
        }
    }    
});



function before_save() {
    var $data;
    $("input[name='command']").val('before_save'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
        success: before_save_success
    });    
};
function save() {
    var $data;
    $("input[name='command']").val('save'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
        success: save_success
    });
};
function save_success (result)
{
    $('#tzModal').modal('hide');
    location.href=getprefix()+result['id']+'/edit';
    console.log(result);
};
function before_save_success(result) 
{
    var $mt = $("#modallist");
    var $mh = $("#modalhead");
    var len=0;
    $mh.find("tr").remove();
    $mh.find("th").remove();
    $mt.find("tr").remove();
    $mt.find("td").remove();
    
    $mh.append("<tr>");
    $mh.find('tr').append("<th>Реквизит</th>");    
    $mh.find('tr').append("<th>Значение было</th>");    
    $mh.find('tr').append("<th>Новое значение</th>");    
    $mh.append("</tr>");
    if (Object.keys(result).length) 
    {
        for(var i in result) 
        {
            if(result.hasOwnProperty(i))
            {
                $mt.append('<tr>');
                $mt.append('<td>'+result[i].name+'</td>');
                $mt.append('<td>'+result[i].pval+'</td>');
                $mt.append('<td>'+result[i].nval+'</td>');
                $mt.append('</tr>');
                len++;
            }    
        }
    }
    if (len)
    {    
        $(".modal-title").text('Saving the modified data');
        $('body').one('click', '#tzModalOK', save);
    }    
    else 
    {
        $(".modal-title").text('Saving data is not required');
        $('body').one('click', '#tzModalOK', function () {
            $('#tzModal').modal('hide');
        });
    }    
    $('#tzModal').modal('show');
}   
$('body').on('click','#save',function(e) {
    var action = $("input[name='action']").val();  
    if (action==='EDIT')
    {
        before_save();  
    }    
    else
    {
        save();  
    }
});

$(document).ready(function() 
{ 
    $("input[name='command']").val('load'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/common/post_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
      success: onLoadValID
      }
    );
    if ($("input[name='action']").val()!='VIEW')
    {    
        var $input = $('.datepicker').pickadate({
                selectMonths: true,
                format: 'yyyy-mm-dd',
                formatSubmit: 'yyyy-mm-dd'
            });
    }    
    $("body").one('OnResize',function(){
        var x = $('div.ivalue-block');
        if (x!=undefined) 
        {    
            $(x).find('form').remove();
            $(x).find('.form-value').remove();
            $(x).hide();
        }
    });
});
