<div class="navbar navbar-static-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target="#nav">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="{$path}/admin/">{$sitename}</a>
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

{if $timezone}
<div class="pull-right" style="margin: 5px 10px 0 0;">
    <strong>Timezone:</strong> {$timezone}
</div>
{/if}