// Chart
var chartRels = Array();

$(document).ready(docLoad);

function docLoad()
{
    $('.datepicker').mtDatepicker(false);
    $('.daterange').mtDatepicker(true);

    /** Country, State & Town for Venues Form **/

    $('select#countries_id').on('change', function(e) {
        var countryId = $(this).val();

        $.ajax({
            type: 'get',
            url: path + '/api/cities/' + countryId + '/',
            data: '',
            dataType: 'json',
            success: function(data) {
                html = '';

                html += '<option value="0">Choose City</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }

                $('select#city_id').html(html);

                html = '<option value="0">-----</option>';
                $('select#zone_id').html(html);
            }
        });
    });

    $('select#city_id').on('change', function(e) {
        var cityId = $(this).val();

        $.ajax({
            type: 'get',
            url: path + '/api/zones/' + cityId + '/',
            data: '',
            dataType: 'json',
            success: function(data) {
                html = '';

                html += '<option value="0">Choose Zone</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }

                $('select#zone_id').html(html);
            }
        });
    });

    $('select#Services__services_categories_id').on('change', function(e) {
        var categoryId = $(this).val();

        $.ajax({
            type: 'get',
            url: path + '/api/subcategories/' + categoryId + '/',
            data: '',
            dataType: 'json',
            success: function(data) {
                html = '';

                html += '<option value="0">Choose Subcategory</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }

                $('select#Services__services_subcategories_id').html(html);

                html = '<option value="0">-----</option>';
                $('select#services_id').html(html);
            }
        });
    });

    $('select#Services__services_subcategories_id').on('change', function(e) {
        var subcategoryId = $(this).val();

        $.ajax({
            type: 'get',
            url: path + '/api/services/' + subcategoryId + '/',
            data: '',
            dataType: 'json',
            success: function(data) {
                html = '';

                html += '<option value="0">Choose Service</option>';
                for (var i = 0; i < data.length; i++) {
                    html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }

                $('select#services_id').html(html);
            }
        });
    });

    $('select#type_task').on('change', function(e) {
        var type = $(this).val();

        $.ajax({
            type: 'get',
            url: path + '/api/taskTypeOptions/' + type + '/',
            data: '',
            dataType: 'json',
            success: function(data) {
                html = '';

                html += '<option value="">-</option>';
                for (var i = 0; i < data.detail.length; i++) {
                    html += '<option value="' + data.detail[i] + '">' + data.detail[i] + '</option>';
                }

                $('select#detail_task').html(html);

                html = '';

                html += '<option value="">-</option>';
                for (var i = 0; i < data.result.length; i++) {
                    html += '<option value="' + data.result[i] + '">' + data.result[i] + '</option>';
                }

                $('select#result_task').html(html);
            }
        });
    });

    $('.apirequest').on('click', function(e) {
        e.preventDefault();
        $(this).attr('disabled', 'disabled');
        
        var endpoint = $(this).data('endpoint');
        var method = $(this).data('method');
        var data = $(this).data('data');
        var confirm = $(this).data('confirm');
        
        apiRequest(endpoint, method, data);
    });

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    if (typeof eventsUpdate !== 'undefined') {
        setEventsTimer();
        checkNotifications();
    }

    $('table#Bills caption').html('Bills (<a href="javascript: toogleValidBills();">Show/Hide Valid</a>)');
    toogleValidBills();

    if (typeof matillionUpdate !== 'undefined') {
        setMatillionTimer();
    }
}

function doSubmit(id, showAlert)
{
    if (showAlert) {
        if (confirm('Are you sure? Confirm!')) {
            $('#' + id).submit();
            
            return true;
        }
    } else {
        $('#' + id).submit();
        
        return true;
    }
}

function adminAction(action, conf)
{
    var conf = (conf != null) ? conf : true;

    var result = (conf) ? confirm('Are you sure? Confirm!') : true;
    console.log(result);
    
    if (result) {
        $('#f_' + action).submit();
        
        return true;
    }

    return false;
}

