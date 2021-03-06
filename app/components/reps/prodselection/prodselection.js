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
    var $mt = $(".modal-body").find("tbody");
    var $mh = $(".modal-body").find("thead");
    var len=0;
    $mh.find("tr").remove();
    $mh.find("th").remove();
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
                $mt.append("<tr class=\"active\" id=\""+id+"\">");
                for(var cid in data['PSET'])
                {
                    cls = data['PSET'][cid]['class'];
                    if (cid in data['LDATA'][id])
                    {    
                        var dname = data['LDATA'][id][cid]['name'];
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
    $('body').one('click', '#tzModalOK', function () {
        $('#tzModal').modal('hide');
    });
    $('#tzModal').modal('show');
    console.log(data);
}
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
        var icount = 0;
        for(var id in data['LDATA'])
        {
            icount += 1;
            $("tbody#entitylist").append("<tr class=\"active\" id=\""+id+"\">");
            for(var cid in data['PSET'])
            {
                cls = data['PSET'][cid]['class'];
                if (cid in data['LDATA'][id])
                {    
                    var dname = data['LDATA'][id][cid]['name'];
                    var did = data['LDATA'][id][cid]['id'];
                    $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\""+did+"\" vt=\""+data['PSET'][cid]['type']+"\">"+dname+"</td>");    
                }
                else
                {
                    if (cid=='num')
                    {
                        $("tr#"+id).append("<td class=\"active\" id=\"num\" it=\"\" vt=\"int\">"+icount.toString()+"</td>");    
                    }   
                    else
                    {
                        $("tr#"+id).append("<td class=\""+cls+"\" id=\""+cid+"\" it=\"\" vt=\""+data['PSET'][cid]['type']+"\"></td>");    
                    }    
                }    
            }
            $("tbody#entitylist").append("</tr>");
        }
    }    
    actionlist(data['actionlist']);
}
function onLoadGetData(data) {
    var curinp = $(".row input[st='info']");
    var curid = $(curinp).attr('id');
    var exd = 0;
    $("#"+curid+"~.types_list").find('li').remove();
    console.log(data.items);
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
        var $data = {action:'EDIT', id:$(this).attr("vt"), type:itype, name:$(this).val(), command:'find', prefix:'field'};
        $("#"+curid+"~.types_list").slideUp('fast'); 
        if ($(this).val().length>1) 
        {
           $.getJSON(
                '/common/get_ajax.php',
                $data,
                onLoadGetData
            );
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
    $action = $("input[name='action']").val();
    var curid = this.id;
    $(".types_list").slideUp('fast'); 
    $("#"+curid+"~.types_list").slideToggle('fast');
});
$('body').on('dblclick','#entitylist tr',function () 
{
    var $itemid = $("input[name='itemid']").val();
    var $action = $("input[name='action']").val();
    var $curcol = $('th.info').attr('id');
    var href='';
    if ($action=='CoverSheets')
    {
        if ($curcol=='docid')
        {    
            var $curid = $(this).find('td#docid').attr('it')
            if ($curid!='')
            {    
                href="\\"+$curid+"\\view";
            }    
        }    
    }   
    else
    {
        $mindate = $("input[name='mindate']").val().substring(0,10);
        href="\\"+$itemid+"\\"+this.id+"\\"+$mindate;
    }    
    if (href!='')
    {
        var otherWindow = window.open(href,"_blank");
        otherWindow.opener = null;
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
             {action:'EDIT', id:lid, type:curname, name:tx, command:'get', prefix:'mdname'},
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
$('body').on('click', '#print', function (e) 
{
    var $itemid = $("input[name='itemid']").val();
    var str="";
    var href='';
    e.preventDefault();
    $('.form-group input').each(
        function()
        {
            if (this.value!='')
            {
                if ((this.name.indexOf('name_') + 1)==0)
                {    
                    str+='\\'+this.value;
                }    
            }    
        }
    );
    if (str!='')
    {
        href="\\print\\"+$itemid+str;
        var otherWindow = window.open(href,"_blank");
        otherWindow.opener = null;
    }    
});

$('body').on('click', '#build', function (e) 
{
    e.preventDefault();
    var $data;
    $("input[name='curid']").val($("input[name='parameter']").val()); 
    $("input[name='command']").val('load'); 
    $data = $('.row input').serializeArray();
    $.ajax({
      url: '/app/components/reps/prodselection/prodselection_ajax.php',
      type: 'post',
      dataType: 'json',
      data: $data,
      success: function(result) {
            onLoadValID(result);
        }  
      }
    );
});
$(document).ready(function() 
{ 
    var filter_val = $("input[name='filter_val']").val();
    $("input[name='doc2']").val(filter_val);
    var curid = $("input[name='curid']").val();
    if (curid!='')
    {
        $("input[name='doc1']").val(curid); 
        $("input[name='command']").val('load'); 
        $data = $('.row input').serializeArray();
        $.ajax({
          url: '/app/components/reps/prodselection/prodselection_ajax.php',
          type: 'post',
          dataType: 'json',
          data: $data,
          success: function(result) {
                onLoadValID(result);
            }  
          }
        );
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

