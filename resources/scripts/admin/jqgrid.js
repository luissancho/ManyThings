function initJqGrid()
{
    var names = new Array();
    var model = new Array();

    jQuery.each(grid.fields, function(i, val) {
        names[i] = val.name;
        model[i] = {
            name: val.id,
            index: val.id,
            width: val.width,
            align: val.align,
            sortable: val.sortable,
            search: val.search,
            stype: val.stype,
            searchoptions: {
                value: val.searchoptions
            },
            formatter: val.formatter,
            formatoptions: {
                srcformat: val.format,
                newformat: val.format
            }
        };
    });

    var rowNum = 20;
    var rowList = (level > 2) ? [10,20,30,50] : [10,20];

    jQuery.extend($.fn.fmatter, {
        date: function(cellValue, options, rowData) {
            return cellValue != '01/01/70' ? cellValue : '';
        }
    });
    jQuery.extend($.fn.fmatter, {
        formatColor: function(cellValue, options, rowData) {
            return '<span class="label label-' + cellValue + '" title="cellValue">&nbsp;</span>';
        }
    });
    jQuery.extend($.fn.fmatter.formatColor, {
        unformat : function(cellvalue, options, cellObject) {
            return $(cellObject.html()).attr('title');
        }
    });

    jQuery('#grid').jqGrid({
        url: path + '/api/grid/' + grid.section + '/',
        datatype: 'json',
        colNames: names,
        colModel: model,
        pager: '#toolbar',
        rowNum: rowNum,
        rowList: rowList,
        height: '100%',
        sortname: grid.sortname,
        viewrecords: true,
        sortorder: grid.sortorder,
        caption: grid.caption,
        viewsortcols: [true, 'horizontal', true],
        jsonReader: {
            repeatitems: false,
            id: '0'
        },
        ondblClickRow: function(id) {
            window.location = path + '/admin/s/' + grid.section + '/details/' + id + '/';
        }
    });
    jQuery('#grid').jqGrid('navGrid', '#toolbar', {
        del: false,
        add: false,
        edit: false,
        search: false,
        refresh: false
    });
    jQuery('#grid').jqGrid('filterToolbar', {
        stringResult: true,
        searchOnEnter: false
    });
    jQuery('#grid').jqGrid('navButtonAdd', '#toolbar', {
        caption: 'Reload',
        buttonicon: 'ui-icon-search',
        onClickButton: function() {
            var grid = $('#grid');
            grid[0].clearToolbar();
        }
    });
    jQuery('#grid').jqGrid('navButtonAdd', '#toolbar', {
        caption: 'Details',
        buttonicon: 'ui-icon-document',
        onClickButton: function() {
            var gsr = jQuery('#grid').jqGrid('getGridParam', 'selrow');
            if (gsr) {
                window.open(path + '/admin/s/' + grid.section + '/details/' + gsr + '/');
            } else {
                alert('Select a record');
            }
        }
    });
    if (grid.edit) {
        jQuery('#grid').jqGrid('navButtonAdd', '#toolbar', {
            caption: 'Edit',
            buttonicon: 'ui-icon-pencil',
            onClickButton: function() {
                var gsr = jQuery('#grid').jqGrid('getGridParam', 'selrow');
                if (gsr) {
                    window.open(path + '/admin/s/' + grid.section + '/edit/' + gsr + '/');
                } else {
                    alert('Select a record');
                }
            }
        });
    }
    if (grid.add) {
        jQuery('#grid').jqGrid('navButtonAdd', '#toolbar', {
            caption: 'Add',
            buttonicon: 'ui-icon-plus',
            onClickButton: function() {
                window.open(path + '/admin/s/' + grid.section + '/add/');
            }
        });
    }
    if (grid.export) {
        jQuery.each(grid.export, function(key, val) {
            var caption = (val != '') ? 'Export ' + val : 'Export';
            jQuery('#grid').jqGrid('navButtonAdd', '#toolbar', {
                caption: caption,
                buttonicon: 'ui-icon-save',
                onClickButton: function() {
                    jQuery('#grid').jqGrid('setCaption', 'Loading...');
                    var data = '';
                    var postData = jQuery('#grid').jqGrid('getGridParam', 'postData');
                    for (var param in postData) {
                        data += param + '=' + postData[param] + '&';
                    }
                    $.ajax({
                        type: 'get',
                        url: path + '/api/grid/' + grid.section + '/' + key + '/',
                        data: data,
                        dataType: 'json',
                        success: function(data) {
                            jQuery('#grid').jqGrid('setCaption', grid.caption);
                            window.location = path + '/export/?name=' + data.filename;
                        }
                    });
                }
            });
        });
    }
}