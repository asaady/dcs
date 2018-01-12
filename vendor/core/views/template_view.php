<!DOCTYPE html>
<html lang="ru">
    self::header_view($context);
<body data-spy="scroll" data-target=".navbar" data-offset="50">
    <header>
        <?php
        self::out_navbar($context,$data);
        ?>                        
    </header>    
    <main>
        self::context_view($context,$data);
        <div id="ivalue" class="input-group"></div>
        <div id="form_result"></div>
        <?php
        self::modal_view();    
        ?>
        <div id="loader">
            <img  style="display: none;" width="10" height="10" alt="loading" src="data:image/gif;base64,R0lGODlhEAAQAPIAAP///zqHrc/h6mylwjqHrYW0zJ7D1qrL2yH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==" />
        </div>    
    </main>    
    <footer>
        <div class="container-fluid">
            <div class="row">
                <a href="/">Copyright &copy; <?=DCS_COMPANY_NAME?> 2017.</a>
            </div>    
        </div>
    </footer>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="/public/js/jquery-3.2.1.min.js"></script>
        <script src="/public/js/bootstrap.min.js"></script>    
        <script src="/public/js/moment.js"></script>
<?php
        if (($context['ACTION']=='EDIT')||($context['ACTION']=='CREATE'))
        {
            echo "<script src=\"/public/js/picker.js\"></script>";
            echo "<script src=\"/public/js/picker.date.js\"></script>";
            echo "<script src=\"/public/js/picker.time.js\"></script>";
        }
?>
        <script type="text/javascript">
        //<![CDATA[
        function logout()
        {
            $("input[name='command']").val('logout');
            $("input[name='mode']").val('AUTH'); 
            var data = $('.row :input').serializeArray();
            $.ajax(
            {
                url: '/common/post_ajax.php',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(result) {
                    location.href=result['redirect'];
                }
            })      
        };

<?php
        if (($context['ACTION']=='EDIT')||($context['ACTION']=='CREATE'))
        {
            echo "$('input.form-control[it=date]').pickadate({
                    selectMonths: true,
                    format: 'yyyy-mm-dd',
                    formatSubmit: 'yyyy-mm-dd'
                  });";
        }        
        if (array_key_exists('jscript', $context))
        {
            include $context['jscript'];
        }        
        else
        {
            include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/public/js/core_app.js";
        }    
?>
        </script>
        
    </body>
</html>