{include file='admin/head.tpl' title='Recover Password'}
<body>

    {include file='admin/nav.tpl'}

    <div class="container">

        <h1>Recover Password</h1>

        <p>Forgot your password? Don't worry, introduce your email below and we'll send you a link so you can choose a new one.</p>

        <form action="{$path}/password/" method="post">
            <input type="hidden" id="action" name="action" value="password" />

            <label for="email">Email</label>
            <input type="text" class="span3" id="email" name="email" placeholder="Email" value="{$data.email}" />
            <span class="help-inline">&nbsp;</span>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>

    </div>

</body>
</html>