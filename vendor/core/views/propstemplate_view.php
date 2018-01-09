<div class="col-xs-12 col-sm-12 col-md-12">    
<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="id" class="control-label">ID</label>
      <?php
                        echo "<input type=\"id\" class=\"form-control\" id=\"id\" name=\"id\" value=\"$data[id]\" readonly>\n";
      ?>            
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="name" class="control-label">NAME</label>
<?php
                        echo "<input type=\"name\" class=\"form-control\" id=\"name\" name=\"name\" value=\"$data[name]\">";
?>            
                        <ul class="types_list">
                            <li id=""></li>
                        </ul>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="synonym" class="control-label">SYNONYM</label>
      <?php
                        echo "<input type=\"synonym\" class=\"form-control\" id=\"synonym\" name=\"synonym\" value=\"$data[synonym]\">\n";
      ?>            
                </div>    
            </div>    
    </div>        
</form>    
    
<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="type" class="control-label">VAL TYPE</label>
      <?php
                        echo "<input type=\"type\" class=\"form-control\" id=\"type\" name=\"type\" value=\"$data[type]\" readonly>\n";
      ?>            
                        <ul class="types_list">
                            <li id="int">int</li>
                            <li id="float">float</li>
                            <li id="str">str</li>
                            <li id="date">date</li>
                            <li id="bool">bool</li>
                            <li id="blob">blob</li>
                            <li id="id">id</li>
                            <li id="file">file</li>
                        </ul>
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="length" class="control-label">VAL LENGTH</label>
<?php
                        echo "<input type=\"length\" class=\"form-control\" id=\"length\" name=\"length\" value=\"$data[length]\">";
?>            
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="prec" class="control-label">VAL PREC</label>
      <?php
                        echo "<input type=\"prec\" class=\"form-control\" id=\"prec\" name=\"prec\" value=\"$data[prec]\">\n";
      ?>            
                </div>    
            </div>    
    </div>        
</form>    

<form class="form-inline" role="form">
    <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="valmdid" class="control-label">VAL MD ID</label>
      <?php
                        echo "<input type=\"valmdid\" class=\"form-control\" id=\"valmdid\" name=\"valmdid\" value=\"$data[valmdid]\" readonly>\n";
      ?>            
                </div>    
            </div>    
            <div class="col-xs-12 col-sm-4 col-md-4">
                <div class="form-group">
                    <label for="valmdname" class="control-label">VAL MD NAME</label>
<?php
                        echo "<input type=\"valmdname\" class=\"form-control\" id=\"valmdname\" value=\"$data[valmdname]\">";
?>            
                        <ul class="types_list">
                            <li></li>
                        </ul>
                </div>    
            </div>    
    </div>        
</form>    
</div>

