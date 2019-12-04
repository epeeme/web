(function(cadet, $, undefined) {

  cadet.Rankings = function() {

    $('.index').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
  
        var tableTitle = 'British Fencing | ' + $('.demographics form select[name="sex"]').val();
                  
        $.ajax({
            url: 'main/c.php?m=cadet&id=getSeasonSize&' + $('.demographics form').serialize(),
        }).done(function(cols) {
            var tableHtml = '';
            var columns = ['#', 'Points', 'Firstname', 'Surname', 'YoB', 'Country', 'Club'];
            for(var c=0; c < cols; c++) { columns.push(c+1); }
            columns.push('');
  
            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });
            
            $('.loading').hide();
            
            $('.index table thead').html('<tr>' + tableHtml + '</tr>');
            
            var builtColumns = [];
            $.ajax({
                url: 'main/c.php?m=cadet&id=getSeasonResults&' + $('.demographics form').serialize(),
            }).done(function(response) {
            });
        });
    }

    $('#getRankButton').on('click', function() {
        $('.loading').show();
        $('.index').show();            
        cadet.Rankings();
    });
}(window.cadet = window.cadet || {}, jQuery));
