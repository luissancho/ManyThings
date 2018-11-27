{include file='head.tpl' title='Errors'}
<body>

    <div class="container">

        <h1>Errors</h1>

        <ul class="errorlist">
            {foreach item=error from=$errors}
            <li>{$error}</li>
            {/foreach}
        </ul>
        <p>
            <a href="javascript: history.go(-1);">Back</a>
        </p>

    </div>

</body>
</html>