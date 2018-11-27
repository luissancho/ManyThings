<table id="Details" class="table table-condensed table-bordered">
    <caption>Details</caption>
    <thead>
        <tr>
            {if $section.permissions.edit == 1 && $item.meta.edit}
            <th class="span1">&nbsp;</th>
            {/if}
            {foreach item="field" from=$item.meta.fields name="field"}
            <th width="{$field.width}" style="text-align: {$field.align};">{$field.name}</th>
            {/foreach}
        </tr>
    </thead>
    <tbody>
        <tr>
            {if $section.permissions.edit == 1 && $item.meta.edit}
            <td class="center">
                <a href="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/">Edit</a>
            </td>
            {/if}
            {foreach item="field" from=$item.meta.fields name="field"}
            <td style="text-align: {$field.align};">
                {if $item.data[$field.id] != ''}
                {if $field.forward}
                <a href="{$path}/admin/s/{$field.forward}/details/{$item.data[$field.id]}/">{$item.data[$field.id]}</a>
                {else}
                {$item.data[$field.id]}
                {/if}
                {else}
                -
                {/if}
            </td>
            {/foreach}
        </tr>
        {if $item.active && $section.permissions.edit == 1 && isset($item.meta.actions.main)}
        <tr>
            {if $item.meta.edit}
            <td colspan="{$smarty.foreach.field.total + 1}">
            {else}
            <td colspan="{$smarty.foreach.field.total}">
            {/if}
                {foreach item="action" key="act" from=$item.meta.actions.main name="action"}
                <form style="display: inline;" id="f_{$act}" action="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/" method="post">
                    <input type="hidden" name="action" value="{$act}" />
                    <input type="hidden" name="log" value="{$action.log}" />
                    {foreach item="var" key="key" from=$action.vars name="var"}
                    <input type="hidden" name="{$key}" value="{$var}" />
                    {/foreach}
                    {if $action.input == 'prompt'}
                    <input type="hidden" name="data" id="d_{$act}" />
                    <a href="javascript: adminActionData('{$act}', '{$action.prompt_name}', '{$action.prompt_def}');">{$action.name}</a>
                    {elseif $action.input == 'confirm'}
                    <a href="javascript: adminAction('{$act}');">{$action.name}</a>
                    {else}
                    <a href="javascript: adminAction('{$act}', false);">{$action.name}</a>
                    {/if}
                </form>
                {if !$smarty.foreach.action.last}|{/if}
                {/foreach}
            </td>
        </tr>
        {elseif !$item.active}
        <tr>
            {if $item.meta.edit}
            <td colspan="{$smarty.foreach.field.total + 1}">
            {else}
            <td colspan="{$smarty.foreach.field.total}">
            {/if}
                <span class="deleted">DELETED</span>
            </td>
        </tr>
        {/if}
    </tbody>
</table>

{foreach item="block" key="name" from=$item.meta.blocks name="block"}
<table id="{$name}" class="table table-condensed table-bordered">
    <caption>{$name}</caption>
    <thead>
        <tr>
            {foreach item="field" from=$block name="block_field"}
            <th width="{$field.width}" style="text-align: {$field.align};">{$field.name}</th>
            {/foreach}
        </tr>
    </thead>
    <tbody>
        <tr>
            {foreach item="field" from=$block name="block_field"}
            <td style="text-align: {$field.align};">
                {if $item.blocks[$name][$field.id] != ''}
                {if $field.forward}
                <a href="{$path}/admin/s/{$field.forward}/details/{$item.blocks[$name][$field.id]}/">{$item.blocks[$name][$field.id]}</a>
                {else}
                {$item.blocks[$name][$field.id]}
                {/if}
                {else}
                -
                {/if}
            </td>
            {/foreach}
        </tr>
        {if $section.permissions.edit == 1 && isset($item.meta.actions[$name])}
        <tr>
            <td colspan="{$smarty.foreach.block_field.total}">
                {foreach item="action" key="act" from=$item.meta.actions[$name] name="block_action"}
                <form style="display: inline;" id="f_{$act}" action="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/" method="post">
                    <input type="hidden" name="action" value="{$act}" />
                    <input type="hidden" name="log" value="{$action.log}" />
                    {foreach item="var" key="key" from=$action.vars name="var"}
                    <input type="hidden" name="{$key}" value="{$var}" />
                    {/foreach}
                    {if $action.input == 'prompt'}
                    <input type="hidden" name="data" id="d_{$act}" />
                    <a href="javascript: adminActionData('{$act}', '{$action.prompt_name}', '{$action.prompt_def}');">{$action.name}</a>
                    {elseif $action.input == 'confirm'}
                    <a href="javascript: adminAction('{$act}');">{$action.name}</a>
                    {else}
                    <a href="javascript: adminAction('{$act}', false);">{$action.name}</a>
                    {/if}
                </form>
                {if !$smarty.foreach.block_action.last}|{/if}
                {/foreach}
            </td>
        </tr>
        {/if}
    </tbody>
</table>
{/foreach}

