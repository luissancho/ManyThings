<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{$title} - {$sitename} Admin</title>

    <meta name="description" content="{$description}" />

    {if $keywords != ''}
    <meta name="keywords" content="{$keywords}" />
    {/if}
    
    {if $robots != ''}
    <meta name="robots" content="{$robots}" />
    {/if}
    
    <!-- JQuery -->
    <script type="text/javascript" src="{$path}/resources/lib/jquery.min.js"></script>

    <!-- JQuery UI -->
    <link rel="stylesheet" type="text/css" media="screen" href="{$path}/resources/lib/jquery-ui/css/smoothness/jquery-ui.css" />
    <script type="text/javascript" src="{$path}/resources/lib/jquery-ui/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/lib/bootstrap/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/lib/bootstrap/css/bootstrap-responsive.min.css" />
    <script type="text/javascript" src="{$path}/resources/lib/bootstrap/js/bootstrap.min.js"></script>

    <script type="text/javascript">
        var path = '{$path}';
        var relpath = '{$relpath}';
    </script>

    {if isset($grid)}
    <!-- JqGrid -->
    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/lib/jqGrid/themes/redmond/jquery-ui-1.7.1.custom.css" />
    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/lib/jqGrid/css/ui.jqgrid.css" />
    <script type="text/javascript" src="{$path}/resources/lib/jqGrid/js/i18n/grid.locale-en.js"></script>
    <script type="text/javascript" src="{$path}/resources/lib/jqGrid/js/jquery.jqGrid.min.js"></script>
    <script type="text/javascript" src="{$path}/resources/scripts/admin/jqgrid.js"></script>
    <script type="text/javascript">
        var grid = {$grid};
        var level = {$session.level}
        $(document).ready(initJqGrid);
    </script>
    {/if}
    
    {if isset($section.type) && $section.type == 'dashboard'}
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        {literal}
        google.load('visualization', '1', {packages:['corechart']});
        {/literal}
    </script>
    {/if}

    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/styles/admin/admin.css" />
    <script type="text/javascript" src="{$path}/resources/js/admin.js"></script>

    <link rel="shortcut icon" href="{$path}/resources/img/favicon-16x16.png" />

</head>