<script type="text/javascript">
    var username = '{$session.user.username} ({$session.uid})';
    var dataLayer = window.dataLayer || [];
    dataLayer.push({
        'username': username
    });
</script>
{literal}
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-P52LPW"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-P52LPW');</script>
<!-- End Google Tag Manager -->
{/literal}

<div class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target="#nav">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="{$path}/">{$sitename}</a>
            <div id="nav" class="nav-collapse collapse">
                <ul class="nav">
                    <li class="divider-vertical"></li>
                    {foreach key='tab' item='group' from=$sections['model'] name='models'}
                    {if count($group) > 0}
                    {if $tab != ''}
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">{$tab} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            {foreach key='key' item='sec' from=$group name='sec'}
                            <li{if $nav == $key} class="active"{/if}><a href="{$path}/admin/s/{$key}/">{$sec.name}</a></li>
                            {/foreach}
                        </ul>
                    </li>
                    {else}
                    {foreach key='key' item='sec' from=$group name='sec'}
                    <li{if $nav == $key} class="active"{/if}><a href="{$path}/admin/s/{$key}/">{$sec.name}</a></li>
                    {/foreach}
                    <li class="divider-vertical"></li>
                    {/if}
                    {/if}
                    {/foreach}

                    {if count($sections['controller']) > 0}
                    <li class="divider-vertical"></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Controllers <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            {foreach key='key' item='sec' from=$sections['controller'][''] name='sec'}
                            <li{if $nav == $key} class="active"{/if}><a href="{$path}/admin/s/{$key}/">{$sec.name}</a></li>
                            {/foreach}
                        </ul>
                    </li>
                    {/if}

                    {if count($sections['dashboard']) > 0}
                    <li class="divider-vertical"></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Dashboards <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            {foreach key='key' item='sec' from=$sections['dashboard'][''] name='sec'}
                            <li{if $nav == $key} class="active"{/if}><a href="{$path}/admin/s/{$key}/">{$sec.name}</a></li>
                            {/foreach}
                        </ul>
                    </li>
                    {/if}

                    {if count($sections['admin']) > 0}
                    <li class="divider-vertical"></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Admin <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            {foreach key='key' item='sec' from=$sections['admin'][''] name='sec'}
                            <li{if $nav == $key} class="active"{/if}><a href="{$path}/admin/s/{$key}/">{$sec.name}</a></li>
                            {/foreach}
                        </ul>
                    </li>
                    {/if}

                    {if $session.level > 0 && count($sections) > 0}
                    <li class="divider-vertical"></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">{$session.user.username} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li{if $nav == 'user'} class="active"{/if}><a href="{$path}/user/">Account</a></li>
                            <li><a href="{$path}/logout/">Logout</a></li>
                        </ul>
                    </li>
                    {/if}
                </ul>
            </div>
        </div>
    </div>
</div>

{if isset($item.meta.menu) && $item.meta.menu !== false}
<div class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target="#menu">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div id="menu" class="nav-collapse collapse">
                <ul class="nav">
                    {if count($item.meta.menu) > 0}
                    {foreach item="link" from=$item.meta.menu name="link"}
                    <li><a href="#{$link}">{$link}</a></li>
                    {/foreach}
                    {/if}
                </ul>
            </div>
        </div>
    </div>
</div>
{/if}

<div class="pull-right" style="margin: 5px 10px 0 0;">
    <strong>Timezone:</strong> {$timezone}
</div>