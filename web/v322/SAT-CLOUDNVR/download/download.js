(function ($, _) {
    $.fn.showAlert = function(message) {
        if (!message) {
            message = 'Error';
        }
        this.find('.alert').remove();
        var alertBar = $('<div class="alert alert-danger fade" role="alert" />');
        $('<a href="#" class="close" data-dismiss="alert">&times;</a>').appendTo(alertBar);
        $('<span />').html(message).appendTo(alertBar);
        this.prepend(alertBar);
        window.setTimeout(function() {
            alertBar.addClass('in');
        }, 0);
    }

    function downloadList(data, key) {
        var list = $('<div class="list-group" />');
        data.forEach(function(entry) {
            var url = 'http://' + entry.external_address + ':' + entry.external_port
                + entry.path.replace('vod', 'download') + '?key=' + key;
            $('<a class="list-group-item" target="_blank"/>').attr('href', url)
                .html(toLocalTime(entry.time)).appendTo(list);
        });
        return list;
    }

    $.fn.showDownload = function(data) {
        var groups = _.groupBy(data.recordings, 'name');
        var names = Object.keys(groups);
        names.sort(function(l, r) {return l.localeCompare(r) > 0;});

        var that = this;
        that.empty();
        //jinho add link, div css update to download.css
        var header ="<a name=\"topdiv\"></a><div class=\"tab\">";
        for( i=0; i<names.length; i++) {
          header += "<button class=\"active\"><a href=\"#"+names[i]+"\">"+names[i]+"</a></button>";
        }
        header +="</div>";
        $(header).appendTo(that);
        //end of jinho add link
        
        names.forEach(function(name) {
            var panel = $('<div class="panel panel-primary" />').appendTo(that);
            $('<a name="'+name+'" />').appendTo(panel); //jinho added anchor
            //$('<div class="panel-heading" />').html(name).appendTo(panel);
            //$('<div class="panel-heading" />').html("<a href='#topdiv' style='color:white'>"+name+"</a>").appendTo(panel); //jinho use name to linktop
            $('<div class="panel-heading" />').html(name+"<div style='text-align:right;float:right;'><a href='#topdiv' style='color:white'>Top</a></div>").appendTo(panel); //jinho add linktop
            panel.append(downloadList(groups[name], data.key));
        });
    }

    function toLocalTime(str) {
        var offset = new Date().getTimezoneOffset();
        var year = parseInt(str.substr(0, 4), 10);
        var month = parseInt(str.substr(4,2), 10) - 1;
        var date = parseInt(str.substr(6,2), 10);
        var hours = parseInt(str.substr(8,2), 10);
        var minutes = parseInt(str.substr(10,2), 10) - offset;
        var seconds = parseInt(str.substr(12,2), 10);
        var date = new Date(year, month, date, hours, minutes, seconds);
        return date.toLocaleString();
    }
    $(function () {
        $('form.form-signin').submit(function (evt) {
            var that = $(this);
            if (that.find('.alert').length) {
                that.find('.alert').on('closed.bs.alert', function () {
                    that.trigger('submit');
                });
                that.find('.alert').alert('close');
                return false;
            }
            evt.preventDefault();
            var post = that.serializeArray();
            that.find('button').button('loading');
            $.ajax('download.php', {method: 'POST', data: post, dataType: 'json'}).then(
                function success(data) {
                    that.hide();
                    $('#download').showDownload(data);
                    that.find('button').button('reset');
                },
                function fail(xhr) {
                    var message = xhr.responseText;
                    //jinho added error time
                    if (xhr.status == 401)
                      login_err++;
                    if (login_err >=5)  setTimeout(function() { window.location.reload(true); }, 0);//setTimeout(refresh, 500);
                    //jinho end force refresh                   
                    that.find('.alert-holder').showAlert(message);
                    that.find('button').button('reset');
                });
            return false;
        });
    });
} (jQuery, _));