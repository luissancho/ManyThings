{include file='head.tpl' title='Login'}
<body>

    <div class="container">

        <h1>{$sitename} Login</h1>

        <form action="{$path}/login/" method="post">
            <input type="hidden" id="url" name="url" value="{$url}" />
            <input type="hidden" id="autologin" name="autologin" value="1" />

            <label for="email">Email</label>
            <input type="text" class="span3" id="email" name="email" placeholder="Email" value="{if isset($data.email)}{$data.email}{/if}" />
            <span class="help-inline">&nbsp;</span>

            <label for="password">Password</label>
            <input type="password" class="span3" id="password" name="password" placeholder="Password" value="{if isset($data.password)}{$data.password}{/if}" autocomplete="new-password" />
            <span class="help-inline">&nbsp;</span>

            <p><a href="{$path}/password/">Recordar contraseña</a></p>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

    </div>

</body>
</html>