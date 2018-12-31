{include file='head.tpl' title='Done!'}
<body>

    <div class="container">

        <h1>Done!</h1>

        {$message}

        <p>
            <a href="{if $url != ''}{$url}{else}{$path}/{/if}">{if $ulink != ''}{$ulink}{else}Back{/if}</a>
        </p>

    </div>

</body>
</html>