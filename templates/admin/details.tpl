{include file='admin/head.tpl' title="$htitle"}
<body>

    {include file='admin/nav.tpl'}

    <div class="container">

        <h1>{$htitle}</h1>

        {if $nav != ''}
        {include file="admin/sections/$nav.tpl"}
        {else}
        {include file="admin/auto.tpl"}
        {/if}

    </div>

</body>
</html>