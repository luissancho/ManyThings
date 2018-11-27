{include file='admin/head.tpl' title="$htitle"}
<body>

    {include file='admin/nav.tpl'}

    <div class="container">

        <h1>{$htitle}</h1>

        {if $form.message != ''}
        <p>{$form.message}</p>
        {/if}

        <form action="{$path}/admin/s/{$nav}/" method="{$form.method}" enctype="multipart/form-data">
            <input type="hidden" name="action" value="action" />

            {foreach item='item' from=$form.fields name='item'}
                <label for="{$item.name}">{$item.label}</label>
                {if $item.type == 'text'}
                <input type="text" class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}" placeholder="{$item.tip}" value="{$data[$item.name]}" />
                {elseif $item.type == 'file'}
                <input type="file" class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}" />
                {elseif $item.type == 'password'}
                <input type="password" class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}" />
                {elseif $item.type == 'date'}
                <input type="text" class="datepicker {if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}" placeholder="{$item.tip}" value="{$data[$item.name]}" />
                {elseif $item.type == 'range'}
                <div class="daterange">
                    <input type="text" class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}" placeholder="{$item.tip}" value="{$data[$item.name]}" />
                    <div rel="{$item.name}"></div>
                </div>
                {elseif $item.type == 'textarea'}
                <textarea rows="4" class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.id}" name="{$item.name}">{$data[$item.name]}</textarea>
                {elseif $item.type == 'select'}
                <select class="{if $item.class == 'std'}span3{elseif $item.class == 'short'}span1{elseif $item.class == 'long'}span5{/if}" id="{$item.name}" name="{$item.name}">
                    {foreach item='elem' from=$item.options name='elem' key='key'}
                    <option value="{$key}"{if $data[$item.name] === $key} selected="selected"{/if}>{$elem}</option>
                    {/foreach}
                </select>
                {elseif $item.type == 'hidden'}
                <input type="hidden" id="{$item.id}" name="{$item.name}" value="{$data[$item.name]}" />
                {/if}
                <span class="help-inline">&nbsp;</span>
            {/foreach}

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Send</button>
                <a class="btn btn-link" href="{$path}/admin/s/{$nav}/">Back</a>
            </div>
        </form>

    </div>

</body>
</html>