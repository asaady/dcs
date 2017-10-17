<?php
    if ($arResult['MODE']=='CONFIG')
    {
echo "<form class=\"form-inline\" role=\"form\">";
echo "  <div class=\"row\">";
echo "      <div class=\"col-xs-12 col-sm-4 col-md-4\">";
echo "          <div class=\"form-group\">";
echo "              <label for=\"id\" class=\"control-label\">ID</label>";
echo "              <input class=\"form-control\" name=\"id\" id=\"id\" value=\"$data[id]\" readonly>\n";
echo "          </div>";
echo "      </div>";   
echo "      <div class=\"col-xs-12 col-sm-4 col-md-4\">";
echo "          <div class=\"form-group\">";
echo "              <label for=\"name\" class=\"control-label\">NAME</label>";
echo "              <input type=\"text\" class=\"form-control\" name=\"name\" id=\"name\" value=\"$data[name]\">";
echo "          </div>";
echo "      </div>";   
echo "      <div class=\"col-xs-12 col-sm-4 col-md-4\">";
echo "          <div class=\"form-group\">";
echo "              <label for=\"synonym\" class=\"control-label\">SYNONYM</label>";
echo "              <input type=\"text\" class=\"form-control\" name=\"synonym\" id=\"synonym\" value=\"$data[synonym]\">";
echo "          </div>";
echo "      </div>";   
echo "  </div>";   
echo "</form>";
    }    
?>    
<table class="table table-border table-hover">
    <thead>
      <tr>
<?php
        foreach($data['PSET'] as $key=>$val)
        {    
            $cls = $val['class'];
            echo "<th class=\"$cls active\" id=\"$key\">$val[synonym]</th>";
        }
?>
      </tr>

    </thead> 
      <tbody id="entitylist" class="list">
      </tbody>
</table>

   

  