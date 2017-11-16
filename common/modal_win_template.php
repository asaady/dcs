    <div id="tzModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="tzModalLabel">Saving the modified data</h4>
                </div>
                <div class="modal-body">
                  <table class="table table-border">
                      <caption></caption>
                      <thead id="modalhead">
                        <tr>
                          <th id="name">Props</th><th id="prev">Prev.value</th><th id="value">new value</th>
                        </tr>
                      </thead> 
                        <tbody id="modallist">
                            <tr></tr>
                        </tbody>
                  </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                      <button type="button" id="tzModalOK" class="btn btn-primary">OK</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
