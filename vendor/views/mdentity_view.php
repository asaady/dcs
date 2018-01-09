<form class="form-inline" role="form">
    <?php    
    include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/modal_win_template.php";    
    ?>
    
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="id" class="control-label">ID</label>
      <?php
                        echo "<input class=\"form-control\" name=\"id\" id=\"id\" value=\"$data[id]\" readonly>\n";
      ?>            
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="name" class="control-label">NAME</label>
      <?php
                        echo "<input type=\"text\" class=\"form-control\" name=\"name\" id=\"name\" value=\"$data[name]\">";
      ?>            
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="synonym" class="control-label">SYNONYM</label>
      <?php
                        echo "<input type=\"text\" class=\"form-control\" name=\"synonym\" id=\"synonym\" value=\"$data[synonym]\">";
      ?>            
                </div>    
            </div>    
    </div>        
</form>        
        
<table class="table table-border table-hover">
    <thead>
      <tr>
<?php
        foreach($data['PSET'] as $key=>$val)
        {    
            $ishide='';
            if ($arResult['MODE']!=='CONFIG')
            {
                if ($key=='id'){
                    $ishide=' hidden';
                }elseif (strtolower($val['name'])=='activity') {
                     $ishide=' hidden';    
                }
            }
            echo "<th class=\"$key$ishide active\" id=\"$key\">$val[synonym]</th>";
        }
?>
      </tr>

    </thead> 
      <tbody id="entitylist" class="list">
      </tbody>
</table>
