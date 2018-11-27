<!DOCTYPE html>
<html lang="es-es">
<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />

    <title>{$title} - ManyThings</title>

    <meta name="description" content="{$description}" />

    {if $keywords != ''}
    <meta name="keywords" content="{$keywords}" />
    {/if}

    {if $robots != ''}
    <meta name="robots" content="{$robots}" />
    {/if}

    <link rel="shortcut icon" href="{$path}/resources/img/favicon.ico" />

    {if $online}
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
    {else}
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{$path}/node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{$path}/node_modules/font-awesome/css/font-awesome.min.css" />
    {/if}
    
    <script type="text/javascript">
        var domain = '{$domain}';
        var path = '{$path}';
    </script>
    
    <link type="text/css" rel="stylesheet" media="screen" href="{$path}/resources/build/{build file='styles.css'}" />

</head>