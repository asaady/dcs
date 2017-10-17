<div class="col-xs-12 col-sm-12 col-md-12">  
<?php    
include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/modal_win_template.php";    
?>
<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="id" class="control-label">ID</label>
                    <input type="text" class="form-control" id="id" name="id" valid="" vt="" value="" readonly>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="name" class="control-label">NAME</label>
                        <input type="text" class="form-control" id="name" name="name" value="">
                        <ul class="types_list">
                            <li id=""></li>
                        </ul>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="synonym" class="control-label">SYNONYM</label>
                    <input type="text" class="form-control" id="synonym" name="synonym" valid="" vt="" value="">
                </div>    
            </div>    
    </div>        
</form>    
        
<form class="form-inline" role="form">
    <div class="row">
        <div class="col-xs-12 col-sm-4 col-md-4">
            <div class="form-group">
                <label for="rank" class="control-label">RANK</label>
                <input type="number" class="form-control" id="rank" name="rank" valid="" vt="" value="">
            </div>    
        </div>    
        <div class="col-xs-12 col-sm-4 col-md-4">
            <div class="form-group">
                <label for="ranktoset" class="control-label">RANK TO SET</label>
                <input type="number" class="form-control" id="ranktoset" name="ranktoset" valid="" vt="" value="">
            </div>    
        </div>    
        <div class="col-xs-12 col-sm-4 col-md-4">
            <div class="form-group">
                <label for="type" class="control-label">VAL TYPE</label>
                    <input type="text" class="form-control" id="type" name="type" value="" readonly>
                    <ul class="types_list">
                        <li id="str">str</li>
                        <li id="bool">bool</li>
                        <li id="int">int</li>
                        <li id="float">float</li>
                        <li id="date">date</li>
                        <li id="text">text</li>
                        <li id="id">id</li>
                        <li id="cid">cid</li>
                        <li id="mdid">mdid</li>
                        <li id="propid">propid</li>
                        <li id="file">file</li>
                    </ul>
            </div>    
        </div>    
    </div>    
</form>        
<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="length" class="control-label">LENGTH</label>
                    <input type="number" class="form-control" id="length" name="length" value="">
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="prec" class="control-label">PREC</label>
                    <input type="number" class="form-control" id="prec" name="prec" value="">
                </div>    
            </div>    
    </div>        
</form>        
        

<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="valmdid" class="control-label">VAL MD ID</label>
                    <input type="text" class="form-control" id="valmdid"  name="valmdid" value="" readonly>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="valmdname" class="control-label">VAL MD NAME</label>
                        <input type="text" class="form-control" id="name_valmdid"  name="name_valmdid" value="">
                        <ul class="types_list">
                            <li></li>
                        </ul>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="valmdtypename" class="control-label">VAL MD TYPE</label>
                    <input type="text" class="form-control" id="valmdtypename"  name="valmdtypename" value="" readonly>
                </div>    
            </div>    
    </div>        
</form>        
</div>