function adminActionData(action, name, def)
{
    var name = (name != null) ? name : 'Data';
    var def = (def != null) ? def : '';
    
    var data = prompt('Enter ' + name, def);
    console.log(data);

    if (data !== null) {
        $('#d_' + action).val(data);
        $('#f_' + action).submit();
        
        return true;
    }

    return false;
}

function drawChart(rel, push)
{
    if (push) {
        chartRels.push(rel);
    } else {
        chartRels = [rel];
    }

    console.log(chartRels);

    var labels = Array();
    var tdsLabels = $('tr[rel="headers"]').eq(0).find('th');
    tdsLabels.each(function() {
        labels.push($(this).attr('title'));
    });
    labels.splice(0, 2);

    console.log(labels);

    var chartData = Array();
    for(var i = 0; i < chartRels.length; i++) {
        var data = Array();
        var tdsData = $('tr[rel="' + chartRels[i] + '"]').eq(0).find('td');
        tdsData.each(function() {
            var value = $(this).text();
            value = value.replace('€', '');
            value = value.replace('%', '');
            value = value.replace('.', '');
            value = value.replace(',', '.');
            value = (!isNaN(value)) ? parseInt(value) : 0;
            data.push(value);
        });
        data.splice(0, 2);
        chartData.push(data);
    }

    console.log(chartData);

    var source = Array();
    var sourceNames = Array('Items');
    for(var i = 0; i < chartRels.length; i++) {
        sourceNames.push(chartRels[i]);
    }
    source.push(sourceNames);
    for(var i = 0; i < labels.length; i++) {
        var sourceData = Array(labels[i]);
        for(var j = 0; j < chartData.length; j++) {
            sourceData.push(chartData[j][i]);
        }
        source.push(sourceData);
    }

    console.log(source);
    
    var cData = google.visualization.arrayToDataTable(source);
    var cOptions = {
        width: 1000,
        height: 300,
        legend: 'top',
        pointSize: 5,
        vAxis: {viewWindowMode: 'pretty'},
        hAxis: {viewWindowMode: 'pretty', showTextEvery: 1, slantedText: true, slantedTextAngle: 45}
    };

    console.log(cOptions);

    $('#chart').empty();
    
    var chart = new google.visualization.LineChart(document.getElementById('chart'));
    chart.draw(cData, cOptions);

    $('.chart_add').show();

    window.location = '#chart';
}

function exportDashboard(dashboard)
{
    var paramsPos = window.location.href.indexOf('?') + 1;
    var data = window.location.href.substr(paramsPos);

    $.ajax({
        type: 'get',
        url: path + '/api/dashboard/' + dashboard + '/',
        data: data,
        dataType: 'json',
        success: function(data) {
            $('#dialog').dialog('close');
            window.location = path + '/export/?name=' + data.filename;
        },
        beforeSend: function() {
            $('#dialog').dialog({
                modal: true,
                title: 'Loading...',
                open: function() {
                    $(this).html('<div class="loading">&nbsp;</div>');
                }
            });
        }
    });
}

function scrollUp()
{
    $('html, body').animate({scrollTop: 0}, 1000);
}

function scrollDown()
{
    $('html, body').animate({scrollTop: $(document).height()}, 1000);
}