{foreach item="relation" key="name" from=$item.relations name="relation"}
<table id="{$relation.name}" class="table table-condensed table-bordered">
    <caption>{$relation.name}</caption>
    {if count($item.relations[$name].items) > 0}
    <thead>
        <tr>
            {foreach item="field" from=$item.relations[$name].meta.fields name="relation_field"}
            <th width="{$field.width}" style="text-align: {$field.align};">{$field.name}</th>
            {/foreach}
            {if count($item.relations[$name]['actions']) > 0}
            <th style="text-align: center;">&nbsp;</th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {foreach item="row" key="n" from=$item.relations[$name].items name="row"}
        <tr>
            {foreach item="field" from=$item.relations[$name].meta.fields name="relation_field"}
            <td style="text-align: {$field.align};">
                {if $row.data[$field.id] != ''}
                {if $field.id == $item.relations[$name].meta.key && $item.relations[$name].forward}
                <a href="{$path}/admin/s/{$item.relations[$name].forward}/details/{$row.data[$field.id]}/">{$row.data[$field.id]}</a>
                {elseif $field.forward}
                <a href="{$path}/admin/s/{$field.forward}/details/{$row.data[$field.id]}/">{$row.data[$field.id]}</a>
                {else}
                {$row.data[$field.id]}
                {/if}
                {else}
                -
                {/if}
            </td>
            {/foreach}
            {if count($item.relations[$name]['actions']) > 0}
            <td>
                {if isset($item.relations[$name]['actions'][$n])}
                {foreach item="action" key="act" from=$item.relations[$name]['actions'][$n] name="rel_action"}
                <form style="display: inline;" id="f_{$act}_{$row.data[$action.column]}" action="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/" method="post">
                    <input type="hidden" name="action" value="{$act}" />
                    <input type="hidden" name="log" value="{$action.log}" />
                    <input type="hidden" name="id" value="{$row.data[$action.column]}" />
                    {foreach item="var" key="key" from=$action.vars name="var"}
                    <input type="hidden" name="{$key}" value="{$var}" />
                    {/foreach}
                    {if $action.input == 'prompt'}
                    <input type="hidden" name="data" id="d_{$act}_{$row.data[$action.column]}" />
                    <a href="javascript: adminActionData('{$act}_{$row.data[$action.column]}', '{$action.prompt_name}', '{$action.prompt_def}');">{$action.name}</a>
                    {elseif $action.input == 'confirm'}
                    <a href="javascript: adminAction('{$act}_{$row.data[$action.column]}');">{$action.name}</a>
                    {else}
                    <a href="javascript: adminAction('{$act}_{$row.data[$action.column]}', false);">{$action.name}</a>
                    {/if}
                </form>
                {if !$smarty.foreach.rel_action.last}|{/if}
                {/foreach}
                {else}
                &nbsp;
                {/if}
            </td>
            {/if}
        </tr>
        {/foreach}
        {if $item.relations[$name].add}
        <tr>
            {if isset($item.relations[$name]['actions'])}
            <td colspan="{$smarty.foreach.relation_field.total + 1}">
            {else}
            <td colspan="{$smarty.foreach.relation_field.total}">
            {/if}
                <a href="{$path}/admin/s/{$item.relations[$name].add}/add/{$item.id}/">Add</a>
            </td>
        </tr>
        {/if}
        {if $section.permissions.edit == 1 && isset($item.meta.actions[$name])}
        <tr>
            {if count($item.relations[$name]['actions']) > 0}
            <td colspan="{$smarty.foreach.relation_field.total + 1}">
            {else}
            <td colspan="{$smarty.foreach.relation_field.total}">
            {/if}
                {foreach item="action" key="act" from=$item.meta.actions[$name] name="block_action"}
                <form style="display: inline;" id="f_{$act}" action="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/" method="post">
                    <input type="hidden" name="action" value="{$act}" />
                    <input type="hidden" name="log" value="{$action.log}" />
                    {foreach item="var" key="key" from=$action.vars name="var"}
                    <input type="hidden" name="{$key}" value="{$var}" />
                    {/foreach}
                    {if $action.input == 'prompt'}
                    <input type="hidden" name="data" id="d_{$act}" />
                    <a href="javascript: adminActionData('{$act}', '{$action.prompt_name}', '{$action.prompt_def}');">{$action.name}</a>
                    {elseif $action.input == 'confirm'}
                    <a href="javascript: adminAction('{$act}');">{$action.name}</a>
                    {else}
                    <a href="javascript: adminAction('{$act}', false);">{$action.name}</a>
                    {/if}
                </form>
                {if !$smarty.foreach.block_action.last}|{/if}
                {/foreach}
            </td>
        </tr>
        {/if}
    </tbody>
    {else}
    <tbody>
        {if $item.relations[$name].add}
        <tr>
            <td class="span1 center">
                <a href="{$path}/admin/s/{$item.relations[$name].add}/add/{$item.id}/">Add</a>
            </td>
        </tr>
        {else}
        <tr>
            <td class="span1 center">
                -
            </td>
        </tr>
        {/if}
        {if $section.permissions.edit == 1 && isset($item.meta.actions[$name])}
        <tr>
            <td colspan="{$smarty.foreach.relation_field.total}">
                {foreach item="action" key="act" from=$item.meta.actions[$name] name="block_action"}
                <form style="display: inline;" id="f_{$act}" action="{$path}/admin/s/{$item.meta.section}/edit/{$item.id}/" method="post">
                    <input type="hidden" name="action" value="{$act}" />
                    <input type="hidden" name="log" value="{$action.log}" />
                    {foreach item="var" key="key" from=$action.vars name="var"}
                    <input type="hidden" name="{$key}" value="{$var}" />
                    {/foreach}
                    {if $action.input == 'prompt'}
                    <input type="hidden" name="data" id="d_{$act}" />
                    <a href="javascript: adminActionData('{$act}', '{$action.prompt_name}', '{$action.prompt_def}');">{$action.name}</a>
                    {elseif $action.input == 'confirm'}
                    <a href="javascript: adminAction('{$act}');">{$action.name}</a>
                    {else}
                    <a href="javascript: adminAction('{$act}', false);">{$action.name}</a>
                    {/if}
                </form>
                {if !$smarty.foreach.block_action.last}|{/if}
                {/foreach}
            </td>
        </tr>
        {/if}
    </tbody>
    {/if}
</table>
{/foreach}