function titleCase(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function strDiff(item, time)
{
    var secs = time - item;
    
    var days = Math.floor(secs / 60 / 60 / 24);
    var hours = Math.floor((secs - (days * 60 * 60 * 24)) / 60 / 60);
    var mins = Math.floor((secs - (days * 60 * 60 * 24) - (hours * 60 * 60)) / 60);

    var sDiff = '';
    if (days > 0) {
        sDiff += (days > 1) ? days + ' days' : '1 day';
    } else if (hours > 0) {
        sDiff += (hours > 1) ? hours + ' hours' : '1 hour';
    } else if (mins > 0) {
        sDiff += (mins > 1) ? mins + ' mins' : '1 min';
    } else {
        sDiff = 'seconds';
    }
    sDiff += ' ago';

    return sDiff;
}

function setEventsTimer(s)
{
    clearInterval(eventsTimer);

    if (s != null) {
        eventsReload = s * 1000;
    }

    switch (eventsService) {
        case 'leads':
            var serviceFn = reloadEventsLeads;
            break;
        default:
            var serviceFn = reloadEvents;
    }

    if (eventsReload > 0) {
        eventsTimer = setInterval(serviceFn, eventsReload);
    }

    if (s != null) {
        serviceFn();
    } else {
        setInterval(calcEvents, 60000);
    }
}

function setEventsAlert(value)
{
    eventsAlert = value;

    switch (eventsService) {
        case 'leads':
            return reloadEventsLeads();
        default:
            return reloadEvents();
    }
}

function reloadEvents(force)
{
    var update = force ? 1 : eventsUpdate;
    var params = {
        'country': $('#country').val(),
        'related': $('#related').val(),
        'rep': $('#rep').val(),
        'count': $('#count').val(),
        'reload': $('#reload').val()
    };

    $.ajax( {
        type: 'post',
        url: path + '/api/events/',
        data: 'update=' + update + '&' + $.param(params),
        dataType: 'json',
        success: function(data) {
            eventsUpdate = data.timestamp;

            var todayBody = $('#today tbody');
            if (data.today.length > 0) {
                todayBody.empty();
                var html = '';

                $.each(data.today, function(i, item) {
                    html += '<tr>';
                    html += '<td>' + titleCase(item.name) + '</td>';
                    html += '<td class="center total">' + item.all + '</td>';
                    html += '<td class="center">' + item.mx + '</td>';
                    html += '<td class="center">' + item.co + '</td>';
                    html += '<td class="center">' + item.cl + '</td>';
                    html += '<td class="center">' + item.pe + '</td>';
                    html += '<td class="center">' + item.pr + '</td>';
                    html += '</tr>';
                });

                todayBody.html(html);
            }

            var eventsBody = $('#events tbody');
            if (data.events.length > 0) {
                eventsBody.empty();
                var html = '';

                $.each(data.events, function(i, item) {
                    var trClass = (item.hl == '1') ? 'hl' : '';
                    var diff = strDiff(item.timestamp, data.timestamp);

                    html += '<tr class="' + trClass + '">';
                    html += '<td class="center" rel="' + item.timestamp + '">' + item.timestr + '<br /><span class="diff">' + diff + '</span></td>';
                    html += '<td class="center ' +  item.related + '">' + titleCase(item.related) + '<br />' + item.country + ' [<a href="' + path + '/admin/s/' + item.related + '/details/' + item.related_id + '/" target="_blank">' + item.related_id + '</a>]</td>';
                    html += '<td>' + item.title + '<br />' + item.content + '</td>';
                    html += '</tr>';
                });

                eventsBody.html(html);
            } else {
                eventsBody.find('tr').attr('class', '');
            }

            $('#timestamp').html(data.timestr);

            $('#today').effect('highlight', {}, 2000);
            $('#events').effect('highlight', {color: '#FAFFBD'}, 2000);
            $('#timestamp').effect('highlight', {}, 5000);
        }
    });
}

function reloadEventsLeads(force)
{
    var update = force ? 1 : eventsUpdate;
    var params = {
        'country': $('#country').val(),
        'rep': $('#rep').val(),
        'status': $('#status').val(),
        'count': $('#count').val(),
        'reload': $('#reload').val(),
        'alert': $('#alert').val()
    };

    $.ajax({
        type: 'post',
        url: path + '/api/events/',
        data: 'service=leads&update=' + update + '&' + $.param(params),
        dataType: 'json',
        success: function(data) {
            eventsUpdate = data.timestamp;

            var todayBody = $('#today tbody');
            if (data.today.length > 0) {
                todayBody.empty();
                var html = '';

                $.each(data.today, function(i, item) {
                    html += '<tr>';
                    html += '<td>' + item.name + '</td>';
                    html += '<td class="center total">' + item.all + '</td>';
                    html += '<td class="center">' + item.telesales + '</td>';
                    html += '<td class="center">' + item.called + '</td>';
                    html += '<td class="center">' + item.contacted + '</td>';
                    html += '<td class="center ratio">' + item.ratio + '%</td>';
                    html += '<td class="center">' + item.delay + ' mins</td>';
                    html += '</tr>';
                });

                todayBody.html(html);
            }

            var eventsBody = $('#events tbody');
            if (data.events.length > 0) {
                eventsBody.empty();
                var html = '';
                var hls = 0;

                $.each(data.events, function(i, item) {
                    var trClass = 'item';
                    if (item.hl == '1') {
                        trClass += ' hl';
                        hls++;
                    }
                    if (item.related == 'relead') {
                        trClass += ' hr';
                    } else if (item.won == 1) {
                        trClass += ' hw';
                    }
                    
                    var tdClass = 'flag';
                    if (item.related == 'relead') {
                        tdClass = ' relead';
                    } else if (item.won == 1) {
                        tdClass = ' won';
                    }
                    
                    var country = item.country != '' ? item.country.toUpperCase() : '-';

                    var type = item.type;
                    if (item.trial == 1) {
                        type += ' <img src="' + path + '/resources/img/bewe-icon.png" width="11" height="11" title="Trial" />';
                    }

                    var status = '';
                    if (item.status == 'won') {
                        status += '<span class="label label-primary">Won (' + item.lapse + ')</span>';
                    } else if (item.status == 'blacklist') {
                        status += '<a tabindex="0" role="button" class="label label-secondary" data-toggle="popover" data-placement="left" data-html="true" data-trigger="focus" title="Note" data-content="' + item.note + '">Blacklist (' + item.lapse + ')</a>';
                    } else if (item.status == 'untargeted') {
                        status += '<a tabindex="0" role="button" class="label label-secondary" data-toggle="popover" data-placement="left" data-html="true" data-trigger="focus" title="Note" data-content="' + item.note + '">Untargeted (' + item.lapse + ')</a>';
                    } else if (item.status == 'lost') {
                        status += '<a tabindex="0" role="button" class="label label-secondary" data-toggle="popover" data-placement="left" data-html="true" data-trigger="focus" title="' + item.lost_reason + '" data-content="' + item.note + '">Lost (' + item.lapse + ')</a>';
                    } else if (item.status == 'contacted' || item.status == 'qualified') {
                        status += '<a tabindex="0" role="button" class="label label-success" data-toggle="popover" data-placement="left" data-html="true" data-trigger="focus" title="Note" data-content="' + item.note + '">Contacted (' + item.lapse + ')</a>';
                    } else if (item.status == 'called') {
                        status += '<a tabindex="0" role="button" class="label label-warning" data-toggle="popover" data-placement="left" data-html="true" data-trigger="focus" title="Note" data-content="' + item.note + '">Called (' + item.lapse + ')</a>';
                    } else if (item.status == 'skip') {
                        status = '<span class="label label-info">Skip</span>';
                    } else if (item.status == 'assigned') {
                        status += '<span class="label label-danger">Waiting</span>';
                    }

                    var business = item.business;
                    if (item.business == 'Psicología' || item.business == 'Fisioterapia') {
                        business = '<span class="label label-success col">' + business + '</span>';
                    }

                    html += '<tr class="' + trClass + '">';
                    html += '<td class="center" rel="' + item.timestamp + '">' + item.timestr + '<br /><span class="diff">' + item.diff + ' ago</span></td>';
                    html += '<td class="' + tdClass + '"><img src="' + path + '/resources/img/flags/' + item.flag + '.png" width="16" height="11" title="' + country + '" /> ' + type + '<br />' + country + ' [<a href="https://miora.pipedrive.com/organization/' + item.related_id + '" target="_blank">' + item.related_id + '</a>]</td>';
                    html += '<td>' + item.name + '<br />' + business + '</td>';
                    html += '<td class="center">' + item.rep + '<br />' + status + '</td>';
                    html += '<td>' + item.contact + '<br />' + item.phone + '</td>';
                    html += '<td>' + item.channel + '<br />' + item.source + '</td>';
                    html += '</tr>';
                });

                if (hls > 0) {
                    if (eventsAlert == 1 || eventsAlert == 2) {
                        sendNotification('BEWE Leads Events', 'We have ' + hls + ' new lead/s ready to be assigned :-)');
                    }
                    if (eventsAlert == 1 || eventsAlert == 3) {
                        playAlert('submarine');
                    }
                }

                eventsBody.html(html);
            } else {
                eventsBody.find('tr').removeClass('hl');
            }

            $('[data-toggle="popover"]').popover();
            $('#timestamp').html(data.timestr + ' (' + data.timezone + ')');

            $('#today').effect('highlight', {}, 2000);
            $('#events').effect('highlight', {color: '#FAFFBD'}, 2000);
            $('#timestamp').effect('highlight', {}, 5000);
        }
    });
}

function calcEvents()
{
    var timestamp = Math.floor($.now() / 1000);

    $.each($('#events tbody tr'), function() {
        var ts = $(this).find('td').first().attr('rel');
        var diff = strDiff(ts, timestamp);
        $(this).find('span.diff').first().html(diff);
    });
}

function checkNotifications()
{
    if (!Notification) {
        alert('Desktop notifications not available in your browser. Try Chrome.');
        
        return;
    }

    if (Notification.permission !== 'granted') {
        Notification.requestPermission();
    }
}

function sendNotification(title, body)
{
    if (Notification.permission !== 'granted') {
        Notification.requestPermission();
        
        return;
    }

    var notif = new Notification(title, {
        icon: 'https://bewe.io/resources/img/favs/apple-icon-60x60.png',
        body: body
    });

    notif.onclick = function () {
        window.open(path + '/admin/s/events-leads/');
    };
}

function toogleValidBills()
{
    $.each($('table#Bills tbody tr'), function(i, item) {
        var tds = $(item).find('td');
        
        if (tds.length > 1) {
            var state = parseInt(tds[2].innerText);
            
            if ((state < 0 || state > 2) && state != -30) {
                $(item).toggle();
            }
        }
    });
}

function setMatillionTimer(s)
{
    clearInterval(matillionTimer);
    matillionTimer = setInterval(reloadMatillionStatus, 10000);
}

function reloadMatillionStatus()
{
    $.ajax({
        type: 'get',
        url: path + '/api/matillion/status/',
        dataType: 'json',
        success: function(data) {
            setMatillionStatus(data.status);
        }
    });
}

function setMatillionStatus(status)
{
    var eventsBody = $('#status');
    var html = '';

    if (status == -1) {
        html += '<span class="label label-danger">Unknown</span>';
        $('#open').hide();
        $('#start').hide();
        $('#stop').hide();
    } else if (status == 0) {
        html += '<span class="label label-warning">Pending</span>';
        $('#open').hide();
        $('#start').hide();
        $('#stop').hide();
    } else if (status == 16) {
        html += '<span class="label label-success">Running</span>';
        $('#open').show();
        $('#start').hide();
        $('#stop').show();
    } else if (status == 32) {
        html += '<span class="label label-warning">Shutting Down</span>';
        $('#open').hide();
        $('#start').hide();
        $('#stop').hide();
    } else if (status == 48) {
        html += '<span class="label label-danger">Terminated</span>';
        $('#open').hide();
        $('#start').hide();
        $('#stop').hide();
    } else if (status == 64) {
        html += '<span class="label label-warning">Stopping</span>';
        $('#open').hide();
        $('#start').hide();
        $('#stop').hide();
    } else if (status == 80) {
        html += '<span class="label label-secondary">Stopped</span>';
        $('#open').hide();
        $('#start').show();
        $('#stop').hide();
    }

    eventsBody.html(html);
}

function runMatillionAction(action)
{
    if (action == 'run') {
        var job = $('#job').val();
        if (job != '') {
            action = 'run/' + job;
        } else {
            alert('Invalid Job.');
            
            return;
        }
    }

    $.ajax({
        type: 'get',
        url: path + '/api/matillion/' + action + '/',
        dataType: 'json',
        success: function(data) {
            setMatillionStatus(data.status);
        },
        beforeSend: function() {
            setMatillionStatus(-1);
        }
    });
}